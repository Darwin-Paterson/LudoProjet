<?php
session_start();
require '../config/db.php'; // পাথ চেক করুন

// রেসপন্স হেল্পার ফাংশন
function redirect_with($url, $status, $msg) {
    // যদি AJAX রিকোয়েস্ট হয় তবে JSON রিটার্ন করবেন, অন্যথায় রিডাইরেক্ট
    // এখানে আমরা সিম্পল রিডাইরেক্ট রাখছি ফর্ম সাবমিটের জন্য
    $prefix = (strpos($url, '?') !== false) ? '&' : '?';
    header("Location: ../" . $url . $prefix . "status=" . $status . "&msg=" . urlencode($msg));
    exit;
}

if (!isset($_SESSION['user_id'])) {
    redirect_with('login.php', 'error', 'Please login first');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with('tournaments.php', 'error', 'Invalid Request');
}

$user_id = $_SESSION['user_id'];
$tournament_id = $_POST['tournament_id'];
$ip_address = $_SERVER['REMOTE_ADDR'];

try {
    // ১. ট্রানজ্যাকশন শুরু
    $pdo->beginTransaction();

    // ২. ইউজার ডাটা লক করা (যাতে একই সময়ে ব্যালেন্স পরিবর্তন না হয়)
    $stmtUser = $pdo->prepare("SELECT balance, win_balance FROM users WHERE id = ? FOR UPDATE");
    $stmtUser->execute([$user_id]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) throw new Exception("User not found!");

    // ৩. টুর্নামেন্ট ডাটা লক করা
    $stmtTour = $pdo->prepare("SELECT * FROM tournaments WHERE id = ? FOR UPDATE");
    $stmtTour->execute([$tournament_id]);
    $tour = $stmtTour->fetch(PDO::FETCH_ASSOC);

    if (!$tour) throw new Exception("Tournament not found!");

    // ৪. ভ্যালিডেশন চেক
    if ($tour['status'] !== 'open') {
        throw new Exception("Tournament is not open for joining.");
    }
    
    // বর্তমান প্লেয়ার সংখ্যা চেক
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = ?");
    $stmtCount->execute([$tournament_id]);
    $joined_count = $stmtCount->fetchColumn();

    if ($joined_count >= $tour['max_players']) {
        throw new Exception("Slots are full!");
    }

    // ডুপ্লিকেট জয়েন চেক
    $stmtCheck = $pdo->prepare("SELECT id FROM tournament_participants WHERE user_id = ? AND tournament_id = ?");
    $stmtCheck->execute([$user_id, $tournament_id]);
    if ($stmtCheck->fetch()) {
        throw new Exception("You have already joined!");
    }

    // ৫. ব্যালেন্স চেক এবং ডিডাকশন লজিক
    $entry_fee = $tour['entry_fee'];
    $total_balance = $user['balance'] + $user['win_balance'];

    if ($total_balance < $entry_fee) {
        throw new Exception("Insufficient Balance! Please deposit.");
    }

    // লজিক: আগে মেইন ব্যালেন্স থেকে কাটবে, তারপর উইনিং থেকে
    if ($entry_fee > 0) {
        $deduct_from_main = 0;
        $deduct_from_win = 0;

        if ($user['balance'] >= $entry_fee) {
            // পুরোটা মেইন ব্যালেন্স থেকে যাবে
            $deduct_from_main = $entry_fee;
        } else {
            // মেইন ব্যালেন্স খালি হবে, বাকিটা উইনিং থেকে
            $deduct_from_main = $user['balance'];
            $deduct_from_win = $entry_fee - $user['balance'];
        }

        // ব্যালেন্স আপডেট কুয়েরি
        $updateBal = $pdo->prepare("UPDATE users SET balance = balance - ?, win_balance = win_balance - ? WHERE id = ?");
        $updateBal->execute([$deduct_from_main, $deduct_from_win, $user_id]);

        // ট্রানজ্যাকশন হিস্টোরি (অপশনাল কিন্তু ভালো)
        $log = $pdo->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description, created_at) VALUES (?, ?, 'debit', ?, NOW())");
        $log->execute([$user_id, $entry_fee, "Joined Tournament #{$tournament_id}"]);
    }

    // ৬. টুর্নামেন্টে পার্টিসিপেন্ট এড করা
    $addPlayer = $pdo->prepare("INSERT INTO tournament_participants (tournament_id, user_id, joined_at) VALUES (?, ?, NOW())");
    $addPlayer->execute([$tournament_id, $user_id]);

    // ৭. অটো লাইভ লজিক (Auto Live Logic)
    $new_count = $joined_count + 1;
    $redirect_url = "tournaments.php"; // ডিফল্ট রিডাইরেক্ট
    $msg = "Joined Successfully!";

    if ($new_count >= $tour['max_players']) {
        // স্লট ফুল হয়ে গেছে, স্ট্যাটাস লাইভ করে দাও
        $updateStatus = $pdo->prepare("UPDATE tournaments SET status = 'live' WHERE id = ?");
        $updateStatus->execute([$tournament_id]);
        
        // ইউজারকে সরাসরি খেলার পেজে পাঠানো হবে
        $redirect_url = "playmatch.php?id=" . $tournament_id;
        $msg = "Match Started! All slots full.";
    }

    // সব ঠিক থাকলে কমিট
    $pdo->commit();

    // রিডাইরেক্ট
    redirect_with($redirect_url, 'success', $msg);

} catch (Exception $e) {
    // সমস্যা হলে রোলব্যাক
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirect_with('tournaments.php', 'error', $e->getMessage());
}
?>
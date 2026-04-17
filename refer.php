<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

// লগইন চেক
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

try {
    // ইউজার ইনফো আনা
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // রেফার কোড না থাকলে অটো-জেনারেট করা (Unique Code Logic)
    if (empty($user['referral_code'])) {
        $is_unique = false;
        while (!$is_unique) {
            // ইউজারনেমের প্রথম ৩ অক্ষর + ৪ ডিজিটের র‍্যান্ডম নাম্বার
            $clean_username = str_replace(' ', '', $user['username']);
            $new_code = strtoupper(substr($clean_username, 0, 3) . rand(1000, 9999));

            // চেক করা যে এই কোডটি অন্য কারো আছে কি না
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
            $check_stmt->execute([$new_code]);
            if (!$check_stmt->fetch()) {
                $is_unique = true;
            }
        }

        // ডাটাবেস আপডেট করা
        $stmt_update = $pdo->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
        $stmt_update->execute([$new_code, $user_id]);
        $user['referral_code'] = $new_code; // ডিসপ্লে করার জন্য আপডেট ভ্যালু সেট করা
    }

    // রেফারেল পরিসংখ্যান (কতজন জয়েন করেছে)
    $stmt_ref_count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?");
    $stmt_ref_count->execute([$user['referral_code']]);
    $total_referrals = $stmt_ref_count->fetchColumn();

    // রেফারেল তালিকা (সর্বশেষ ৫ জন)
    $stmt_list = $pdo->prepare("SELECT username, created_at, avatar FROM users WHERE referred_by = ? ORDER BY id DESC LIMIT 5");
    $stmt_list->execute([$user['referral_code']]);
    $referral_list = $stmt_list->fetchAll();

    // রেফারেল আর্নিং
    $total_earnings = isset($user['referral_earnings']) ? $user['referral_earnings'] : 0;

} catch (PDOException $e) {
    error_log($e->getMessage());
}

$share_text = "Rejoins Ludo Pro avec mon code *" . $user['referral_code'] . "* et reçois ৳50 de bonus ! Joue et gagne gros ! Lien : https://yoursite.com/register.php?ref=" . $user['referral_code'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Parrainage - Ludo Pro</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { 
            background: #020617; 
            color: #f1f5f9; 
            font-family: 'Rajdhani', sans-serif;
            background-image: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 70%);
            -webkit-tap-highlight-color: transparent;
        }
        .glass-card { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255,255,255,0.05); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }
        .nav-btn { color: #64748b; transition: all 0.3s; }
        .nav-btn.active { color: #3b82f6; text-shadow: 0 0 10px rgba(59, 130, 246, 0.5); }
        
        .dashed-border {
            background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' rx='16' ry='16' stroke='%23334155FF' stroke-width='2' stroke-dasharray='10%2c 10' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
        }
    </style>
</head>
<body class="pb-24 antialiased">

    <header class="p-5 flex justify-between items-center sticky top-0 z-40 bg-[#020617]/80 backdrop-blur-md">
        <button onclick="location.href='dashboard.php'" class="w-10 h-10 rounded-xl glass-card flex items-center justify-center text-slate-300 hover:text-white transition active:scale-95">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h2 class="text-xl font-bold tracking-wide">Parrainage</h2>
        <div class="w-10"></div>
    </header>

    <div class="max-w-md mx-auto px-5 mt-2 space-y-6">

        <div class="text-center space-y-2">
            <div class="relative inline-block">
                <div class="absolute inset-0 bg-blue-500 blur-2xl opacity-30 animate-pulse"></div>
                <img src="https://cdn-icons-png.flaticon.com/512/8695/8695029.png" class="w-32 h-32 relative z-10 mx-auto drop-shadow-xl" alt="Gift">
            </div>
            <h1 class="text-3xl font-black text-white">Gagnez ৳50 !</h1>
            <p class="text-sm text-slate-400 max-w-[80%] mx-auto">Invitez vos amis et recevez ৳50 dès qu'ils s'inscrivent et déposent.</p>
        </div>

        <div class="glass-card p-5 rounded-2xl border border-blue-500/30 relative overflow-hidden">
            <div class="absolute -right-5 -top-5 w-20 h-20 bg-blue-500/20 rounded-full blur-xl"></div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center mb-2">Votre code de parrainage</p>
            
            <div class="dashed-border p-1 rounded-2xl flex items-center justify-between bg-[#0f172a]/50">
                <div class="pl-4 font-mono text-xl font-bold text-blue-400 tracking-wider">
                    <?= htmlspecialchars($user['referral_code']) ?>
                </div>
                <button onclick="copyCode()" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-3 rounded-xl font-bold text-xs uppercase tracking-wide transition active:scale-95 shadow-lg shadow-blue-600/20">
                    Copier
                </button>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <a href="whatsapp://send?text=<?= urlencode($share_text) ?>" class="glass-card p-3 rounded-xl flex flex-col items-center gap-2 hover:bg-green-500/10 transition active:scale-95 border-b-2 border-green-500">
                <i class="fab fa-whatsapp text-2xl text-green-500"></i>
                <span class="text-[10px] font-bold text-slate-300">WhatsApp</span>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=https://yoursite.com" target="_blank" class="glass-card p-3 rounded-xl flex flex-col items-center gap-2 hover:bg-blue-600/10 transition active:scale-95 border-b-2 border-blue-600">
                <i class="fab fa-facebook text-2xl text-blue-600"></i>
                <span class="text-[10px] font-bold text-slate-300">Facebook</span>
            </a>
            <a href="https://t.me/share/url?url=https://yoursite.com&text=<?= urlencode($share_text) ?>" class="glass-card p-3 rounded-xl flex flex-col items-center gap-2 hover:bg-sky-500/10 transition active:scale-95 border-b-2 border-sky-500">
                <i class="fab fa-telegram text-2xl text-sky-500"></i>
                <span class="text-[10px] font-bold text-slate-300">Telegram</span>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="glass-card p-4 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h4 class="text-xl font-black text-white"><?= $total_referrals ?></h4>
                    <p class="text-[10px] text-slate-400 uppercase font-bold">Amis inscrits</p>
                </div>
            </div>
            <div class="glass-card p-4 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center text-green-400">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <h4 class="text-xl font-black text-white">৳<?= $total_earnings ?></h4>
                    <p class="text-[10px] text-slate-400 uppercase font-bold">Total gagné</p>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Parrainages récents</h3>
            <div class="space-y-3">
                <?php if(empty($referral_list)): ?>
                    <div class="text-center py-4 text-slate-500 text-xs">Aucun parrainage pour le moment. Partagez votre code !</div>
                <?php else: ?>
                    <?php foreach($referral_list as $ref): ?>
                    <div class="flex items-center justify-between border-b border-white/5 pb-2 last:border-0 last:pb-0">
                        <div class="flex items-center gap-3">
                            <img src="assets/avatars/<?= !empty($ref['avatar']) ? $ref['avatar'] : 'default.png' ?>" class="w-8 h-8 rounded-full bg-slate-800 object-cover">
                            <div>
                                <p class="text-sm font-bold text-white"><?= htmlspecialchars($ref['username']) ?></p>
                                <p class="text-[10px] text-slate-500"><?= date('d M, Y', strtotime($ref['created_at'])) ?></p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-green-400">+৳50</span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-[#0f172a]/90 backdrop-blur-xl border-t border-white/5 px-6 py-2 flex justify-between items-center z-50">
        <a href="dashboard.php" class="nav-btn flex flex-col items-center gap-1 p-2"><i class="fas fa-home text-lg"></i><span class="text-[9px] font-bold">Accueil</span></a>
        <a href="tournaments.php" class="nav-btn flex flex-col items-center gap-1 p-2"><i class="fas fa-trophy text-lg"></i><span class="text-[9px] font-bold">Événements</span></a>
        <div class="relative -top-6"><div onclick="location.href='tournaments.php'" class="bg-blue-600 w-14 h-14 rounded-2xl flex items-center justify-center shadow-2xl border-4 border-[#020617] cursor-pointer"><i class="fas fa-gamepad text-white text-xl"></i></div></div>
        <a href="wallet.php" class="nav-btn flex flex-col items-center gap-1 p-2"><i class="fas fa-wallet text-lg"></i><span class="text-[9px] font-bold">Portefeuille</span></a>
        <a href="profile.php" class="nav-btn flex flex-col items-center gap-1 p-2 active"><i class="fas fa-user text-lg"></i><span class="text-[9px] font-bold">Profil</span></a>
    </nav>

    <script>
        function copyCode() {
            const code = "<?= $user['referral_code'] ?>";
            navigator.clipboard.writeText(code).then(() => {
                Swal.fire({
                    title: 'Copié !',
                    text: 'Code de parrainage copié',
                    icon: 'success',
                    background: '#1e293b',
                    color: '#fff',
                    confirmButtonColor: '#3b82f6',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }
    </script>
</body>
</html>
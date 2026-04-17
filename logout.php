<?php
// ১. সেশন শুরু করা
session_start();

// ২. সেশনের সকল ডেটা রিমুভ করা
$_SESSION = array();

// ৩. সেশন কুকি ডিলিট করা (ব্রাউজার থেকে সেশন পুরোপুরি মুছে ফেলার জন্য)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ৪. সেশন পুরোপুরি ধ্বংস (Destroy) করা
session_destroy();

// ৫. লগইন পেজে রিডাইরেক্ট করা
header("Location: login.php"); // অথবা আপনার লগইন ফাইলের নাম দিন
exit;
?>

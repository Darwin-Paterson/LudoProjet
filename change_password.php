<?php
session_start();
require 'config/db.php';

// ১. লগইন চেক
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// ২. পাসওয়ার্ড পরিবর্তন লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_pass'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // ডাটাবেস থেকে বর্তমান পাসওয়ার্ড আনা
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        // বর্তমান পাসওয়ার্ড চেক
        if (password_verify($current_pass, $user['password'])) {
            // নতুন পাসওয়ার্ড ভ্যালিডেশন
            if (strlen($new_pass) < 6) {
                $msg = "Le mot de passe doit contenir au moins 6 caractères.";
                $msg_type = "red";
            } elseif ($new_pass !== $confirm_pass) {
                $msg = "Les nouveaux mots de passe ne correspondent pas.";
                $msg_type = "red";
            } else {
                // পাসওয়ার্ড হ্যাশ করে আপডেট করা
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($update->execute([$new_hash, $user_id])) {
                    $msg = "Mot de passe modifié avec succès !";
                    $msg_type = "green";
                } else {
                    $msg = "Une erreur est survenue !";
                    $msg_type = "red";
                }
            }
        } else {
            $msg = "Le mot de passe actuel est incorrect.";
            $msg_type = "red";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<?php include __DIR__.'/config/pwa.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Changer le mot de passe - Ludo Pro</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body { 
            background: #020617; 
            color: #f1f5f9; 
            font-family: 'Rajdhani', sans-serif;
            background-image: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 70%);
            -webkit-tap-highlight-color: transparent;
            min-height: 100vh;
        }
        .glass-card { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255,255,255,0.05); 
        }
        /* ইনপুট স্টাইল আগের পেজের মতো */
        .custom-input { 
            background: rgba(15, 23, 42, 0.6) !important; 
            border: 1px solid rgba(255,255,255,0.1) !important; 
            color: white !important; 
            transition: all 0.3s ease;
        }
        .custom-input:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="antialiased">

    <!-- Header -->
    <header class="p-5 flex justify-between items-center sticky top-0 z-40 bg-[#020617]/80 backdrop-blur-md">
        <a href="profile.php" class="w-10 h-10 rounded-xl glass-card flex items-center justify-center text-slate-300 hover:text-white transition active:scale-95">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2 class="text-xl font-bold tracking-wide">Sécurité</h2>
        <div class="w-10"></div> <!-- Spacer for center alignment -->
    </header>

    <div class="max-w-md mx-auto px-6 mt-4">
        
        <!-- Icon Banner -->
        <div class="flex justify-center mb-8">
            <div class="w-24 h-24 rounded-full bg-blue-500/10 flex items-center justify-center border-4 border-blue-500/20 shadow-[0_0_20px_rgba(59,130,246,0.3)]">
                <i class="fas fa-shield-alt text-4xl text-blue-500"></i>
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-black text-white">Changer le mot de passe</h1>
            <p class="text-sm text-slate-400 mt-1">Créez un mot de passe fort pour protéger votre compte.</p>
        </div>

        <!-- Alert Message -->
        <?php if($msg): ?>
            <div class="mb-6 bg-<?= $msg_type ?>-500/10 border border-<?= $msg_type ?>-500/50 text-<?= $msg_type ?>-400 px-4 py-3 rounded-xl flex items-center gap-3 text-sm font-bold shadow-lg">
                <i class="fas <?= $msg_type == 'green' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="" method="POST" class="space-y-5">
            
            <!-- Current Password -->
            <div class="space-y-2">
                <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider ml-1">Mot de passe actuel</label>
                <div class="relative group">
                    <div class="absolute left-4 top-3.5 text-slate-500 group-focus-within:text-blue-500 transition">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" name="current_password" required placeholder="Saisir l'ancien mot de passe" 
                           class="custom-input w-full py-3.5 pl-12 pr-12 rounded-xl outline-none text-sm font-medium">
                    <div class="absolute right-4 top-3.5 text-slate-500 cursor-pointer hover:text-white" onclick="togglePass(this)">
                        <i class="fas fa-eye-slash"></i>
                    </div>
                </div>
            </div>

            <!-- New Password -->
            <div class="space-y-2">
                <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider ml-1">Nouveau mot de passe</label>
                <div class="relative group">
                    <div class="absolute left-4 top-3.5 text-slate-500 group-focus-within:text-blue-500 transition">
                        <i class="fas fa-key"></i>
                    </div>
                    <input type="password" name="new_password" required placeholder="6 caractères minimum" 
                           class="custom-input w-full py-3.5 pl-12 pr-12 rounded-xl outline-none text-sm font-medium">
                    <div class="absolute right-4 top-3.5 text-slate-500 cursor-pointer hover:text-white" onclick="togglePass(this)">
                        <i class="fas fa-eye-slash"></i>
                    </div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
                <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider ml-1">Confirmer le nouveau mot de passe</label>
                <div class="relative group">
                    <div class="absolute left-4 top-3.5 text-slate-500 group-focus-within:text-blue-500 transition">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <input type="password" name="confirm_password" required placeholder="Retapez le nouveau mot de passe" 
                           class="custom-input w-full py-3.5 pl-12 pr-12 rounded-xl outline-none text-sm font-medium">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" name="change_pass" 
                    class="w-full mt-6 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white py-4 rounded-xl font-bold uppercase tracking-widest text-sm shadow-[0_4px_20px_rgba(37,99,235,0.3)] active:scale-[0.98] transition transform">
                Mettre à jour le mot de passe
            </button>

        </form>

        <div class="mt-8 text-center">
            <a href="profile.php" class="text-xs text-slate-500 hover:text-white transition border-b border-transparent hover:border-slate-500 pb-0.5">Annuler et revenir</a>
        </div>

    </div>

    <!-- Password Toggle Script -->
    <script>
        function togglePass(icon) {
            let input = icon.previousElementSibling;
            let iconElement = icon.querySelector('i');
            
            if (input.type === "password") {
                input.type = "text";
                iconElement.classList.remove('fa-eye-slash');
                iconElement.classList.add('fa-eye');
                iconElement.style.color = "#3b82f6";
            } else {
                input.type = "password";
                iconElement.classList.remove('fa-eye');
                iconElement.classList.add('fa-eye-slash');
                iconElement.style.color = "";
            }
        }
    </script>

</body>
</html>
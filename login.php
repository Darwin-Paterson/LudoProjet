<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

// Si l'utilisateur est déjà connecté, le rediriger vers le tableau de bord
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "L'e-mail et le mot de passe sont obligatoires.";
    } else {
        // Recherche de l'utilisateur par e-mail
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe (en testant directement $user pour éviter les erreurs)
        if ($user && password_verify($password, $user['password'])) {
            // Mise en place de la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            // $_SESSION['balance'] = $user['balance']; // Mieux vaut récupérer le solde en direct sur le tableau de bord

            // Redirection selon le rôle de l'utilisateur
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "E-mail ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Connexion - Ludo Pro</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body { 
            background: #020617; 
            color: #f1f5f9; 
            font-family: 'Rajdhani', sans-serif;
            background-image: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 70%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255,255,255,0.05); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }
        .input-group {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.3s ease;
        }
        .input-group:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="p-4">

    <!-- Background Glow Effects -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-64 h-64 bg-blue-600/20 rounded-full blur-[80px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-64 h-64 bg-indigo-600/20 rounded-full blur-[80px]"></div>
    </div>

    <div class="w-full max-w-md">
        
        <!-- Logo Section -->
        <div class="text-center mb-8 animate-fade-in-down">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/20 mb-4 transform rotate-3">
                <i class="fas fa-dice text-3xl text-white"></i>
            </div>
            <h1 class="text-4xl font-black tracking-tighter text-white">LUDO<span class="text-blue-500">PRO</span></h1>
            <p class="text-slate-400 text-sm font-medium mt-1">Jouez, gagnez et encaissez de l'argent réel</p>
        </div>

        <!-- Login Card -->
        <div class="glass-card p-8 rounded-[2rem] relative overflow-hidden">
            
            <h2 class="text-2xl font-bold text-white mb-6 text-center">Bon retour !</h2>

            <!-- Error Message -->
            <?php if($error): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-3 mb-6 rounded-xl text-xs font-bold flex items-center gap-2 animate-pulse">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                
                <!-- Email Field -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Adresse e-mail</label>
                    <div class="input-group flex items-center rounded-xl px-4 py-3">
                        <i class="fas fa-envelope text-slate-500 mr-3 text-lg"></i>
                        <input type="email" name="email" required 
                            class="bg-transparent border-none outline-none text-white w-full placeholder-slate-600 font-medium" 
                            placeholder="name@example.com">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Mot de passe</label>
                    <div class="input-group flex items-center rounded-xl px-4 py-3">
                        <i class="fas fa-lock text-slate-500 mr-3 text-lg"></i>
                        <input type="password" name="password" required 
                            class="bg-transparent border-none outline-none text-white w-full placeholder-slate-600 font-medium" 
                            placeholder="••••••••">
                    </div>
                    <div class="text-right mt-2">
                        <a href="#" class="text-[11px] font-bold text-blue-500 hover:text-blue-400 transition">Mot de passe oublié ?</a>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white py-3.5 rounded-xl font-bold text-sm uppercase tracking-wider shadow-lg shadow-blue-900/20 transition-all active:scale-95 flex items-center justify-center gap-2 mt-2">
                    <span>Se connecter en sécurité</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

            </form>

            <div class="mt-8 text-center border-t border-white/5 pt-6">
                <p class="text-slate-400 text-sm">
                    Nouveau sur LudoPro ? 
                    <a href="register.php" class="text-blue-500 font-bold hover:text-blue-400 transition ml-1">Créer un compte</a>
                </p>
            </div>
        </div>
        
        <!-- Footer Info -->
        <p class="text-center text-[10px] text-slate-600 mt-6 font-bold uppercase tracking-widest">
            &copy; <?= date('Y') ?> Ludo Pro. Jeu sécurisé.
        </p>

    </div>

</body>
</html>
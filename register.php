<?php
// Le fichier config/db.php doit impérativement exister
require 'config/db.php';

// Si l'utilisateur est déjà connecté, le rediriger vers le tableau de bord
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nettoyage des données en entrée
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']); // Récupération du numéro de téléphone
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation de base
    if (empty($username) || empty($email) || empty($phone) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        // Vérifier si l'e‑mail, le nom d'utilisateur ou le téléphone existent déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ? OR phone = ?");
        $stmt->execute([$email, $username, $phone]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Un compte existe déjà avec cet e-mail, nom d'utilisateur ou numéro de téléphone.";
        } else {
            // Hachage du mot de passe (sécurité)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Enregistrement en base (avec la colonne phone)
            // Solde par défaut à 0, avec un bonus éventuel (ici bonus de 20)
            $sql = "INSERT INTO users (username, email, phone, password, balance, created_at) VALUES (?, ?, ?, ?, 20.00, NOW())";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$username, $email, $phone, $hashed_password])) {
                $success = "Compte créé avec succès ! Redirection vers la connexion...";
                // Redirection automatique vers la page de connexion
                header("refresh:2;url=login.php"); 
            } else {
                $error = "Un problème serveur est survenu, veuillez réessayer plus tard.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inscription - Ludo Pro</title>
    
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
        /* Autofill color fix for dark mode */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px #0f172a inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>
<body class="p-4 py-8">

    <!-- Background Glow Effects -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] right-[-10%] w-64 h-64 bg-blue-600/20 rounded-full blur-[80px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-64 h-64 bg-indigo-600/20 rounded-full blur-[80px]"></div>
    </div>

    <div class="w-full max-w-md">
        
        <!-- Logo Section -->
        <div class="text-center mb-6">
            <h1 class="text-4xl font-black tracking-tighter text-white">LUDO<span class="text-blue-500">PRO</span></h1>
            <p class="text-slate-400 text-sm font-medium mt-1">Créez un compte et obtenez un bonus de ৳20</p>
        </div>

        <!-- Register Card -->
        <div class="glass-card p-6 rounded-[2rem] relative overflow-hidden">
            
            <h2 class="text-xl font-bold text-white mb-5 text-center">Inscription</h2>

            <!-- Error Message -->
            <?php if($error): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-3 mb-5 rounded-xl text-xs font-bold flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if($success): ?>
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-3 mb-5 rounded-xl text-xs font-bold flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                
                <!-- Username -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Nom d'utilisateur</label>
                    <div class="input-group flex items-center rounded-xl px-4 py-2.5">
                        <i class="fas fa-user text-slate-500 mr-3 text-sm"></i>
                        <input type="text" name="username" required 
                            class="bg-transparent border-none outline-none text-white w-full text-sm placeholder-slate-600 font-medium" 
                            placeholder="Ex: KingLudo123">
                    </div>
                </div>

                <!-- Phone Number (NEW) -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Numéro de téléphone</label>
                    <div class="input-group flex items-center rounded-xl px-4 py-2.5">
                        <i class="fas fa-phone-alt text-slate-500 mr-3 text-sm"></i>
                        <input type="tel" name="phone" required 
                            class="bg-transparent border-none outline-none text-white w-full text-sm placeholder-slate-600 font-medium" 
                            placeholder="017xxxxxxxx">
                    </div>
                </div>

                <!-- Email Address -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Adresse e-mail</label>
                    <div class="input-group flex items-center rounded-xl px-4 py-2.5">
                        <i class="fas fa-envelope text-slate-500 mr-3 text-sm"></i>
                        <input type="email" name="email" required 
                            class="bg-transparent border-none outline-none text-white w-full text-sm placeholder-slate-600 font-medium" 
                            placeholder="name@example.com">
                    </div>
                </div>

                <!-- Password Fields -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Mot de passe</label>
                        <div class="input-group flex items-center rounded-xl px-4 py-2.5">
                            <i class="fas fa-lock text-slate-500 mr-2 text-sm"></i>
                            <input type="password" name="password" required 
                                class="bg-transparent border-none outline-none text-white w-full text-sm placeholder-slate-600 font-medium" 
                                placeholder="******">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Confirmer</label>
                        <div class="input-group flex items-center rounded-xl px-4 py-2.5">
                            <i class="fas fa-check-circle text-slate-500 mr-2 text-sm"></i>
                            <input type="password" name="confirm_password" required 
                                class="bg-transparent border-none outline-none text-white w-full text-sm placeholder-slate-600 font-medium" 
                                placeholder="******">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white py-3.5 rounded-xl font-bold text-sm uppercase tracking-wider shadow-lg shadow-blue-900/20 transition-all active:scale-95 flex items-center justify-center gap-2 mt-4">
                    <span>Créer un compte</span>
                    <i class="fas fa-user-plus"></i>
                </button>

            </form>

            <div class="mt-6 text-center border-t border-white/5 pt-4">
                <p class="text-slate-400 text-sm">
                    Vous avez déjà un compte ? 
                    <a href="login.php" class="text-blue-500 font-bold hover:text-blue-400 transition ml-1">Se connecter</a>
                </p>
            </div>
        </div>
        
        <!-- Footer Info -->
        <p class="text-center text-[10px] text-slate-600 mt-6 font-bold uppercase tracking-widest">
            En vous inscrivant, vous acceptez nos conditions générales
        </p>

    </div>

</body>
</html>
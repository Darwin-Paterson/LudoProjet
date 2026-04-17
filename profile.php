<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

$msg = "";
$msg_type = "blue";

// --- 1. Logique d'upload d'avatar ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png');

    if (in_array($fileExt, $allowed)) {
        if ($file['size'] < 2000000) { 
            $newFileName = "user_" . $user_id . "_" . time() . "." . $fileExt;
            $fileDestination = 'assets/avatars/' . $newFileName;

            if (!is_dir('assets/avatars')) { mkdir('assets/avatars', 0777, true); }

            if (move_uploaded_file($file['tmp_name'], $fileDestination)) {
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$newFileName, $user_id]);
                header("Location: profile.php?success=Avatar mis à jour avec succès");
                exit();
            }
        } else { $msg = "Fichier trop volumineux ! (Max 2 Mo)"; $msg_type = "red"; }
    } else { $msg = "Type de fichier non valide !"; $msg_type = "red"; }
}

// --- 2. Logique de mise à jour du nom et du téléphone ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_name = $_POST['name'];
    $new_phone = $_POST['phone'];
    
    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
    if ($stmt->execute([$new_name, $new_phone, $user_id])) {
        header("Location: profile.php?success=Profil mis à jour avec succès");
        exit();
    }
}

// Gestion des messages de succès
if(isset($_GET['success'])) { $msg = $_GET['success']; $msg_type = "green"; }

// Récupération des informations depuis la base
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Statistiques de matchs
    $stmt_total = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE user_id = ?");
    $stmt_total->execute([$user_id]);
    $total_matches = $stmt_total->fetchColumn();

    $stmt_win = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE user_id = ? AND result = 'Win'");
    $stmt_win->execute([$user_id]);
    $total_wins = $stmt_win->fetchColumn();

    $win_rate = $total_matches > 0 ? round(($total_wins / $total_matches) * 100) : 0;
} catch (PDOException $e) { error_log($e->getMessage()); }

$avatar_url = !empty($user['avatar']) ? "assets/avatars/" . $user['avatar'] : "https://cdn-icons-png.flaticon.com/512/149/149071.png";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profil - Ludo Pro</title>
    
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
        }
        .glass-card { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255,255,255,0.05); 
        }
        .nav-btn { color: #64748b; transition: all 0.3s; }
        .nav-btn.active { color: #3b82f6; text-shadow: 0 0 10px rgba(59, 130, 246, 0.5); }
        input { background: rgba(0,0,0,0.3) !important; border: 1px solid rgba(255,255,255,0.1) !important; color: white !important; }
    </style>
</head>
<body class="pb-24 antialiased">

    <header class="p-5 flex justify-between items-center sticky top-0 z-40 bg-[#020617]/80 backdrop-blur-md">
        <button onclick="location.href='dashboard.php'" class="w-10 h-10 rounded-xl glass-card flex items-center justify-center text-slate-300 hover:text-white transition active:scale-95">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h2 class="text-xl font-bold tracking-wide">Mon profil</h2>
        <div class="w-10"></div>
    </header>

    <div class="max-w-md mx-auto px-5 mt-2 space-y-6">

        <?php if($msg): ?>
            <div class="bg-<?= $msg_type ?>-500/20 border border-<?= $msg_type ?>-500 text-<?= $msg_type ?>-200 px-4 py-2 rounded-xl text-center text-sm">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col items-center">
            <form id="avatarForm" action="" method="POST" enctype="multipart/form-data" class="relative group">
                <div class="absolute inset-0 bg-blue-500 rounded-full blur opacity-40 group-hover:opacity-60 transition"></div>
                <img src="<?= $avatar_url ?>" class="w-28 h-28 rounded-full object-cover border-4 border-[#020617] relative z-10 shadow-2xl">
                <input type="file" name="avatar" id="avatarInput" class="hidden" accept="image/*" onchange="this.form.submit();">
                <div onclick="document.getElementById('avatarInput').click();" class="absolute bottom-1 right-1 z-20 bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center border-2 border-[#020617] cursor-pointer active:scale-90 transition shadow-lg">
                    <i class="fas fa-camera text-xs"></i>
                </div>
            </form>
            
            <h1 class="text-2xl font-black mt-4 text-white tracking-wide"><?= htmlspecialchars($user['name'] ?? 'User Name') ?></h1>
            <p class="text-sm text-slate-400 font-medium">@<?= htmlspecialchars($user['username']) ?></p>
            
            <button onclick="toggleEditModal()" class="mt-4 px-6 py-2 rounded-full border border-blue-500/30 text-blue-400 text-xs font-bold uppercase tracking-widest hover:bg-blue-500/10 transition">
                Modifier les informations
            </button>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="glass-card rounded-2xl p-3 text-center border-b-2 border-purple-500">
                <i class="fas fa-dice text-purple-500 text-xl mb-1"></i>
                <h4 class="text-lg font-black text-white"><?= $total_matches ?></h4>
                <p class="text-[9px] text-slate-400 uppercase tracking-wider">Parties</p>
            </div>
            <div class="glass-card rounded-2xl p-3 text-center border-b-2 border-yellow-500">
                <i class="fas fa-trophy text-yellow-500 text-xl mb-1"></i>
                <h4 class="text-lg font-black text-white"><?= $total_wins ?></h4>
                <p class="text-[9px] text-slate-400 uppercase tracking-wider">Gagnées</p>
            </div>
            <div class="glass-card rounded-2xl p-3 text-center border-b-2 border-blue-500">
                <i class="fas fa-chart-line text-blue-500 text-xl mb-1"></i>
                <h4 class="text-lg font-black text-white"><?= $win_rate ?>%</h4>
                <p class="text-[9px] text-slate-400 uppercase tracking-wider">Taux de victoire</p>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 space-y-4">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Infos du compte</h3>
            
            <div class="flex items-center gap-4 border-b border-white/5 pb-3">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400"><i class="fas fa-envelope"></i></div>
                <div>
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Email</p>
                    <p class="text-sm font-medium text-slate-200"><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400"><i class="fas fa-phone"></i></div>
                <div>
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Téléphone</p>
                    <p class="text-sm font-medium text-slate-200"><?= htmlspecialchars($user['phone'] ?? 'Non défini') ?></p>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-4 flex items-center justify-between border-l-4 border-blue-600">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500">
                    <i class="fas fa-code"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-white">Développeur</p>
                    <p class="text-[10px] text-slate-400 tracking-wider">Imtiaz</p>
                </div>
            </div>
            <a href="https://t.me/appdeveloperbdandappseller" target="_blank" class="text-[10px] font-bold text-blue-400 border border-blue-500/30 px-3 py-1.5 rounded-lg hover:bg-blue-500 hover:text-white transition uppercase">Contact</a>
        </div>

        <div class="space-y-3">
            <a href="change_password.php" class="glass-card p-4 rounded-xl flex items-center justify-between group active:scale-[0.98] transition">
                <div class="flex items-center gap-3">
                    <i class="fas fa-lock text-slate-400 group-hover:text-white"></i>
                    <span class="text-sm font-bold text-slate-300 group-hover:text-white">Changer le mot de passe</span>
                </div>
                <i class="fas fa-chevron-right text-xs text-slate-600"></i>
            </a>

            <a href="support.php" class="glass-card p-4 rounded-xl flex items-center justify-between group active:scale-[0.98] transition">
                <div class="flex items-center gap-3">
                    <i class="fas fa-headset text-slate-400 group-hover:text-white"></i>
                    <span class="text-sm font-bold text-slate-300 group-hover:text-white">Aide et support</span>
                </div>
                <i class="fas fa-chevron-right text-xs text-slate-600"></i>
            </a>

            <a href="logout.php" class="glass-card p-4 rounded-xl flex items-center justify-between border-l-4 border-red-500 active:scale-[0.98] transition">
                <div class="flex items-center gap-3">
                    <i class="fas fa-sign-out-alt text-red-500"></i>
                    <span class="text-sm font-bold text-red-400">Déconnexion</span>
                </div>
                <i class="fas fa-chevron-right text-xs text-slate-600"></i>
            </a>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center px-6">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="toggleEditModal()"></div>
        <div class="glass-card w-full max-w-sm rounded-3xl p-6 relative z-10 border border-white/10">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold">Modifier le profil</h3>
                <i class="fas fa-times text-slate-400 cursor-pointer" onclick="toggleEditModal()"></i>
            </div>
            <form method="POST" class="space-y-5">
                <div>
                    <label class="text-[10px] text-slate-400 uppercase font-bold px-1 mb-1 block">Nom complet</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" class="w-full p-3.5 rounded-xl focus:outline-none" placeholder="Saisir le nom" required>
                </div>
                <div>
                    <label class="text-[10px] text-slate-400 uppercase font-bold px-1 mb-1 block">Numéro de téléphone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="w-full p-3.5 rounded-xl focus:outline-none" placeholder="017xxxxxxxx" required>
                </div>
                <button type="submit" name="update_profile" class="w-full bg-blue-600 py-3.5 rounded-xl font-bold uppercase tracking-widest text-sm shadow-lg shadow-blue-600/30 active:scale-95 transition">Enregistrer</button>
            </form>
        </div>
    </div>

    <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-[#0f172a]/90 backdrop-blur-xl border-t border-white/5 px-6 py-2 flex justify-between items-center z-50">
        <a href="dashboard.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-home text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Accueil</span>
        </a>
        <a href="tournaments.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-trophy text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Événements</span>
        </a>
        <div onclick="location.href='tournaments.php'" class="relative -top-6 bg-gradient-to-br from-blue-500 to-blue-600 w-14 h-14 rounded-2xl flex items-center justify-center shadow-2xl border-4 border-[#020617] cursor-pointer active:scale-90 transition-all">
            <i class="fas fa-gamepad text-white text-xl"></i>
        </div>
        <a href="wallet.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-wallet text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Portefeuille</span>
        </a>
        <a href="profile.php" class="nav-btn active flex flex-col items-center gap-1 p-2">
            <i class="fas fa-user text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Profil</span>
        </a>
    </nav>

    <script>
        function toggleEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.toggle('hidden');
        }
    </script>
</body>
</html>
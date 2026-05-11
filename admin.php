<?php
ob_start(); // Éviter les erreurs dues à l'envoi prématuré de headers
session_start();
require 'config/db.php';

// --- 1. LOGIQUE DE CONNEXION ADMIN ---
if (isset($_POST['login'])) {
    $user = trim($_POST['user']);
    $pass = $_POST['pass'];

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$user]);
    $admin = $stmt->fetch();

    if ($admin) {
        if (password_verify($pass, $admin['password']) || $pass === $admin['password']) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: admin.php");
            exit;
        }
    }
    $error = "Nom d'utilisateur ou mot de passe incorrect !";
}

// --- 2. LOGIQUE DE DÉCONNEXION ---
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin.php");
    exit;
}

// --- 3. AFFICHAGE DE LA PAGE DE CONNEXION (si non connecté) ---
if (!isset($_SESSION['admin_logged_in'])) {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<?php include __DIR__.'/config/pwa.php'; ?><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Ludo Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Rajdhani',sans-serif; background:#0f172a;}</style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-slate-800 p-8 rounded-2xl w-full max-w-sm border border-slate-700 shadow-2xl">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-white tracking-widest">ADMIN<span class="text-blue-500">PANEL</span></h1>
            <p class="text-slate-500 text-sm">Accès sécurisé uniquement</p>
        </div>
        <?php if(isset($error)) echo "<div class='bg-red-500/10 border border-red-500/50 text-red-400 p-3 rounded-lg mb-4 text-center text-sm font-bold'>$error</div>"; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="text-xs text-slate-400 font-bold uppercase ml-1">Nom d'utilisateur</label>
                <input type="text" name="user" class="w-full bg-slate-900 p-3 rounded-xl text-white border border-slate-600 outline-none focus:border-blue-500 transition" required>
            </div>
            <div>
                <label class="text-xs text-slate-400 font-bold uppercase ml-1">Mot de passe</label>
                <input type="password" name="pass" class="w-full bg-slate-900 p-3 rounded-xl text-white border border-slate-600 outline-none focus:border-blue-500 transition" required>
            </div>
            <button name="login" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-3.5 rounded-xl font-bold uppercase tracking-widest shadow-lg shadow-blue-600/20 transition">Se connecter</button>
        </form>
    </div>
</body>
</html>
<?php exit; }

// ==========================================
//          LOGIQUE BACK‑END PRINCIPALE
// ==========================================

$msg = ""; $msgType = "";

// 1. Création de tournoi
if (isset($_POST['add_tournament'])) {
    $sql = "INSERT INTO tournaments (title, entry_fee, prize_pool, start_time, max_players, status) VALUES (?, ?, ?, ?, ?, 'open')";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$_POST['title'], $_POST['fee'], $_POST['prize'], $_POST['time'], $_POST['players']])) {
        $msg = "Tournoi créé avec succès !"; $msgType = "success";
    }
}

// 2. Suppression de tournoi
if (isset($_GET['del_tour'])) {
    $pdo->prepare("DELETE FROM tournaments WHERE id=?")->execute([$_GET['del_tour']]);
    $msg = "Match supprimé !"; $msgType = "success";
}

// 3. Modification de compte utilisateur
if (isset($_POST['update_user'])) {
    $uid = $_POST['user_id'];
    $bal = $_POST['balance'];
    $win = $_POST['win_balance'];
    $stat = $_POST['status'];
    
    if (!empty($_POST['new_pass'])) {
        $hash = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET balance=?, win_balance=?, status=?, password=? WHERE id=?")->execute([$bal, $win, $stat, $hash, $uid]);
    } else {
        $pdo->prepare("UPDATE users SET balance=?, win_balance=?, status=? WHERE id=?")->execute([$bal, $win, $stat, $uid]);
    }
    $msg = "Utilisateur mis à jour !"; $msgType = "success";
}

// 4. Traitement des dépôts
if(isset($_GET['action']) && $_GET['action'] == 'dep_status') {
    $did = $_GET['id']; $status = $_GET['status']; $amount = $_GET['amount']; $uid = $_GET['uid'];
    $check = $pdo->prepare("SELECT status FROM deposits WHERE id=?"); $check->execute([$did]);
    if($check->fetch()['status'] == 'Pending'){
        if($status == 'Approved') {
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$amount, $uid]);
            $pdo->prepare("UPDATE deposits SET status='Approved' WHERE id=?")->execute([$did]);
            $msg="Dépôt approuvé !"; $msgType="success";
        } else {
            $pdo->prepare("UPDATE deposits SET status='Rejected' WHERE id=?")->execute([$did]);
            $msg="Dépôt rejeté !"; $msgType="error";
        }
    }
}

// 5. Traitement des retraits
if(isset($_GET['action']) && $_GET['action'] == 'withdraw_status') {
    $wid = $_GET['id']; $status = $_GET['status'];
    $wd = $pdo->prepare("SELECT * FROM withdraws WHERE id=?"); $wd->execute([$wid]);
    $data = $wd->fetch();
    if($data['status'] == 'Pending'){
        if($status == 'Rejected') {
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$data['amount'], $data['user_id']]);
            $pdo->prepare("UPDATE withdraws SET status='Rejected' WHERE id=?")->execute([$wid]);
            $msg="Remboursé et rejeté !"; $msgType="success";
        } else {
            $pdo->prepare("UPDATE withdraws SET status='Approved' WHERE id=?")->execute([$wid]);
            $msg="Marqué comme payé !"; $msgType="success";
        }
    }
}

// --- Récupération des agrégats de données ---
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pending_dep = $pdo->query("SELECT COUNT(*) FROM deposits WHERE status='Pending'")->fetchColumn();
$pending_wd = $pdo->query("SELECT COUNT(*) FROM withdraws WHERE status='Pending'")->fetchColumn();
$total_balance = $pdo->query("SELECT SUM(balance + win_balance) FROM users")->fetchColumn();

// Récupération des listes détaillées (utilisateurs, tournois, demandes)
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT 100")->fetchAll();
$tournaments = $pdo->query("SELECT * FROM tournaments ORDER BY start_time DESC")->fetchAll();
$withdraws = $pdo->query("SELECT w.*, u.username FROM withdraws w JOIN users u ON w.user_id = u.id WHERE w.status = 'Pending' ORDER BY w.id DESC")->fetchAll();
$deposits = $pdo->query("SELECT d.*, u.username FROM deposits d JOIN users u ON d.user_id = u.id WHERE d.status = 'Pending' ORDER BY d.id DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #e2e8f0; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .sidebar-link { transition: all 0.2s; }
        .sidebar-link:hover { background: #1e293b; color: white; transform: translateX(5px); }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-sm">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="w-64 bg-[#111827] border-r border-slate-800 hidden md:flex flex-col z-50 transition-all">
        <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-[#1f2937]">
            <h2 class="text-xl font-bold text-white tracking-wide">Ludo<span class="text-blue-500">Pro</span></h2>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <p class="px-4 text-[10px] font-bold text-slate-500 uppercase mb-2 mt-2">Principal</p>
            <a href="admin.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-300 rounded-xl font-medium bg-blue-600/10 text-blue-400 border border-blue-600/20">
                <i class="fas fa-home w-5"></i> Tableau de bord
            </a>
            <a href="admin_settings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-400 rounded-xl font-medium">
                <i class="fas fa-cogs w-5"></i> Paramètres
            </a>

            <p class="px-4 text-[10px] font-bold text-slate-500 uppercase mb-2 mt-6">Gestion</p>
            <a href="#tournaments" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-400 rounded-xl font-medium">
                <i class="fas fa-trophy w-5"></i> Tournois
            </a>
            <a href="#users" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-400 rounded-xl font-medium">
                <i class="fas fa-users w-5"></i> Liste des utilisateurs
            </a>
            
            <p class="px-4 text-[10px] font-bold text-slate-500 uppercase mb-2 mt-6">Demandes</p>
            <a href="#deposits" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-400 rounded-xl font-medium">
                <i class="fas fa-wallet w-5"></i> Dépôts 
                <?php if($pending_dep>0): ?><span class="ml-auto bg-blue-500 text-white text-[10px] px-2 rounded-full"><?= $pending_dep ?></span><?php endif; ?>
            </a>
            <a href="#withdraws" class="sidebar-link flex items-center gap-3 px-4 py-3 text-slate-400 rounded-xl font-medium">
                <i class="fas fa-money-bill w-5"></i> Retraits 
                <?php if($pending_wd>0): ?><span class="ml-auto bg-yellow-500 text-white text-[10px] px-2 rounded-full"><?= $pending_wd ?></span><?php endif; ?>
            </a>
            
            <a href="?logout=1" class="sidebar-link flex items-center gap-3 px-4 py-3 text-red-400 rounded-xl font-medium mt-10 hover:bg-red-500/10">
                <i class="fas fa-sign-out-alt w-5"></i> Déconnexion
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-full overflow-hidden bg-[#0f172a]">
        <header class="h-16 bg-[#1e293b]/90 backdrop-blur-md border-b border-slate-800 flex items-center justify-between px-6">
            <button onclick="document.getElementById('sidebar').classList.toggle('hidden'); document.getElementById('sidebar').classList.toggle('absolute'); document.getElementById('sidebar').classList.toggle('h-full');" class="md:hidden text-slate-300 text-lg"><i class="fas fa-bars"></i></button>
            <h2 class="font-bold text-slate-300">Panneau d'administration</h2>
            <button onclick="document.getElementById('addTourModal').classList.remove('hidden')" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-5 py-2 rounded-full font-bold text-xs shadow-lg shadow-blue-600/20 active:scale-95 transition">
                <i class="fas fa-plus mr-2"></i> CRÉER UN MATCH
            </button>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-8">
            <?php if($msg): ?><script>Swal.fire({icon:'<?= $msgType ?>',title:'<?= $msg ?>',toast:true,position:'top-end',showConfirmButton:false,timer:2000, background:'#1e293b', color:'#fff'});</script><?php endif; ?>

            <!-- STATS CARDS -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="glass p-5 rounded-2xl flex flex-col justify-between h-28 border border-slate-700">
                    <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">Utilisateurs totaux</p>
                    <h3 class="text-3xl font-black text-white"><?= $total_users ?></h3>
                    <div class="w-full bg-slate-700 h-1 rounded-full mt-2"><div class="bg-blue-500 h-1 rounded-full" style="width: 70%"></div></div>
                </div>
                <div class="glass p-5 rounded-2xl flex flex-col justify-between h-28 border border-green-500/30 bg-green-500/5">
                    <p class="text-xs text-green-400 uppercase font-bold tracking-wider">Avoirs utilisateurs</p>
                    <h3 class="text-3xl font-black text-white"><?= number_format($total_balance) ?> FCFA</h3>
                </div>
                <div class="glass p-5 rounded-2xl flex flex-col justify-between h-28 border border-blue-500/30">
                    <p class="text-xs text-blue-400 uppercase font-bold tracking-wider">Dépôts en attente</p>
                    <h3 class="text-3xl font-black text-white"><?= $pending_dep ?></h3>
                </div>
                <div class="glass p-5 rounded-2xl flex flex-col justify-between h-28 border border-yellow-500/30">
                    <p class="text-xs text-yellow-400 uppercase font-bold tracking-wider">Retraits en attente</p>
                    <h3 class="text-3xl font-black text-white"><?= $pending_wd ?></h3>
                </div>
            </div>

            <!-- TOURNAMENTS SECTION (STYLED) -->
            <div id="tournaments" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-white text-lg"><i class="fas fa-trophy text-yellow-500 mr-2"></i> Tournois</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if(count($tournaments) == 0) echo "<p class='text-slate-500'>Aucun match créé pour l'instant.</p>"; ?>
                    <?php foreach($tournaments as $t): ?>
                    <div class="glass rounded-xl overflow-hidden border border-slate-700 group hover:border-blue-500/50 transition relative">
                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            <span class="text-[10px] font-bold uppercase px-2 py-1 rounded-md <?= $t['status']=='live'?'bg-red-500 text-white animate-pulse':'bg-green-500 text-white' ?>">
                                <?= $t['status'] ?>
                            </span>
                        </div>

                        <div class="p-5">
                            <h4 class="text-white font-bold text-lg mb-1"><?= htmlspecialchars($t['title']) ?></h4>
                            <p class="text-xs text-slate-400 mb-4"><i class="far fa-calendar-alt mr-1"></i> <?= date('d M, h:i A', strtotime($t['start_time'])) ?></p>
                            
                            <div class="flex justify-between items-center bg-slate-800/50 p-3 rounded-lg border border-slate-700 mb-4">
                                <div class="text-center">
                                    <p class="text-[10px] text-slate-500 uppercase">Mise</p>
                                    <p class="text-sm font-bold text-white"><?= $t['entry_fee'] ?> FCFA</p>
                                </div>
                                <div class="w-px h-8 bg-slate-700"></div>
                                <div class="text-center">
                                    <p class="text-[10px] text-slate-500 uppercase">Gain</p>
                                    <p class="text-sm font-bold text-green-400"><?= $t['prize_pool'] ?> FCFA</p>
                                </div>
                                <div class="w-px h-8 bg-slate-700"></div>
                                <div class="text-center">
                                    <p class="text-[10px] text-slate-500 uppercase">Joueurs</p>
                                    <p class="text-sm font-bold text-blue-400"><?= $t['max_players'] ?></p>
                                </div>
                            </div>

                            <a href="?del_tour=<?= $t['id'] ?>" onclick="return confirm('Supprimer ce match ?')" class="w-full block text-center bg-red-500/10 hover:bg-red-500 hover:text-white text-red-500 py-2 rounded-lg text-xs font-bold transition">
                                <i class="fas fa-trash mr-1"></i> SUPPRIMER LE MATCH
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- USERS SECTION -->
            <div id="users" class="glass rounded-xl p-5 border border-slate-700">
                <div class="flex flex-col md:flex-row justify-between items-center mb-5 gap-3">
                    <h3 class="font-bold text-white text-lg"><i class="fas fa-users text-purple-500 mr-2"></i> Liste des utilisateurs</h3>
                    <div class="relative w-full md:w-64">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" id="searchUser" onkeyup="filterUsers()" placeholder="Rechercher un utilisateur..." class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-9 pr-3 py-2 text-xs text-white outline-none focus:border-blue-500 transition">
                    </div>
                </div>
                
                <div class="overflow-x-auto max-h-[500px]">
                    <table class="w-full text-left text-slate-400">
                        <thead class="bg-slate-800 text-xs uppercase sticky top-0">
                            <tr><th class="p-3">Utilisateur</th><th class="p-3">Téléphone</th><th class="p-3">Solde</th><th class="p-3">Statut</th><th class="p-3 text-right">Action</th></tr>
                        </thead>
                        <tbody id="userTableBody" class="divide-y divide-slate-700">
                            <?php foreach($users as $u): ?>
                            <tr class="hover:bg-slate-800/50 user-row transition">
                                <td class="p-3 text-white font-bold username"><?= $u['username'] ?></td>
                                <td class="p-3 text-xs font-mono text-slate-300 phone"><?= $u['phone'] ?? 'N/A' ?></td>
                                <td class="p-3">
                                    <span class="text-green-400 font-bold"><?= $u['balance'] ?> FCFA</span>
                                    <span class="text-[10px] text-slate-500 block">Gains : <?= $u['win_balance'] ?> FCFA</span>
                                </td>
                                <td class="p-3"><span class="text-[10px] px-2 py-0.5 rounded uppercase font-bold <?= $u['status']=='banned'?'bg-red-500 text-white':'bg-emerald-500/10 text-emerald-500' ?>"><?= $u['status'] ?></span></td>
                                <td class="p-3 text-right">
                                    <button onclick='openUserModal(<?= json_encode($u) ?>)' class="bg-slate-700 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">Modifier</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- REQUESTS GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Deposits -->
                <div id="deposits" class="glass rounded-xl p-5 border border-slate-700 h-96 overflow-y-auto">
                    <h3 class="font-bold text-white mb-4 sticky top-0 bg-[#151e2e] py-2 border-b border-slate-700 z-10"><i class="fas fa-arrow-down text-emerald-500 mr-2"></i> Dépôts en attente</h3>
                    <?php if(empty($deposits)) echo "<div class='text-center text-slate-500 py-10'><i class='fas fa-check-circle text-2xl mb-2'></i><p>Tout est traité !</p></div>"; ?>
                    <?php foreach($deposits as $d): ?>
                    <div class="flex justify-between items-center bg-slate-800/50 p-4 rounded-xl mb-3 border border-slate-700 hover:border-emerald-500/30 transition">
                        <div>
                            <p class="font-bold text-white text-sm"><?= $d['username'] ?> <span class="text-[10px] text-blue-400 bg-blue-500/10 px-1 rounded"><?= $d['method'] ?></span></p>
                            <p class="text-[10px] text-slate-400 mt-1 font-mono bg-slate-900 px-1 rounded inline-block">Trx: <?= $d['transaction_id'] ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-lg text-emerald-400"><?= $d['amount'] ?> FCFA</p>
                            <div class="flex gap-2 mt-2">
                                <a href="?action=dep_status&status=Approved&id=<?= $d['id'] ?>&amount=<?= $d['amount'] ?>&uid=<?= $d['user_id'] ?>" onclick="return confirm('Approuver ce dépôt ?')" class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-500 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition"><i class="fas fa-check"></i></a>
                                <a href="?action=dep_status&status=Rejected&id=<?= $d['id'] ?>&amount=0&uid=0" onclick="return confirm('Rejeter ce dépôt ?')" class="w-8 h-8 rounded-lg bg-red-500/20 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition"><i class="fas fa-times"></i></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Withdraws -->
                <div id="withdraws" class="glass rounded-xl p-5 border border-slate-700 h-96 overflow-y-auto">
                    <h3 class="font-bold text-white mb-4 sticky top-0 bg-[#151e2e] py-2 border-b border-slate-700 z-10"><i class="fas fa-arrow-up text-red-500 mr-2"></i> Retraits en attente</h3>
                    <?php if(empty($withdraws)) echo "<div class='text-center text-slate-500 py-10'><i class='fas fa-check-circle text-2xl mb-2'></i><p>Tout est traité !</p></div>"; ?>
                    <?php foreach($withdraws as $w): ?>
                    <div class="flex justify-between items-center bg-slate-800/50 p-4 rounded-xl mb-3 border border-slate-700 hover:border-red-500/30 transition">
                        <div>
                            <p class="font-bold text-white text-sm"><?= $w['username'] ?> <span class="text-[10px] text-yellow-400 bg-yellow-500/10 px-1 rounded"><?= $w['method'] ?></span></p>
                            <p class="text-[10px] text-slate-400 mt-1 font-mono"><?= $w['account_number'] ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-lg text-white"><?= $w['amount'] ?> FCFA</p>
                            <div class="flex gap-2 mt-2">
                                <a href="?action=withdraw_status&status=Approved&id=<?= $w['id'] ?>" onclick="return confirm('Marquer comme payé ?')" class="px-3 py-1.5 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500 hover:text-white text-[10px] font-bold transition">PAYER</a>
                                <a href="?action=withdraw_status&status=Rejected&id=<?= $w['id'] ?>" onclick="return confirm('Rembourser et rejeter ?')" class="px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white text-[10px] font-bold transition">REMBOURSER</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </main>

    <!-- ADD TOURNAMENT MODAL -->
    <div id="addTourModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 z-[60]">
        <div class="bg-slate-800 p-6 rounded-2xl w-full max-w-md border border-slate-700 shadow-2xl transform transition-all scale-100">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2"><i class="fas fa-gamepad text-blue-500"></i> Créer un match</h3>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Titre</label>
                    <input type="text" name="title" placeholder="ex. Grand Tournoi" class="w-full bg-slate-900 p-3 rounded-xl text-white border border-slate-700 outline-none focus:border-blue-500 mt-1" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Mise d'entrée</label>
                        <input type="number" name="fee" class="w-full bg-slate-900 p-3 rounded-xl text-white border border-slate-700 outline-none mt-1" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Gain</label>
                        <input type="number" name="prize" class="w-full bg-slate-900 p-3 rounded-xl text-white border border-slate-700 outline-none mt-1" required>
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Heure de début</label>
                    <input type="datetime-local" name="time" class="w-full bg-slate-900 p-3 rounded-xl text-white border border-slate-700 outline-none mt-1" required>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Joueurs</label>
                    <select name="players" class="w-full bg-slate-900 p-3 rounded-xl text-white border border-slate-700 outline-none mt-1">
                        <option value="2">2 Joueurs</option>
                        <option value="4">4 Joueurs</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addTourModal').classList.add('hidden')" class="flex-1 bg-slate-700 hover:bg-slate-600 py-3 rounded-xl text-white font-bold transition">Annuler</button>
                    <button name="add_tournament" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 py-3 rounded-xl text-white font-bold transition shadow-lg">Créer le match</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT USER MODAL -->
    <div id="userModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 z-[60]">
        <div class="bg-slate-800 p-6 rounded-2xl w-full max-w-sm border border-slate-700 shadow-2xl">
            <h3 class="text-lg font-bold text-white mb-1">Modifier l'utilisateur</h3>
            <p id="modalUserName" class="text-blue-400 text-sm mb-4 font-mono"></p>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="user_id" id="modalUserId">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Solde principal</label>
                        <input type="number" name="balance" id="modalBalance" class="w-full bg-slate-900 p-2.5 rounded-xl text-white border border-slate-700 mt-1">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Solde gains</label>
                        <input type="number" name="win_balance" id="modalWin" class="w-full bg-slate-900 p-2.5 rounded-xl text-white border border-slate-700 mt-1">
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Statut</label>
                    <select name="status" id="modalStatus" class="w-full bg-slate-900 p-3 rounded-xl text-white border border-slate-700 mt-1">
                        <option value="active">Actif</option>
                        <option value="banned">Banni</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Nouveau mot de passe (optionnel)</label>
                    <input type="text" name="new_pass" class="w-full bg-slate-900 p-3 rounded-xl text-white border border-slate-700 mt-1" placeholder="Laisser vide pour conserver">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('userModal').classList.add('hidden')" class="flex-1 bg-slate-700 hover:bg-slate-600 py-3 rounded-xl text-white font-bold transition">Fermer</button>
                    <button name="update_user" class="flex-1 bg-emerald-600 hover:bg-emerald-500 py-3 rounded-xl text-white font-bold transition shadow-lg">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUserModal(u) {
            document.getElementById('userModal').classList.remove('hidden');
            document.getElementById('modalUserId').value = u.id;
            document.getElementById('modalUserName').innerText = u.username;
            document.getElementById('modalBalance').value = u.balance;
            document.getElementById('modalWin').value = u.win_balance;
            document.getElementById('modalStatus').value = u.status;
        }

        function filterUsers() {
            let input = document.getElementById('searchUser').value.toLowerCase();
            let rows = document.getElementsByClassName('user-row');
            for (let row of rows) {
                let name = row.querySelector('.username').innerText.toLowerCase();
                let phone = row.querySelector('.phone').innerText.toLowerCase();
                if (name.includes(input) || phone.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        }
    </script>
</body>
</html>
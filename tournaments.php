<?php
require 'config/db.php';
requireAuth(); // Fonction de vérification de session

$user_id = $_SESSION['user_id'];

// 1. Récupérer le dernier solde et les données utilisateur
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// 2. Logique de filtrage
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$fee_filter = isset($_GET['fee']) ? $_GET['fee'] : 'all';

// 3. Logique de requête (optimisée)
$query = "SELECT t.*, 
          (SELECT COUNT(*) FROM tournament_participants tp WHERE tp.tournament_id = t.id) as joined_count 
          FROM tournaments t WHERE 1=1";

$params = [];

if ($status_filter !== 'all') {
    $query .= " AND t.status = ?";
    $params[] = $status_filter;
} else {
    $query .= " AND t.status != 'ended'"; // Masquer les tournois déjà terminés
}

if ($fee_filter === 'free') {
    $query .= " AND t.entry_fee = 0";
} elseif ($fee_filter === 'paid') {
    $query .= " AND t.entry_fee > 0";
}

$query .= " ORDER BY t.status DESC, t.start_time ASC"; // D'abord les live, puis par date
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tournaments = $stmt->fetchAll();

// 4. Liste des tournois auxquels l'utilisateur a déjà participé
$joined_stmt = $pdo->prepare("SELECT tournament_id FROM tournament_participants WHERE user_id = ?");
$joined_stmt->execute([$user_id]);
$my_joined = $joined_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<?php include __DIR__.'/config/pwa.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournois - Ludo Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #e2e8f0; font-family: 'Inter', sans-serif; }
        /* আগের গ্লাস ইফেক্ট ডিজাইন */
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .glass-card { background: linear-gradient(145deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.9)); border: 1px solid #334155; }
        .glass-card:hover { border-color: #3b82f6; transform: translateY(-2px); }
        
        /* কাস্টম স্ক্রলবার */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="antialiased min-h-screen pb-20">

<!-- Header Section -->
<header class="sticky top-0 z-50 glass px-6 py-4 flex justify-between items-center mb-8">
    <div class="flex items-center gap-4">
        <a href="index.php" class="h-10 w-10 flex items-center justify-center rounded-full bg-slate-800 text-white hover:bg-slate-700 transition border border-slate-700">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h2 class="text-xl font-bold text-white">Tous les <span class="text-blue-500">tournois</span></h2>
    </div>
    
    <div class="flex items-center gap-4">
        <div class="hidden md:flex flex-col items-end leading-tight">
            <span class="text-[10px] text-slate-400 uppercase tracking-wider">Solde total</span>
            <!-- ব্যালেন্স + উইনিং ব্যালেন্স একসাথে দেখানো হচ্ছে -->
            <span class="font-bold text-green-400 text-lg">$<?= number_format($user['balance'] + $user['win_balance'], 2) ?></span>
        </div>
        <div class="h-10 w-10 rounded-full border-2 border-blue-500 p-0.5 relative">
            <img src="assets/avatars/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>" class="w-full h-full rounded-full object-cover">
            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-slate-900 rounded-full"></span>
        </div>
    </div>
</header>

<main class="max-w-6xl mx-auto px-6">
    
    <!-- Alerts/Notifications -->
    <?php if(isset($_GET['msg'])): ?>
        <div class="mb-8 p-4 rounded-xl <?= $_GET['status'] == 'success' ? 'bg-green-500/10 border border-green-500/20 text-green-400' : 'bg-red-500/10 border border-red-500/20 text-red-400' ?> flex items-center gap-3 shadow-lg">
            <div class="h-8 w-8 rounded-full flex items-center justify-center <?= $_GET['status'] == 'success' ? 'bg-green-500/20' : 'bg-red-500/20' ?>">
                <i class="fas <?= $_GET['status'] == 'success' ? 'fa-check' : 'fa-exclamation' ?>"></i>
            </div>
            <span class="font-medium"><?= htmlspecialchars($_GET['msg']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Filters Section -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-8">
        <div class="flex bg-slate-900/50 p-1.5 rounded-xl border border-slate-800 w-full md:w-auto overflow-x-auto">
            <a href="tournaments.php?status=all" class="px-6 py-2.5 rounded-lg text-sm font-bold transition whitespace-nowrap <?= $status_filter == 'all' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Tous les matchs</a>
            <a href="tournaments.php?status=live" class="px-6 py-2.5 rounded-lg text-sm font-bold transition whitespace-nowrap <?= $status_filter == 'live' ? 'bg-red-600 text-white shadow-lg shadow-red-900/50' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">En direct</a>
            <a href="tournaments.php?status=open" class="px-6 py-2.5 rounded-lg text-sm font-bold transition whitespace-nowrap <?= $status_filter == 'open' ? 'bg-green-600 text-white shadow-lg shadow-green-900/50' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">À venir</a>
        </div>

        <div class="w-full md:w-auto">
            <form action="" method="GET">
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                <div class="relative">
                    <i class="fas fa-filter absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <select name="fee" onchange="this.form.submit()" class="w-full md:w-48 bg-slate-900 border border-slate-800 text-white text-sm rounded-xl pl-10 pr-4 py-3 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition appearance-none cursor-pointer">
                        <option value="all">Tous les frais d'entrée</option>
                        <option value="free" <?= $fee_filter == 'free' ? 'selected' : '' ?>>Entrée gratuite</option>
                        <option value="paid" <?= $fee_filter == 'paid' ? 'selected' : '' ?>>Entrée payante</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Tournaments Grid -->
    <?php if(count($tournaments) > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($tournaments as $t): 
            $is_joined = in_array($t['id'], $my_joined);
            $is_full = $t['joined_count'] >= $t['max_players'];
            $progress = ($t['joined_count'] / $t['max_players']) * 100;
        ?>
        <div class="glass-card rounded-2xl overflow-hidden group transition duration-300">
            
            <!-- Card Image Area (Previous Design) -->
            <div class="relative h-32 bg-gradient-to-br from-blue-600/20 to-purple-600/20 flex items-center justify-center group-hover:from-blue-600/30 group-hover:to-purple-600/30 transition">
                <i class="fas fa-trophy text-5xl text-blue-400/30 group-hover:scale-110 group-hover:text-blue-400/50 transition duration-500 drop-shadow-lg"></i>
                
                <div class="absolute top-3 left-3">
                    <span class="bg-slate-900/80 backdrop-blur-md text-[10px] text-slate-300 px-2.5 py-1 rounded-md border border-white/10 font-mono">
                        ID: #<?= str_pad($t['id'], 4, '0', STR_PAD_LEFT) ?>
                    </span>
                </div>

                <?php if($t['status'] == 'live'): ?>
                <div class="absolute top-3 right-3">
                    <span class="flex items-center gap-1.5 bg-red-500 text-white text-[10px] font-bold px-2.5 py-1 rounded shadow-lg shadow-red-500/50 animate-pulse">
                        <span class="w-1.5 h-1.5 bg-white rounded-full"></span> LIVE
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Card Content -->
            <div class="p-5">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-lg font-bold text-white truncate w-3/4" title="<?= htmlspecialchars($t['title']) ?>">
                        <?= htmlspecialchars($t['title']) ?>
                    </h3>
                    <div class="text-right">
                        <span class="block text-[10px] text-slate-400 uppercase">Carte</span>
                        <span class="text-xs font-bold text-blue-400">Classique</span>
                    </div>
                </div>

                <p class="text-xs text-slate-400 mb-5 flex items-center gap-2 bg-slate-800/50 p-2 rounded-lg border border-slate-700/50">
                    <i class="far fa-calendar-alt text-blue-500"></i> 
                    <?= date('M d, Y • h:i A', strtotime($t['start_time'])) ?>
                </p>

                <!-- Prize & Fee -->
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-slate-800/50 p-3 rounded-xl border border-white/5 group-hover:border-yellow-500/20 transition">
                        <p class="text-[10px] text-slate-500 uppercase font-bold">Cagnotte</p>
                        <p class="text-lg font-bold text-yellow-400">$<?= number_format($t['prize_pool']) ?></p>
                    </div>
                    <div class="bg-slate-800/50 p-3 rounded-xl border border-white/5 group-hover:border-green-500/20 transition">
                        <p class="text-[10px] text-slate-500 uppercase font-bold">Frais d'entrée</p>
                        <p class="text-lg font-bold text-green-400"><?= $t['entry_fee'] == 0 ? 'GRATUIT' : '$'.number_format($t['entry_fee']) ?></p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="flex justify-between text-xs mb-2">
                        <span class="text-slate-400">Joueurs inscrits</span>
                        <span class="<?= $is_full ? 'text-red-400' : 'text-white' ?> font-bold"><?= $t['joined_count'] ?>/<?= $t['max_players'] ?></span>
                    </div>
                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        <div class="<?= $is_full ? 'bg-red-500' : 'bg-blue-500' ?> h-full rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(59,130,246,0.5)]" style="width: <?= $progress ?>%"></div>
                    </div>
                </div>

                <!-- Action Buttons (Logic Updated, Design Restored) -->
                <div>
                    <?php if ($t['status'] == 'live' && $is_joined): ?>
                        <!-- Scenario 1: Live & Joined -> Play Now -->
                        <a href="playmatch.php?id=<?= $t['id'] ?>" class="w-full bg-gradient-to-r from-green-600 to-green-500 hover:from-green-500 hover:to-green-400 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-green-600/20 transition flex items-center justify-center gap-2 transform active:scale-95">
                            <i class="fas fa-play-circle text-lg animate-pulse"></i> JOUER
                        </a>

                    <?php elseif ($is_joined): ?>
                        <!-- Scenario 2: Just Joined -->
                        <button disabled class="w-full bg-slate-700/50 text-slate-400 font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 cursor-not-allowed border border-slate-700">
                            <i class="fas fa-check-circle text-green-500"></i> DÉJÀ INSCRIT
                        </button>

                    <?php elseif ($t['status'] == 'live' && !$is_joined): ?>
                         <!-- Scenario 3: Live but missed -->
                        <button disabled class="w-full bg-slate-800 text-slate-500 font-bold py-3.5 rounded-xl cursor-not-allowed border border-slate-700">
                            MATCH COMMENCÉ
                        </button>

                    <?php elseif ($is_full): ?>
                        <!-- Scenario 4: Full -->
                        <button disabled class="w-full border border-red-500/30 text-red-500 bg-red-500/5 font-bold py-3.5 rounded-xl cursor-not-allowed">
                            PLACES COMPLÈTES
                        </button>

                    <?php else: ?>
                        <!-- Scenario 5: Ready to Join -->
                        <form action="api/join_tournament.php" method="POST">
                            <input type="hidden" name="tournament_id" value="<?= $t['id'] ?>">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center justify-center gap-2 active:scale-95 group-hover:shadow-blue-600/50">
                                REJOINDRE <i class="fas fa-arrow-right text-sm opacity-70"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- No Data State (Designed) -->
    <div class="flex flex-col items-center justify-center py-24 glass rounded-3xl border-2 border-dashed border-slate-700 text-center">
        <div class="w-24 h-24 bg-slate-800/80 rounded-full flex items-center justify-center mb-6 shadow-inner">
            <i class="fas fa-search text-4xl text-slate-600"></i>
        </div>
        <h3 class="text-2xl font-bold text-white mb-2">Aucun tournoi trouvé</h3>
        <p class="text-slate-400 max-w-xs mx-auto">Aucun match ne correspond aux filtres sélectionnés.</p>
        <a href="tournaments.php?status=all" class="mt-6 text-blue-400 hover:text-blue-300 font-medium transition">Réinitialiser les filtres</a>
    </div>
    <?php endif; ?>
</main>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php'; 

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

try {
    // সরাসরি users টেবিল থেকে balance এবং bonus_balance আনা হচ্ছে
    $stmt = $pdo->prepare("SELECT balance, bonus_balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ডাটাবেস থেকে ভ্যালু সেট করা (না থাকলে ডিফল্ট ০.০০)
    $main_balance = isset($user['balance']) ? $user['balance'] : 0.00;
    $bonus_balance = isset($user['bonus_balance']) ? $user['bonus_balance'] : 0.00;

    // ট্রানজ্যাকশন হিস্ট্রি
    $stmt_tx = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 5");
    $stmt_tx->execute([$user_id]);
    $transactions = $stmt_tx->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { 
    error_log($e->getMessage()); 
    $main_balance = 0.00;
    $bonus_balance = 0.00;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<?php include __DIR__.'/config/pwa.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Portefeuille - Ludo Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #020617; color: #f1f5f9; font-family: 'Rajdhani', sans-serif; background-image: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 70%); }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .balance-card { background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.5); }
        .nav-btn { color: #64748b; }
        .nav-btn.active { color: #3b82f6; }
    </style>
</head>
<body class="pb-28 antialiased">

    <header class="p-5 flex justify-between items-center sticky top-0 z-40 bg-[#020617]/80 backdrop-blur-md">
        <button onclick="location.href='dashboard.php'" class="w-10 h-10 rounded-xl glass-card flex items-center justify-center text-slate-300 active:scale-90 transition">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h2 class="text-xl font-bold tracking-widest uppercase">Mon portefeuille</h2>
        <div class="w-10"></div>
    </header>

    <div class="max-w-md mx-auto px-5 space-y-6 pt-2">
        
        <div class="balance-card rounded-[2.5rem] p-8 relative overflow-hidden">
            <p class="text-blue-100/70 text-[10px] font-bold uppercase tracking-widest mb-1">Solde disponible</p>
            <h1 class="text-5xl font-black text-white">
                <span class="text-2xl font-medium">FCFA </span><?= number_format($main_balance, 2) ?>
            </h1>
            
            <div class="flex gap-3 mt-8">
                <div class="flex-1 bg-black/20 rounded-2xl p-3 backdrop-blur-sm">
                    <p class="text-[9px] text-blue-100/50 uppercase font-bold">Solde dépôt</p>
                    <p class="text-sm font-bold text-green-400">FCFA <?= number_format($main_balance, 2) ?></p>
                </div>
                <div class="flex-1 bg-black/20 rounded-2xl p-3 backdrop-blur-sm border border-yellow-500/20">
                    <p class="text-[9px] text-yellow-100/50 uppercase font-bold">Solde bonus</p>
                    <p class="text-sm font-bold text-yellow-400">FCFA <?= number_format($bonus_balance, 2) ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-3 text-center">
            <a href="deposit.php" class="space-y-2 group">
                <div class="w-full aspect-square rounded-2xl glass-card flex items-center justify-center text-green-500 text-xl group-active:scale-90 transition">
                    <i class="fas fa-plus"></i>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Dépôt</p>
            </a>
            <a href="withdraw.php" class="space-y-2 group">
                <div class="w-full aspect-square rounded-2xl glass-card flex items-center justify-center text-blue-500 text-xl group-active:scale-90 transition">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Retrait</p>
            </a>
            <a href="refer.php" class="space-y-2 group">
                <div class="w-full aspect-square rounded-2xl glass-card flex items-center justify-center text-yellow-500 text-xl group-active:scale-90 transition">
                    <i class="fas fa-gift"></i>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Bonus</p>
            </a>
            <a href="transactions.php" class="space-y-2 group">
                <div class="w-full aspect-square rounded-2xl glass-card flex items-center justify-center text-purple-500 text-xl group-active:scale-90 transition">
                    <i class="fas fa-history"></i>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Historique</p>
            </a>
        </div>

        <div class="glass-card rounded-3xl p-5 border-l-4 border-blue-500">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Partenaires sécurisés</p>
            <div class="flex justify-between items-center grayscale opacity-60">
                <img src="https://freelogopng.com/images/all_img/1656234745bkash-app-logo-png.png" class="h-5" alt="bKash">
                <img src="https://freelogopng.com/images/all_img/1679248787Nagad-Logo-PNG.png" class="h-5" alt="Nagad">
                <img src="https://logowik.com/content/uploads/images/rocket4947.logowik.com.webp" class="h-6" alt="Rocket">
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 px-1">Transactions</h3>
            <div class="space-y-3">
                <?php if(empty($transactions)): ?>
                    <div class="glass-card rounded-[2rem] py-12 text-center border-dashed border-2 border-white/5">
                        <i class="fas fa-receipt text-slate-700 text-3xl mb-3"></i>
                        <p class="text-slate-500 text-[10px] font-bold uppercase">Aucune activité trouvée</p>
                    </div>
                <?php else: ?>
                    <?php foreach($transactions as $tx): ?>
                        <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-[#0f172a]/95 backdrop-blur-xl border-t border-white/5 px-6 py-2 flex justify-between items-center z-50 rounded-t-[2rem]">
        <a href="dashboard.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-home text-lg"></i>
            <span class="text-[9px] font-bold">ACCUEIL</span>
        </a>
        <a href="tournaments.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-trophy text-lg"></i>
            <span class="text-[9px] font-bold">ÉVÉNEMENTS</span>
        </a>
        <div onclick="location.href='tournaments.php'" class="relative -top-8 bg-gradient-to-tr from-blue-600 to-purple-600 w-16 h-16 rounded-[1.5rem] flex items-center justify-center shadow-2xl border-4 border-[#020617] active:scale-90 transition">
            <i class="fas fa-play text-white text-xl ml-1"></i>
        </div>
        <a href="wallet.php" class="nav-btn active flex flex-col items-center gap-1 p-2">
            <i class="fas fa-wallet text-lg"></i>
            <span class="text-[9px] font-bold">PORTEFEUILLE</span>
        </a>
        <a href="profile.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-user text-lg"></i>
            <span class="text-[9px] font-bold">PROFIL</span>
        </a>
    </nav>
</body>
</html>
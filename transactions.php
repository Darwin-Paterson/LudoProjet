<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

try {
    // deposits এবং withdraws উভয় টেবিল থেকে ডাটা এনে একসাথে দেখানো (UNION ব্যবহার করে)
    // এতে ইউজার এক পেজেই সব লেনদেন দেখতে পাবে
    $query = "
        (SELECT method, amount, status, created_at, 'deposit' as type FROM deposits WHERE user_id = ?)
        UNION ALL
        (SELECT method, amount, status, created_at, 'withdraw' as type FROM withdraws WHERE user_id = ?)
        ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $user_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $history = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Historique des transactions - Ludo Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #020617; color: #f1f5f9; font-family: 'Rajdhani', sans-serif; background-image: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 70%); }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .nav-btn { color: #64748b; }
        .nav-btn.active { color: #3b82f6; }
        
        /* Status Colors */
        .status-pending { color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
        .status-approved { color: #10b981; background: rgba(16, 185, 129, 0.1); }
        .status-rejected { color: #ef4444; background: rgba(239, 68, 68, 0.1); }
    </style>
</head>
<body class="pb-24 antialiased">

    <header class="p-5 flex justify-between items-center sticky top-0 z-40 bg-[#020617]/80 backdrop-blur-md border-b border-white/5">
        <button onclick="location.href='wallet.php'" class="w-10 h-10 rounded-xl glass-card flex items-center justify-center text-slate-300 active:scale-95 transition">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h2 class="text-xl font-bold tracking-widest uppercase">Historique</h2>
        <div class="w-10"></div>
    </header>

    <div class="max-w-md mx-auto px-5 mt-6 space-y-4">
        
        <?php if (empty($history)): ?>
            <div class="flex flex-col items-center justify-center py-20 opacity-40">
                <div class="w-20 h-20 rounded-full bg-slate-800 flex items-center justify-center mb-4">
                    <i class="fas fa-receipt text-3xl"></i>
                </div>
                <p class="text-lg font-bold">Aucune transaction pour le moment</p>
                <p class="text-sm">Votre historique de paiement apparaîtra ici.</p>
            </div>
        <?php else: ?>
            
            <?php foreach ($history as $row): ?>
                <div class="glass-card p-4 rounded-2xl flex items-center justify-between border-l-4 <?= $row['type'] == 'deposit' ? 'border-green-500' : 'border-red-500' ?>">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl <?= $row['type'] == 'deposit' ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' ?>">
                            <i class="fas <?= $row['type'] == 'deposit' ? 'fa-arrow-down' : 'fa-arrow-up' ?>"></i>
                        </div>
                        
                        <div>
                            <h4 class="font-bold text-white"><?= htmlspecialchars($row['method']) ?> <?= ucfirst($row['type']) ?></h4>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                                <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-lg font-black <?= $row['type'] == 'deposit' ? 'text-green-400' : 'text-red-400' ?>">
                            <?= $row['type'] == 'deposit' ? '+' : '-' ?>৳<?= number_format($row['amount'], 2) ?>
                        </p>
                        <span class="text-[9px] px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter 
                            <?= $row['status'] == 'Approved' || $row['status'] == 'Success' ? 'status-approved' : ($row['status'] == 'Rejected' ? 'status-rejected' : 'status-pending') ?>">
                            <?= $row['status'] ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <div class="max-w-md mx-auto px-5 mt-8 pb-10">
        <p class="text-center text-[10px] text-slate-600 uppercase tracking-[3px]">Fin des transactions</p>
    </div>

</body>
</html>
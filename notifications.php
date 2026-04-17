<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

try {
    // ইউজারের নিজস্ব নোটিফিকেশন এবং গ্লোবাল (user_id IS NULL) নোটিফিকেশন আনা হচ্ছে
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // পেজ ভিজিট করলে সব নোটিফিকেশনকে 'read' হিসেবে মার্ক করা
    $update_stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $update_stmt->execute([$user_id]);

} catch (PDOException $e) { 
    error_log($e->getMessage());
    $notifications = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Notifications - Ludo Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #020617; color: #f1f5f9; font-family: 'Rajdhani', sans-serif; background-image: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 70%); }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .unread-dot { width: 8px; height: 8px; background: #3b82f6; border-radius: 50%; box-shadow: 0 0 10px #3b82f6; }
        
        /* Icon Colors based on Type */
        .icon-info { color: #3b82f6; background: rgba(59, 130, 246, 0.1); }
        .icon-success { color: #10b981; background: rgba(16, 185, 129, 0.1); }
        .icon-warning { color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
        .icon-offer { color: #ec4899; background: rgba(236, 72, 153, 0.1); }
    </style>
</head>
<body class="pb-10 antialiased">

    <header class="p-5 flex justify-between items-center sticky top-0 z-40 bg-[#020617]/80 backdrop-blur-md border-b border-white/5">
        <button onclick="history.back()" class="w-10 h-10 rounded-xl glass-card flex items-center justify-center text-slate-300">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h2 class="text-xl font-bold tracking-widest uppercase">Notifications</h2>
        <button class="text-slate-500 text-xs font-bold uppercase tracking-tighter">Tout effacer</button>
    </header>

    <div class="max-w-md mx-auto px-5 py-6 space-y-4">
        
        <?php if (empty($notifications)): ?>
            <div class="flex flex-col items-center justify-center py-24 opacity-30">
                <i class="fas fa-bell-slash text-6xl mb-4"></i>
                <p class="text-lg font-bold">Boîte vide</p>
                <p class="text-xs uppercase tracking-widest">Aucune nouveauté pour le moment</p>
            </div>
        <?php else: ?>
            
            <?php foreach ($notifications as $note): ?>
                <?php 
                    // টাইপ অনুযায়ী আইকন সেট করা
                    $icon = 'fa-bell';
                    $color_class = 'icon-info';
                    if($note['type'] == 'success') { $icon = 'fa-check-circle'; $color_class = 'icon-success'; }
                    if($note['type'] == 'warning') { $icon = 'fa-exclamation-triangle'; $color_class = 'icon-warning'; }
                    if($note['type'] == 'offer') { $icon = 'fa-gift'; $color_class = 'icon-offer'; }
                ?>
                
                <div class="glass-card p-4 rounded-3xl flex items-start gap-4 relative overflow-hidden transition active:scale-[0.98] <?= $note['is_read'] == 0 ? 'bg-white/5' : '' ?>">
                    
                    <div class="w-12 h-12 rounded-2xl flex-shrink-0 flex items-center justify-center text-xl <?= $color_class ?>">
                        <i class="fas <?= $icon ?>"></i>
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-sm text-white pr-4"><?= htmlspecialchars($note['title']) ?></h4>
                            <?php if($note['is_read'] == 0): ?>
                                <div class="unread-dot mt-1"></div>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed mb-2">
                            <?= htmlspecialchars($note['message']) ?>
                        </p>
                        <p class="text-[9px] font-bold text-slate-600 uppercase tracking-widest">
                            <i class="far fa-clock mr-1"></i> <?= date('h:i A | d M Y', strtotime($note['created_at'])) ?>
                        </p>
                    </div>

                </div>
            <?php endforeach; ?>

        <?php endif; ?>

        <div class="mt-10 p-6 rounded-3xl border border-dashed border-white/10 text-center">
            <p class="text-xs text-slate-500 mb-3">Si vous avez des questions, n'hésitez pas à nous contacter.</p>
            <a href="support.php" class="inline-block px-6 py-2 rounded-full bg-blue-600/20 text-blue-400 text-[10px] font-bold uppercase tracking-widest border border-blue-500/30">
                Contacter le support
            </a>
        </div>

    </div>

</body>
</html>
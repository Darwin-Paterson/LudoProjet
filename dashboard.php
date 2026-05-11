<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Notification Count
    $stmt_n = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'unread'");
    $stmt_n->execute([$user_id]);
    $notif_count = $stmt_n->fetchColumn();

    // Recent Matches
    $stmt_m = $pdo->prepare("SELECT * FROM matches WHERE user_id = ? ORDER BY id DESC LIMIT 3");
    $stmt_m->execute([$user_id]);
    $my_matches = $stmt_m->fetchAll();

} catch (PDOException $e) { error_log($e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<?php include __DIR__.'/config/pwa.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ludo Pro - Tableau de bord premium</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Swiper CSS (For Slider) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

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
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }
        .nav-btn { color: #64748b; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .nav-btn.active { color: #3b82f6; text-shadow: 0 0 10px rgba(59, 130, 246, 0.5); }
        
        /* Swiper Custom Styles */
        .swiper { width: 100%; padding-bottom: 20px; }
        .swiper-slide { 
            background: transparent; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            border-radius: 1rem;
            overflow: hidden;
        }
        .swiper-pagination-bullet { background: #475569; opacity: 1; }
        .swiper-pagination-bullet-active { background: #3b82f6; width: 20px; border-radius: 5px; transition: width 0.3s; }
    </style>
</head>
<body class="pb-24 antialiased">

    <!-- Header -->
    <header class="p-5 flex justify-between items-center bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40 border-b border-white/5">
        <div class="flex items-center gap-3">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-2 rounded-xl shadow-lg shadow-blue-500/20">
                <i class="fas fa-dice text-xl text-white"></i>
            </div>
            <h1 class="text-2xl font-black tracking-tighter text-white">LUDO<span class="text-blue-500">PRO</span></h1>
        </div>
        
        <div class="flex items-center gap-5">
            <div class="relative cursor-pointer hover:text-white transition" onclick="location.href='notifications.php'">
                <i class="fas fa-bell text-xl text-slate-400"></i>
                <?php if($notif_count > 0): ?>
                    <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-red-500 text-[9px] flex items-center justify-center rounded-full text-white font-bold ring-2 ring-[#020617]"><?= $notif_count ?></span>
                <?php endif; ?>
            </div>
            <div class="relative" onclick="location.href='profile.php'">
                <img src="assets/avatars/<?= !empty($user['avatar']) ? $user['avatar'] : 'default.png' ?>" class="w-9 h-9 rounded-full object-cover border-2 border-blue-500/30 cursor-pointer">
                <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-[#020617]"></div>
            </div>
        </div>
    </header>

    <div class="max-w-md mx-auto px-5 mt-5 space-y-6">

        <!-- 1. Banner Slider -->
        <div class="swiper mySwiper w-full rounded-2xl overflow-hidden shadow-2xl shadow-black/20">
            <div class="swiper-wrapper">
                <!-- Slide 1 -->
                <div class="swiper-slide">
                    <div class="w-full h-40 bg-gradient-to-r from-violet-600 to-indigo-600 relative p-5 flex items-center justify-between">
                        <div class="z-10 w-2/3">
                            <span class="bg-white/20 text-white text-[9px] font-bold px-2 py-0.5 rounded uppercase mb-2 inline-block">Nouveau</span>
                            <h3 class="text-xl font-black text-white leading-tight mb-1">Méga tournoi</h3>
                            <p class="text-[10px] text-indigo-100 mb-3">Participez et gagnez un prize pool de FCFA 50,000 !</p>
                            <button class="bg-white text-indigo-600 text-[10px] font-bold px-4 py-1.5 rounded-full shadow-lg">Participer</button>
                        </div>
                        <i class="fas fa-trophy text-6xl text-white/20 absolute -right-2 -bottom-4 rotate-12"></i>
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-30"></div>
                    </div>
                </div>
                <!-- Slide 2 -->
                <div class="swiper-slide">
                    <div class="w-full h-40 bg-gradient-to-r from-pink-600 to-rose-500 relative p-5 flex items-center justify-between">
                        <div class="z-10 w-2/3">
                            <span class="bg-white/20 text-white text-[9px] font-bold px-2 py-0.5 rounded uppercase mb-2 inline-block">Offre</span>
                            <h3 class="text-xl font-black text-white leading-tight mb-1">Bonus de 50%</h3>
                            <p class="text-[10px] text-rose-100 mb-3">Sur le premier dépôt supérieur à FCFA 500</p>
                            <button class="bg-white text-rose-600 text-[10px] font-bold px-4 py-1.5 rounded-full shadow-lg">Dépôt</button>
                        </div>
                        <i class="fas fa-gift text-6xl text-white/20 absolute -right-2 -bottom-4 rotate-12"></i>
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-30"></div>
                    </div>
                </div>
                <!-- Slide 3 -->
                <div class="swiper-slide">
                    <div class="w-full h-40 bg-gradient-to-r from-emerald-500 to-teal-600 relative p-5 flex items-center justify-between">
                        <div class="z-10 w-2/3">
                            <span class="bg-white/20 text-white text-[9px] font-bold px-2 py-0.5 rounded uppercase mb-2 inline-block">Mise à jour</span>
                            <h3 class="text-xl font-black text-white leading-tight mb-1">Parrainez et gagnez</h3>
                            <p class="text-[10px] text-emerald-100 mb-3">Recevez FCFA 50 par ami invité !</p>
                            <button class="bg-white text-emerald-600 text-[10px] font-bold px-4 py-1.5 rounded-full shadow-lg">Inviter</button>
                        </div>
                        <i class="fas fa-users text-6xl text-white/20 absolute -right-2 -bottom-4 rotate-12"></i>
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20"></div>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>

        <!-- 2. Balance Card -->
        <div class="glass-card p-6 rounded-[1.5rem] relative overflow-hidden group border border-white/10">
            <div class="absolute -right-12 -top-12 w-40 h-40 bg-blue-600/20 rounded-full blur-[50px]"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Solde total</p>
                    <h2 class="text-4xl font-black text-white tracking-tight">FCFA <?= number_format($user['balance'] + $user['win_balance'], 2) ?></h2>
                </div>
                <div class="bg-gradient-to-br from-blue-500/20 to-purple-500/20 p-3 rounded-xl border border-white/5">
                    <i class="fas fa-wallet text-blue-400 text-lg"></i>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mt-6 relative z-10">
                <button onclick="location.href='deposit.php'" class="bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-xl font-bold text-xs uppercase tracking-wide transition-all active:scale-95 shadow-lg shadow-blue-900/20 flex items-center justify-center gap-2">
                    <i class="fas fa-plus-circle"></i> Ajouter des fonds
                </button>
                <button onclick="location.href='withdraw.php'" class="bg-slate-800 hover:bg-slate-700 text-slate-200 py-3 rounded-xl font-bold text-xs uppercase tracking-wide transition-all active:scale-95 border border-white/5 flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-down"></i> Retirer
                </button>
            </div>
        </div>

        <!-- 3. Game Modes Grid -->
        <section>
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Jouer et gagner</h3>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <!-- Refer & Earn (Replaced Quick Match) -->
                <div onclick="location.href='refer.php'" class="glass-card p-4 rounded-2xl border-l-4 border-teal-500 hover:bg-slate-800/40 transition-all active:scale-95 cursor-pointer relative overflow-hidden">
                    <div class="bg-teal-500/10 w-10 h-10 rounded-lg flex items-center justify-center text-teal-400 text-lg mb-3">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h4 class="text-sm font-bold text-white">Parrainer et gagner</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Obtenez FCFA 50 de bonus</p>
                    <i class="fas fa-chevron-right text-slate-600 absolute right-3 top-1/2 -translate-y-1/2 text-xs"></i>
                </div>

                <!-- Tournament -->
                <div onclick="location.href='tournaments.php'" class="glass-card p-4 rounded-2xl border-l-4 border-purple-500 hover:bg-slate-800/40 transition-all active:scale-95 cursor-pointer relative overflow-hidden">
                    <div class="bg-purple-500/10 w-10 h-10 rounded-lg flex items-center justify-center text-purple-400 text-lg mb-3">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h4 class="text-sm font-bold text-white">Tournoi</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Gros lots</p>
                    <i class="fas fa-chevron-right text-slate-600 absolute right-3 top-1/2 -translate-y-1/2 text-xs"></i>
                </div>

                <!-- Friends -->
                <div onclick="location.href='playfriend.php'" class="glass-card p-4 rounded-2xl border-l-4 border-emerald-500 hover:bg-slate-800/40 transition-all active:scale-95 cursor-pointer relative overflow-hidden">
                    <div class="bg-emerald-500/10 w-10 h-10 rounded-lg flex items-center justify-center text-emerald-400 text-lg mb-3">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h4 class="text-sm font-bold text-white">Jouer entre amis</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Salon privé</p>
                    <i class="fas fa-chevron-right text-slate-600 absolute right-3 top-1/2 -translate-y-1/2 text-xs"></i>
                </div>

                <!-- Computer -->
                <div onclick="location.href='game.php?mode=bot'" class="glass-card p-4 rounded-2xl border-l-4 border-orange-500 hover:bg-slate-800/40 transition-all active:scale-95 cursor-pointer relative overflow-hidden">
                    <div class="bg-orange-500/10 w-10 h-10 rounded-lg flex items-center justify-center text-orange-400 text-lg mb-3">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h4 class="text-sm font-bold text-white">Ordinateur</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Mode entraînement</p>
                    <i class="fas fa-chevron-right text-slate-600 absolute right-3 top-1/2 -translate-y-1/2 text-xs"></i>
                </div>
            </div>
        </section>

        <!-- 4. Match History -->
        <section class="pb-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Activité récente</h3>
                <a href="history.php" class="text-[10px] font-bold text-blue-500 hover:text-blue-400 transition">VOIR TOUT</a>
            </div>

            <div class="space-y-2.5">
                <?php if(empty($my_matches)): ?>
                    <div class="glass-card rounded-xl p-8 text-center border-dashed border-2 border-slate-700">
                        <i class="fas fa-history text-3xl text-slate-600 mb-2"></i>
                        <p class="text-xs text-slate-400">Aucun historique de partie pour le moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($my_matches as $m): ?>
                    <div class="glass-card p-3.5 rounded-xl flex justify-between items-center group hover:bg-slate-800/30 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-slate-800 rounded-lg flex items-center justify-center text-base shadow-inner">
                                <?= $m['result'] == 'Win' ? '🏆' : '🎲' ?>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-200"><?= ucfirst($m['mode']) ?> Match</h5>
                                <p class="text-[9px] text-slate-500 font-medium"><?= date('d M, h:i A', strtotime($m['created_at'])) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold <?= $m['result'] == 'Win' ? 'text-green-400' : 'text-red-400' ?>">
                                <?= $m['result'] == 'Win' ? '+' : '-' ?>FCFA <?= number_format($m['amount']) ?>
                            </p>
                            <p class="text-[9px] font-bold <?= $m['result'] == 'Win' ? 'text-green-500/50' : 'text-red-500/50' ?> uppercase tracking-wide"><?= $m['result'] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-[#0f172a]/90 backdrop-blur-xl border-t border-white/5 px-6 py-2 flex justify-between items-center z-50 shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
        <a href="dashboard.php" class="nav-btn active flex flex-col items-center gap-1 p-2">
            <i class="fas fa-home text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Accueil</span>
        </a>
        <a href="tournaments.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-trophy text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Événements</span>
        </a>
        
        <!-- Floating Action Button (Now points to Tournaments) -->
        <div class="relative -top-6 group">
            <div class="absolute inset-0 bg-blue-500 blur-lg opacity-40 group-hover:opacity-60 transition"></div>
            <div onclick="location.href='tournaments.php'" class="relative bg-gradient-to-br from-blue-500 to-blue-600 w-14 h-14 rounded-2xl flex items-center justify-center shadow-2xl border-4 border-[#020617] cursor-pointer transform group-active:scale-95 transition-all duration-200">
                <i class="fas fa-gamepad text-white text-xl"></i>
            </div>
        </div>

        <a href="wallet.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-wallet text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Portefeuille</span>
        </a>
        <a href="profile.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-user text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Profil</span>
        </a>
    </nav>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            spaceBetween: 10,
            centeredSlides: true,
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            }
        });
    </script>
</body>
</html>
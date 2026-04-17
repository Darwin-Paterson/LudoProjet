<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ludo Pro - Jouez et gagnez de l'argent réel</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { 
            background: #020617; 
            color: #f1f5f9; 
            font-family: 'Rajdhani', sans-serif;
            background-image: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 70%);
            overflow-x: hidden;
        }
        
        /* Glassmorphism Classes */
        .glass-nav {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .glass-card { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255,255,255,0.05); 
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            background: rgba(30, 41, 59, 0.6); 
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-5px);
            box-shadow: 0 10px 40px -10px rgba(59, 130, 246, 0.2);
        }

        /* Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .floating-element { animation: float 6s ease-in-out infinite; }
        
        .text-gradient {
            background: linear-gradient(to right, #60a5fa, #a855f7, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="antialiased selection:bg-blue-500 selection:text-white">

    <!-- Background Glow Effects -->
    <div class="fixed top-20 left-0 w-80 h-80 bg-blue-600/20 rounded-full blur-[120px] -z-10"></div>
    <div class="fixed bottom-0 right-0 w-96 h-96 bg-purple-600/20 rounded-full blur-[120px] -z-10"></div>

    <!-- Navigation -->
    <nav class="glass-nav fixed w-full z-50 top-0">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-2 rounded-lg shadow-lg shadow-blue-500/20">
                    <i class="fas fa-dice text-xl text-white"></i>
                </div>
                <h1 class="text-2xl font-black tracking-tighter text-white">LUDO<span class="text-blue-500">PRO</span></h1>
            </div>
            
            <div class="hidden md:flex gap-8 text-sm font-bold tracking-wider text-slate-300">
                <a href="#home" class="hover:text-white transition">ACCUEIL</a>
                <a href="#features" class="hover:text-white transition">FONCTIONNALITÉS</a>
                <a href="#how-it-works" class="hover:text-white transition">COMMENT JOUER</a>
            </div>

            <div class="flex gap-3">
                <a href="login.php" class="px-5 py-2 rounded-full border border-white/10 text-white text-xs font-bold hover:bg-white/5 transition">CONNEXION</a>
                <a href="register.php" class="px-5 py-2 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-bold shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition transform active:scale-95">INSCRIPTION</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="relative pt-32 pb-20 px-6 max-w-7xl mx-auto min-h-screen flex flex-col md:flex-row items-center justify-between gap-10">
        
        <!-- Text Content -->
        <div class="md:w-1/2 space-y-6 z-10 text-center md:text-left">
            <div class="inline-block px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-bold tracking-widest uppercase mb-2">
                ✨ La plateforme de jeu n°1
            </div>
            <h1 class="text-5xl md:text-7xl font-black leading-tight text-white">
                JOUEZ À LUDO <br />
                <span class="text-gradient">GAGNEZ DE L'ARGENT RÉEL</span>
            </h1>
            <p class="text-slate-400 text-sm md:text-base max-w-lg mx-auto md:mx-0 leading-relaxed">
                Rejoignez plus d'un million de joueurs sur la plateforme la plus fiable. Retraits instantanés, jeu équitable et tournois quotidiens vous attendent.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start pt-4">
                <a href="register.php" class="px-8 py-4 rounded-xl bg-white text-slate-900 font-bold text-sm shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:shadow-[0_0_30px_rgba(255,255,255,0.5)] transition flex items-center justify-center gap-2">
                    <i class="fas fa-gamepad"></i> JOUER MAINTENANT
                </a>
                <a href="#" class="px-8 py-4 rounded-xl glass-card text-white font-bold text-sm hover:bg-white/10 transition flex items-center justify-center gap-2">
                    <i class="fab fa-android text-green-400 text-lg"></i> TÉLÉCHARGER L'APP
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 pt-8 border-t border-white/5 mt-8">
                <div>
                    <h3 class="text-2xl font-bold text-white">1M+</h3>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest">Joueurs</p>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white">৳5Cr+</h3>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest">Gains</p>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white">24/7</h3>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest">Support</p>
                </div>
            </div>
        </div>

        <!-- Hero Visual (Image Inside Phone Frame) -->
        <div class="md:w-1/2 flex justify-center relative">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 blur-[80px] opacity-20 animate-pulse"></div>
            
            <!-- Phone Container -->
            <div class="relative w-72 h-[520px] bg-slate-900 rounded-[2.5rem] border-[8px] border-slate-800 shadow-2xl floating-element overflow-hidden">
                
                <!-- THE IMAGE GOES HERE -->
                <div class="w-full h-full relative bg-slate-800">
                    <!-- Placeholder if image missing (remove 'onerror' if not needed) -->
                    <img src="assets/phone.png" 
                         onerror="this.src='https://placehold.co/300x600/1e293b/FFF?text=App+Screenshot'"
                         alt="Ludo App Interface" 
                         class="w-full h-full object-cover">
                         
                    <!-- Overlay Gradient (Optional: Makes text readable if image is bright) -->
                    <div class="absolute inset-0 bg-gradient-to-t from-[#020617] via-transparent to-transparent opacity-40"></div>
                </div>

                <!-- Phone Notch -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-6 bg-slate-800 rounded-b-2xl z-20"></div>
            </div>

            <!-- Floating Notification 1 -->
            <div class="absolute top-24 right-8 glass-card p-3 rounded-xl flex items-center gap-3 animate-bounce shadow-lg shadow-black/50 border-l-4 border-green-500" style="animation-duration: 3s;">
                <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center text-green-400"><i class="fas fa-check"></i></div>
                <div>
                    <p class="text-[10px] text-slate-400 font-bold">Paiement reçu</p>
                    <p class="text-xs font-black text-white">+ ৳500.00</p>
                </div>
            </div>
            
            <!-- Floating Notification 2 -->
            <div class="absolute bottom-32 -left-6 glass-card p-3 rounded-xl flex items-center gap-3 animate-bounce shadow-lg shadow-black/50 border-l-4 border-yellow-500" style="animation-duration: 4s;">
                <div class="w-8 h-8 bg-yellow-500/20 rounded-full flex items-center justify-center text-yellow-400"><i class="fas fa-trophy"></i></div>
                <div>
                    <p class="text-[10px] text-slate-400 font-bold">Raju a gagné</p>
                    <p class="text-xs font-black text-yellow-400">৳2,000.00</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-blue-500 font-bold tracking-widest text-xs uppercase">Pourquoi nous choisir</span>
                <h2 class="text-3xl md:text-4xl font-black text-white mt-2">FONCTIONNALITÉS PREMIUM</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500/20 rounded-full blur-2xl group-hover:bg-blue-500/40 transition"></div>
                    <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center text-blue-400 text-2xl mb-6 shadow-inner">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Retrait instantané</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Recevez vos gains directement sur votre compte Bkash, Nagad ou bancaire en quelques minutes.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-purple-500/20 rounded-full blur-2xl group-hover:bg-purple-500/40 transition"></div>
                    <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center text-purple-400 text-2xl mb-6 shadow-inner">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">100% sûr et sécurisé</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Notre plateforme est certifiée RNG et utilise un chiffrement avancé pour garantir l'équité et la sécurité des données.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-pink-500/20 rounded-full blur-2xl group-hover:bg-pink-500/40 transition"></div>
                    <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center text-pink-400 text-2xl mb-6 shadow-inner">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Support 24/7</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Un problème ? Notre équipe support dédiée est disponible 24h/24 via WhatsApp.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-20 bg-slate-900/30">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-black text-white">COMMENT COMMENCER ?</h2>
                <p class="text-slate-400 mt-2">Commencez votre parcours gagnant en 3 étapes simples</p>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                <!-- Step 1 -->
                <div class="flex-1 text-center group">
                    <div class="w-20 h-20 mx-auto bg-slate-800 border border-slate-700 rounded-full flex items-center justify-center text-2xl font-bold text-white mb-6 relative z-10 group-hover:scale-110 transition duration-300">
                        1
                        <div class="absolute inset-0 bg-blue-500/20 rounded-full blur-lg -z-10 opacity-0 group-hover:opacity-100 transition"></div>
                    </div>
                    <h4 class="text-lg font-bold text-white mb-2">Créer un compte</h4>
                    <p class="text-slate-400 text-sm">Inscrivez-vous gratuitement avec votre numéro mobile.</p>
                </div>
                
                <!-- Connector Line (Desktop) -->
                <div class="hidden md:block w-24 h-1 bg-slate-800"></div>

                <!-- Step 2 -->
                <div class="flex-1 text-center group">
                    <div class="w-20 h-20 mx-auto bg-slate-800 border border-slate-700 rounded-full flex items-center justify-center text-2xl font-bold text-white mb-6 relative z-10 group-hover:scale-110 transition duration-300">
                        2
                        <div class="absolute inset-0 bg-purple-500/20 rounded-full blur-lg -z-10 opacity-0 group-hover:opacity-100 transition"></div>
                    </div>
                    <h4 class="text-lg font-bold text-white mb-2">Rejoindre une partie</h4>
                    <p class="text-slate-400 text-sm">Ajoutez de l'argent et rejoignez votre tournoi Ludo préféré.</p>
                </div>

                <!-- Connector Line (Desktop) -->
                <div class="hidden md:block w-24 h-1 bg-slate-800"></div>

                <!-- Step 3 -->
                <div class="flex-1 text-center group">
                    <div class="w-20 h-20 mx-auto bg-slate-800 border border-slate-700 rounded-full flex items-center justify-center text-2xl font-bold text-white mb-6 relative z-10 group-hover:scale-110 transition duration-300">
                        3
                        <div class="absolute inset-0 bg-green-500/20 rounded-full blur-lg -z-10 opacity-0 group-hover:opacity-100 transition"></div>
                    </div>
                    <h4 class="text-lg font-bold text-white mb-2">Gagner et retirer</h4>
                    <p class="text-slate-400 text-sm">Gagnez la partie et retirez votre argent instantanément.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-6">
        <div class="max-w-5xl mx-auto glass-card rounded-[3rem] p-10 md:p-16 text-center relative overflow-hidden border-t border-white/20">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20"></div>
            
            <div class="relative z-10">
                <h2 class="text-3xl md:text-5xl font-black text-white mb-6">PRÊT À JOUER ?</h2>
                <p class="text-slate-300 mb-8 text-lg">Ne ratez pas les méga tournois quotidiens. Rejoignez-nous et obtenez <span class="text-yellow-400 font-bold">৳50 de bonus</span> !</p>
                
                <a href="register.php" class="inline-block px-10 py-4 rounded-full bg-white text-blue-900 font-black text-lg shadow-[0_0_40px_rgba(255,255,255,0.4)] hover:scale-105 transition transform">
                    CRÉER UN COMPTE
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/5 bg-slate-900 pt-16 pb-8 px-6">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-10 mb-10">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <div class="bg-blue-600 p-1.5 rounded-lg">
                        <i class="fas fa-dice text-white"></i>
                    </div>
                    <h2 class="text-xl font-black text-white">LUDO<span class="text-blue-500">PRO</span></h2>
                </div>
                <p class="text-slate-500 text-sm leading-relaxed max-w-sm">
                    La destination idéale pour les fans de Ludo. Jouez avec de vrais joueurs, gagnez de l'argent réel et profitez de la meilleure expérience de jeu.
                </p>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-4">Liens rapides</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="#" class="hover:text-blue-400 transition">À propos</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition">Conditions générales</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition">Politique de confidentialité</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition">Politique de remboursement</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-4">Contact</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><i class="fas fa-envelope w-5"></i> support@ludopro.com</li>
                    <li><i class="fab fa-whatsapp w-5"></i>+880 1330-368547</li>
                    <li><i class="fas fa-map-marker-alt w-5"></i> Dhaka, Bangladesh</li>
                </ul>
                <div class="flex gap-4 mt-4">
                    <a href="https://t.me/appdeveloperbdandappseller" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-blue-600 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://t.me/appdeveloperbdandappseller" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-pink-600 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                    <a href="https://t.me/appdeveloperbdandappseller" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-blue-400 hover:text-white transition"><i class="fab fa-telegram"></i></a>
                </div>
            </div>
        </div>
        
        <div class="border-t border-slate-800 pt-8 text-center">
            <p class="text-slate-600 text-xs">&copy; <?= date('Y') ?> Ludo Pro. Tous droits réservés.</p>
        </div>
    </footer>

</body>
</html>
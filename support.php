<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

// Vérification d'authentification
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

$msg = "";
$msg_type = "";

// Traitement de la soumission d'un ticket de support
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ticket'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($subject) && !empty($message)) {
        try {
            // Supposé qu'il existe une table 'support_tickets'.
            // Sinon, il faut la créer ou journaliser les demandes. Ici, insertion standard.
            $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message, status, created_at) VALUES (?, ?, ?, 'open', NOW())");
            $stmt->execute([$user_id, $subject, $message]);
            
            $msg = "Ticket envoyé avec succès ! Nous vous répondrons bientôt.";
            $msg_type = "success";
        } catch (PDOException $e) {
            $msg = "Erreur lors de l'envoi du ticket. Veuillez réessayer.";
            $msg_type = "error";
        }
    } else {
        $msg = "Veuillez remplir tous les champs.";
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<?php include __DIR__.'/config/pwa.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ludo Pro - Support</title>
    
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
        
        /* FAQ Accordion Styles */
        .faq-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .faq-item.active .faq-content { max-height: 200px; }
        .faq-icon { transition: transform 0.3s; }
        .faq-item.active .faq-icon { transform: rotate(180deg); }
        
        /* Form Inputs */
        .input-glass {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s;
        }
        .input-glass:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>
<body class="pb-24 antialiased">

    <!-- Header -->
    <header class="p-5 flex items-center gap-4 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40 border-b border-white/5">
        <button onclick="history.back()" class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-700 transition">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h1 class="text-xl font-black tracking-wide text-white">Aide & <span class="text-blue-500">Support</span></h1>
    </header>

    <div class="max-w-md mx-auto px-5 mt-5 space-y-6">

        <!-- 1. Quick Contact Channels -->
        <section>
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Support instantané</h3>
            <div class="grid grid-cols-3 gap-3">
                <!-- WhatsApp -->
                <a href="https://wa.me/01709075605" target="_blank" class="glass-card p-4 rounded-2xl flex flex-col items-center justify-center gap-2 group hover:bg-slate-800/40 transition active:scale-95">
                    <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center group-hover:bg-green-500/30 transition">
                        <i class="fab fa-whatsapp text-xl text-green-400"></i>
                    </div>
                    <span class="text-[10px] font-bold text-slate-300">WhatsApp</span>
                </a>

                <!-- Telegram -->
                <a href="https://t.me/appdeveloperbdandappseller" target="_blank" class="glass-card p-4 rounded-2xl flex flex-col items-center justify-center gap-2 group hover:bg-slate-800/40 transition active:scale-95">
                    <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center group-hover:bg-blue-500/30 transition">
                        <i class="fab fa-telegram-plane text-xl text-blue-400"></i>
                    </div>
                    <span class="text-[10px] font-bold text-slate-300">Telegram</span>
                </a>

                <!-- Email -->
                <a href="mailto:support@ludopro.com" class="glass-card p-4 rounded-2xl flex flex-col items-center justify-center gap-2 group hover:bg-slate-800/40 transition active:scale-95">
                    <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center group-hover:bg-red-500/30 transition">
                        <i class="fas fa-envelope text-xl text-red-400"></i>
                    </div>
                    <span class="text-[10px] font-bold text-slate-300">Email</span>
                </a>
            </div>
        </section>

        <!-- 2. Ticket Form -->
        <section class="glass-card p-6 rounded-2xl relative overflow-hidden border border-white/10">
            <div class="absolute -right-10 -top-10 w-32 h-32 bg-purple-600/20 rounded-full blur-[40px]"></div>
            
            <h3 class="text-lg font-bold text-white mb-1 relative z-10">Ouvrir un ticket</h3>
            <p class="text-[10px] text-slate-400 mb-4 relative z-10">Vous avez un problème ? Décrivez-le ci-dessous.</p>

            <?php if($msg): ?>
                <div class="mb-4 p-3 rounded-xl text-xs font-bold text-center <?= $msg_type == 'success' ? 'bg-green-500/20 text-green-400 border border-green-500/20' : 'bg-red-500/20 text-red-400 border border-red-500/20' ?>">
                    <?= $msg ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4 relative z-10">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Sujet</label>
                    <input type="text" name="subject" required placeholder="ex : problème de dépôt" class="w-full mt-1 px-4 py-3 rounded-xl text-sm input-glass placeholder-slate-600">
                </div>
                
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Message</label>
                    <textarea name="message" rows="4" required placeholder="Donnez-nous plus de détails..." class="w-full mt-1 px-4 py-3 rounded-xl text-sm input-glass placeholder-slate-600 resize-none"></textarea>
                </div>

                <button type="submit" name="submit_ticket" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg shadow-blue-900/40 active:scale-[0.98] transition transform">
                    Envoyer le ticket <i class="fas fa-paper-plane ml-2"></i>
                </button>
            </form>
        </section>

        <!-- 3. FAQ Section -->
        <section>
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Questions fréquentes</h3>
            <div class="space-y-2">
                
                <!-- FAQ Item 1 -->
                <div class="faq-item glass-card rounded-xl overflow-hidden cursor-pointer" onclick="toggleFaq(this)">
                    <div class="p-4 flex justify-between items-center">
                        <h4 class="text-xs font-bold text-slate-200">Comment déposer de l'argent ?</h4>
                        <i class="fas fa-chevron-down text-xs text-slate-500 faq-icon"></i>
                    </div>
                    <div class="faq-content bg-slate-900/30">
                        <p class="p-4 pt-0 text-[11px] text-slate-400 leading-relaxed">
                            Allez dans la section Portefeuille, cliquez sur "Ajouter des fonds", choisissez votre méthode de paiement (Bkash/Nagad), saisissez le montant puis envoyez l'ID de transaction.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="faq-item glass-card rounded-xl overflow-hidden cursor-pointer" onclick="toggleFaq(this)">
                    <div class="p-4 flex justify-between items-center">
                        <h4 class="text-xs font-bold text-slate-200">Quel est le retrait minimum ?</h4>
                        <i class="fas fa-chevron-down text-xs text-slate-500 faq-icon"></i>
                    </div>
                    <div class="faq-content bg-slate-900/30">
                        <p class="p-4 pt-0 text-[11px] text-slate-400 leading-relaxed">
                            Le montant minimum de retrait est de FCFA 100. Les retraits sont traités sous 10 à 30 minutes pendant les heures de service.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="faq-item glass-card rounded-xl overflow-hidden cursor-pointer" onclick="toggleFaq(this)">
                    <div class="p-4 flex justify-between items-center">
                        <h4 class="text-xs font-bold text-slate-200">Le jeu a planté, remboursement ?</h4>
                        <i class="fas fa-chevron-down text-xs text-slate-500 faq-icon"></i>
                    </div>
                    <div class="faq-content bg-slate-900/30">
                        <p class="p-4 pt-0 text-[11px] text-slate-400 leading-relaxed">
                            Prenez une capture d'écran de l'erreur et envoyez un ticket avec l'ID du match. Nous vérifierons et rembourserons si cela vient du serveur.
                        </p>
                    </div>
                </div>

            </div>
        </section>

    </div>

    <!-- Bottom Navigation (Same as Dashboard) -->
    <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-[#0f172a]/90 backdrop-blur-xl border-t border-white/5 px-6 py-2 flex justify-between items-center z-50 shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
        <a href="dashboard.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-home text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Accueil</span>
        </a>
        <a href="tournaments.php" class="nav-btn flex flex-col items-center gap-1 p-2">
            <i class="fas fa-trophy text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Événements</span>
        </a>
        
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
        <a href="profile.php" class="nav-btn active flex flex-col items-center gap-1 p-2">
            <i class="fas fa-user text-lg mb-0.5"></i>
            <span class="text-[9px] font-bold tracking-wide">Profil</span>
        </a>
    </nav>

    <!-- JavaScript for FAQ -->
    <script>
        function toggleFaq(element) {
            // Fermer toutes les autres questions
            const allFaqs = document.querySelectorAll('.faq-item');
            allFaqs.forEach(item => {
                if(item !== element) {
                    item.classList.remove('active');
                }
            });
            // Basculer l'état de la question sélectionnée
            element.classList.toggle('active');
        }
    </script>
</body>
</html>
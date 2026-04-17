<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

// 1. Vérification de la connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. Récupération des paramètres administrateur (numéros de paiement)
try {
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Settings Error: " . $e->getMessage());
}

$msg = "";
$msg_type = "";

// 3. Logique de soumission de dépôt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_deposit'])) {
    $method = $_POST['method']; // bKash, Nagad, Rocket
    $amount = $_POST['amount'];
    $sender_number = $_POST['sender_number']; // Nouveau champ : numéro depuis lequel l'argent est envoyé
    $trx_id = $_POST['trx_id'];

    if ($amount < 10) {
        $msg = "Le montant minimum du dépôt est de ৳10";
        $msg_type = "red";
    } elseif (empty($trx_id) || empty($sender_number)) {
        $msg = "Tous les champs sont obligatoires !";
        $msg_type = "red";
    } else {
        // Vérification d'un éventuel doublon de TrxID
        $check = $pdo->prepare("SELECT id FROM deposits WHERE transaction_id = ?");
        $check->execute([$trx_id]);
        
        if ($check->rowCount() > 0) {
            $msg = "ID de transaction déjà utilisé !";
            $msg_type = "red";
        } else {
            // Insertion en base de données
            $stmt = $pdo->prepare("INSERT INTO deposits (user_id, method, amount, sender_number, transaction_id, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
            if ($stmt->execute([$user_id, $method, $amount, $sender_number, $trx_id])) {
                $msg = "Dépôt envoyé avec succès ! Veuillez attendre la validation.";
                $msg_type = "green";
            } else {
                $msg = "Une erreur est survenue ! Veuillez réessayer.";
                $msg_type = "red";
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
    <title>Dépôt d'argent - Ludo Pro</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { 
            background: #020617; 
            color: #f1f5f9; 
            font-family: 'Rajdhani', sans-serif;
            background-image: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 70%);
            -webkit-tap-highlight-color: transparent;
            min-height: 100vh;
        }
        .glass-card { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255,255,255,0.05); 
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        /* Custom Radio Button Style */
        .method-radio:checked + div {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
        }
        input { 
            background: rgba(15, 23, 42, 0.6) !important; 
            border: 1px solid rgba(255,255,255,0.1) !important; 
            color: white !important; 
            transition: all 0.3s;
        }
        input:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.2);
            outline: none;
        }
    </style>
</head>
<body class="pb-10 antialiased">

    <!-- Header -->
    <header class="p-5 flex justify-between items-center sticky top-0 z-40 bg-[#020617]/80 backdrop-blur-md border-b border-white/5">
        <a href="wallet.php" class="w-10 h-10 rounded-xl glass-card flex items-center justify-center text-slate-300 hover:text-white transition active:scale-95">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h2 class="text-xl font-bold tracking-wide text-white">Ajouter des fonds</h2>
        <div class="w-10"></div>
    </header>

    <div class="max-w-md mx-auto px-5 mt-4 space-y-6">

        <!-- Alert Message -->
        <?php if($msg): ?>
            <script>
                Swal.fire({
                    icon: '<?= $msg_type == "green" ? "success" : "error" ?>',
                    title: '<?= $msg_type == "green" ? "Succès" : "Erreur" ?>',
                    text: '<?= $msg ?>',
                    background: '#1e293b',
                    color: '#fff',
                    confirmButtonColor: '#3b82f6'
                });
            </script>
        <?php endif; ?>

        <!-- Instructions -->
        <div class="glass-card rounded-2xl p-4 border-l-4 border-yellow-500 bg-yellow-500/5">
            <h3 class="text-xs font-bold text-yellow-500 uppercase mb-2 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Instructions
            </h3>
            <ul class="text-[11px] text-slate-400 space-y-1.5 list-disc ml-4 font-medium">
                <li>Sélectionnez la méthode de paiement (bKash/Nagad/Rocket).</li>
                <li>Envoyez l'argent avec l'option <span class="text-white font-bold">"Send Money"</span>.</li>
                <li>Copiez l'ID de transaction puis collez-le ci-dessous.</li>
                <li>Le montant minimum du dépôt est de <span class="text-white font-bold">৳10</span>.</li>
            </ul>
        </div>

        <!-- Deposit Form -->
        <form method="POST" class="space-y-6">
            
            <!-- Payment Methods -->
            <div>
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1 mb-2 block">Choisir une méthode</label>
                <div class="grid grid-cols-3 gap-3">
                    <!-- bKash -->
                    <label class="cursor-pointer group">
                        <input type="radio" name="method" value="bKash" class="hidden method-radio" checked onchange="updateNumber('<?= $settings['bkash_number'] ?>', 'bKash')">
                        <div class="glass-card p-3 rounded-2xl flex flex-col items-center gap-2 transition hover:bg-slate-800/50 h-full justify-center">
                            <img src="https://freelogopng.com/images/all_img/1656234745bkash-app-logo-png.png" class="h-8 w-auto object-contain">
                            <span class="text-[10px] font-bold uppercase text-slate-400 group-hover:text-white">bKash</span>
                        </div>
                    </label>

                    <!-- Nagad -->
                    <label class="cursor-pointer group">
                        <input type="radio" name="method" value="Nagad" class="hidden method-radio" onchange="updateNumber('<?= $settings['nagad_number'] ?>', 'Nagad')">
                        <div class="glass-card p-3 rounded-2xl flex flex-col items-center gap-2 transition hover:bg-slate-800/50 h-full justify-center">
                            <img src="https://freelogopng.com/images/all_img/1679248787Nagad-Logo-PNG.png" class="h-6 w-auto object-contain mt-1">
                            <span class="text-[10px] font-bold uppercase text-slate-400 group-hover:text-white mt-1">Nagad</span>
                        </div>
                    </label>

                    <!-- Rocket -->
                    <label class="cursor-pointer group">
                        <input type="radio" name="method" value="Rocket" class="hidden method-radio" onchange="updateNumber('<?= $settings['rocket_number'] ?>', 'Rocket')">
                        <div class="glass-card p-3 rounded-2xl flex flex-col items-center gap-2 transition hover:bg-slate-800/50 h-full justify-center">
                            <img src="https://logowik.com/content/uploads/images/rocket4947.logowik.com.webp" class="h-6 w-auto object-contain mt-1">
                            <span class="text-[10px] font-bold uppercase text-slate-400 group-hover:text-white mt-1">Rocket</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Admin Number Display -->
            <div class="glass-card rounded-2xl p-5 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-purple-600/10 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">Envoyer à (<span id="methodName" class="text-blue-400">bKash</span>)</p>
                        <p class="text-xl font-black text-white tracking-widest font-mono" id="adminNumber"><?= $settings['bkash_number'] ?></p>
                    </div>
                    <button type="button" onclick="copyNumber()" class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 hover:bg-blue-500 hover:text-white transition active:scale-90">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>

            <!-- Input Fields -->
            <div class="space-y-4">
                <!-- Amount -->
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1 ml-1">Montant</label>
                    <div class="relative mt-1 group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold group-focus-within:text-blue-500 transition">৳</span>
                        <input type="number" name="amount" class="w-full py-3.5 pl-10 pr-4 rounded-xl text-sm font-bold placeholder-slate-600" placeholder="Saisir le montant (Min 10)" required min="10">
                    </div>
                </div>

                <!-- Sender Number -->
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1 ml-1">Numéro expéditeur</label>
                    <div class="relative mt-1 group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold group-focus-within:text-blue-500 transition"><i class="fas fa-phone-alt text-xs"></i></span>
                        <input type="text" name="sender_number" class="w-full py-3.5 pl-10 pr-4 rounded-xl text-sm font-bold placeholder-slate-600" placeholder="017xxxxxxxx" required>
                    </div>
                </div>

                <!-- TrxID -->
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1 ml-1">ID de transaction</label>
                    <div class="relative mt-1 group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold group-focus-within:text-blue-500 transition"><i class="fas fa-receipt text-xs"></i></span>
                        <input type="text" name="trx_id" class="w-full py-3.5 pl-10 pr-4 rounded-xl text-sm font-bold placeholder-slate-600" placeholder="Collez le TrxID (ex: 9HGF76...)" required>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" name="submit_deposit" class="w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 py-4 rounded-xl font-black uppercase tracking-widest text-sm text-white shadow-lg shadow-blue-600/30 active:scale-95 transition transform">
                Vérifier et ajouter les fonds <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>

        <div class="text-center pb-6">
            <a href="support.php" class="text-[11px] text-slate-500 hover:text-white transition font-medium border-b border-transparent hover:border-slate-500 pb-0.5">Besoin d'aide pour le dépôt ? Contactez le support</a>
        </div>
    </div>

    <!-- JavaScript for Dynamic Number Change -->
    <script>
        function updateNumber(number, method) {
            // Mise à jour du numéro affiché
            const numElement = document.getElementById('adminNumber');
            const nameElement = document.getElementById('methodName');
            
            // Effet d'animation sur le changement
            numElement.style.opacity = '0';
            setTimeout(() => {
                numElement.innerText = number;
                nameElement.innerText = method;
                numElement.style.opacity = '1';
            }, 200);
        }

        function copyNumber() {
            const num = document.getElementById('adminNumber').innerText;
            navigator.clipboard.writeText(num).then(() => {
                const btn = document.querySelector('button[onclick="copyNumber()"]');
                const originalHTML = btn.innerHTML;
                
                // Changer l'icône du bouton pour afficher une coche
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.classList.add('bg-green-500/20', 'text-green-500', 'border-green-500/20');
                btn.classList.remove('bg-blue-500/10', 'text-blue-500', 'border-blue-500/20');

                // Notification toast (SweetAlert2)
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    background: '#1e293b',
                    color: '#fff'
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Numéro copié !'
                });

                // Revenir à l'état initial après 2 secondes
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('bg-green-500/20', 'text-green-500', 'border-green-500/20');
                    btn.classList.add('bg-blue-500/10', 'text-blue-500', 'border-blue-500/20');
                }, 2000);
            });
        }
    </script>

</body>
</html>
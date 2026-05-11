<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

$msg = "";
$msg_type = "";

// Récupération du solde actuel de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_balance = $user['balance'] ?? 0;
} catch (PDOException $e) { $current_balance = 0; }

// Logique de soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_withdraw'])) {
    $method = $_POST['method'];
    $amount = $_POST['amount'];
    $account_number = $_POST['account_number'];

    if ($amount < 50) {
        $msg = "Le montant minimum du retrait est de FCFA 50";
        $msg_type = "red";
    } elseif ($amount > $current_balance) {
        $msg = "Solde insuffisant !";
        $msg_type = "red";
    } else {
        // 1. Débiter le solde (bloquer le montant pendant que la demande est en attente)
        $new_balance = $current_balance - $amount;
        $update_stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $update_stmt->execute([$new_balance, $user_id]);

        // 2. Insérer la demande dans la table des retraits
        $stmt = $pdo->prepare("INSERT INTO withdraws (user_id, method, amount, account_number) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $method, $amount, $account_number])) {
            $msg = "Demande de retrait envoyée avec succès !";
            $msg_type = "green";
            $current_balance = $new_balance; // Pour afficher le solde mis à jour
        } else {
            $msg = "Une erreur est survenue !";
            $msg_type = "red";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<?php include __DIR__.'/config/pwa.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Retrait - Ludo Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #020617; color: #f1f5f9; font-family: 'Rajdhani', sans-serif; background-image: radial-gradient(circle at 50% 0%, #1e1b4b 0%, #020617 70%); }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        input { background: rgba(0,0,0,0.3) !important; border: 1px solid rgba(255,255,255,0.1) !important; color: white !important; }
        input:focus { border-color: #ef4444 !important; outline: none; ring: 2px; ring-color: rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body class="pb-10 antialiased">

    <header class="p-5 flex justify-between items-center sticky top-0 z-40 bg-[#020617]/80 backdrop-blur-md">
        <button onclick="location.href='wallet.php'" class="w-10 h-10 rounded-xl glass-card flex items-center justify-center text-slate-300">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h2 class="text-xl font-bold tracking-widest uppercase">Retrait</h2>
        <div class="w-10"></div>
    </header>

    <div class="max-w-md mx-auto px-5 space-y-6">

        <div class="glass-card rounded-[2rem] p-6 text-center border-t-4 border-red-500">
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Solde retirable</p>
            <h1 class="text-4xl font-black text-white mt-1">FCFA <?= number_format($current_balance, 2) ?></h1>
        </div>

        <?php if($msg): ?>
            <div class="bg-<?= $msg_type ?>-500/20 border border-<?= $msg_type ?>-500 text-<?= $msg_type ?>-200 px-4 py-3 rounded-2xl text-center text-sm font-bold">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            
            <div class="space-y-3">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Recevoir via</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="method" value="bKash" class="hidden peer" required checked>
                        <div class="glass-card p-4 rounded-2xl flex flex-col items-center gap-2 border-2 border-transparent peer-checked:border-pink-500 peer-checked:bg-pink-500/10 transition">
                            <img src="https://freelogopng.com/images/all_img/1656234745bkash-app-logo-png.png" class="h-6">
                            <span class="text-[10px] font-bold uppercase">bKash</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="method" value="Nagad" class="hidden peer">
                        <div class="glass-card p-4 rounded-2xl flex flex-col items-center gap-2 border-2 border-transparent peer-checked:border-orange-500 peer-checked:bg-orange-500/10 transition">
                            <img src="https://freelogopng.com/images/all_img/1679248787Nagad-Logo-PNG.png" class="h-6">
                            <span class="text-[10px] font-bold uppercase">Nagad</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="method" value="Rocket" class="hidden peer">
                        <div class="glass-card p-4 rounded-2xl flex flex-col items-center gap-2 border-2 border-transparent peer-checked:border-purple-500 peer-checked:bg-purple-500/10 transition">
                            <img src="https://logowik.com/content/uploads/images/rocket4947.logowik.com.webp" class="h-6">
                            <span class="text-[10px] font-bold uppercase">Rocket</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Votre numéro de compte</label>
                    <input type="text" name="account_number" class="w-full p-4 mt-1 rounded-2xl" placeholder="017XXXXXXXX" required>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Montant du retrait</label>
                    <div class="relative mt-1">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold">FCFA </span>
                        <input type="number" name="amount" class="w-full p-4 pl-8 rounded-2xl" placeholder="Min 50" required min="50">
                    </div>
                </div>
            </div>

            <div class="glass-card p-4 rounded-2xl bg-red-500/5">
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-1"></i> 
                    Vérifiez bien votre numéro de compte. Le traitement du retrait peut prendre jusqu'à 24 heures.
                </p>
            </div>

            <button type="submit" name="submit_withdraw" class="w-full bg-red-600 hover:bg-red-700 py-4 rounded-2xl font-black uppercase tracking-widest text-sm shadow-xl shadow-red-600/20 active:scale-95 transition-all">
                Retirer l'argent
            </button>
        </form>

    </div>

</body>
</html>
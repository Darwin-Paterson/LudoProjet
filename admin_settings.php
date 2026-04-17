<?php
session_start();
require 'config/db.php';

// Vérification de la connexion administrateur
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin.php"); exit(); }

$msg = ""; $msgType = "";

// 1. Mise à jour des paramètres
if (isset($_POST['update_settings'])) {
    $sql = "UPDATE settings SET referral_bonus=?, signup_bonus=?, min_withdraw=?, bkash_number=?, nagad_number=?, rocket_number=? WHERE id=1";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$_POST['ref'], $_POST['sign'], $_POST['min_wd'], $_POST['bkash'], $_POST['nagad'], $_POST['rocket']])) {
        $msg = "Paramètres mis à jour !"; $msgType = "success";
    }
}

// 2. Ajout d'un slider
if (isset($_POST['add_slider'])) {
    if(!empty($_FILES['img']['name'])){
        $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
        $name = "slide_".time().".".$ext;
        // Créer le dossier s'il n'existe pas pour éviter les erreurs
        if(!is_dir('assets/sliders')) mkdir('assets/sliders', 0777, true);
        
        if(move_uploaded_file($_FILES['img']['tmp_name'], "assets/sliders/".$name)){
            $pdo->prepare("INSERT INTO sliders (image, title, subtitle) VALUES (?,?,?)")->execute([$name, $_POST['title'], $_POST['sub']]);
            $msg = "Slide ajoutée !"; $msgType = "success";
        }
    }
}

// 3. Suppression d'un slider
if (isset($_GET['del_slider'])) {
    $pdo->prepare("DELETE FROM sliders WHERE id=?")->execute([$_GET['del_slider']]);
    $msg = "Slide supprimée !"; $msgType = "success";
}

// 4. Changement de mot de passe admin
if(isset($_POST['change_pass'])){
    if($_POST['p1'] === $_POST['p2']){
        $hash = password_hash($_POST['p1'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE admin SET password=? WHERE id=?")->execute([$hash, $_SESSION['admin_id']]);
        $msg = "Mot de passe modifié !"; $msgType = "success";
    } else { $msg = "Les mots de passe ne correspondent pas !"; $msgType = "error"; }
}

// 5. Envoi d'une notification globale ou ciblée
if(isset($_POST['send_notif'])){
    $uid = ($_POST['uid'] == 'all') ? 0 : $_POST['uid'];
    $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?,?,?)")->execute([$uid, $_POST['title'], $_POST['msg']]);
    $msg = "Notification envoyée !"; $msgType = "success";
}

$set = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch();
$sliders = $pdo->query("SELECT * FROM sliders ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body{background:#0f172a;color:#e2e8f0;font-family:sans-serif;} input,textarea{background:#1e293b;border:1px solid #334155;color:white;width:100%;padding:10px;border-radius:8px;outline:none;}</style>
</head>
<body class="flex h-screen overflow-hidden">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#111827] border-r border-slate-800 hidden md:block">
        <div class="h-16 flex items-center px-6 border-b border-slate-800"><h2 class="text-xl font-bold text-white">Ludo<span class="text-blue-500">Pro</span></h2></div>
        <nav class="p-4 space-y-2">
            <a href="admin.php" class="block px-4 py-3 text-slate-400 hover:bg-slate-800 rounded-xl font-bold"><i class="fas fa-home w-6"></i> Tableau de bord</a>
            <a href="admin_settings.php" class="block px-4 py-3 bg-blue-600 text-white rounded-xl font-bold"><i class="fas fa-cogs w-6"></i> Paramètres généraux</a>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="h-16 bg-[#1e293b] border-b border-slate-800 flex items-center px-6"><h2 class="font-bold text-slate-300">Paramètres système</h2></header>
        
        <div class="flex-1 overflow-y-auto p-6 space-y-8">
            <?php if($msg): ?><script>Swal.fire({icon:'<?= $msgType ?>',title:'<?= $msg ?>',toast:true,position:'top-end',showConfirmButton:false,timer:2000});</script><?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- General Settings -->
                <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700">
                    <h3 class="font-bold text-white mb-4 border-b border-slate-700 pb-2">Paiements et bonus</h3>
                    <form method="POST" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="text-xs text-slate-400">Bonus parrainage</label><input type="number" name="ref" value="<?= $set['referral_bonus'] ?>"></div>
                            <div><label class="text-xs text-slate-400">Bonus inscription</label><input type="number" name="sign" value="<?= $set['signup_bonus'] ?>"></div>
                        </div>
                        <div><label class="text-xs text-slate-400">Retrait minimum</label><input type="number" name="min_wd" value="<?= $set['min_withdraw'] ?>"></div>
                        <div><label class="text-xs text-pink-500">Bkash Number</label><input type="text" name="bkash" value="<?= $set['bkash_number'] ?>"></div>
                        <div><label class="text-xs text-orange-500">Nagad Number</label><input type="text" name="nagad" value="<?= $set['nagad_number'] ?>"></div>
                        <div><label class="text-xs text-purple-500">Rocket Number</label><input type="text" name="rocket" value="<?= $set['rocket_number'] ?>"></div>
                        <button name="update_settings" class="w-full bg-blue-600 py-2 rounded font-bold text-white mt-2">Enregistrer</button>
                    </form>
                </div>

                <div class="space-y-6">
                    <!-- Notifications -->
                    <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700">
                        <h3 class="font-bold text-white mb-4">Envoyer une notification</h3>
                        <form method="POST" class="space-y-3">
                            <input type="text" name="uid" placeholder="ID utilisateur ou 'all'" required>
                            <input type="text" name="title" placeholder="Titre" required>
                            <textarea name="msg" placeholder="Message" rows="2" required></textarea>
                            <button name="send_notif" class="w-full bg-green-600 py-2 rounded font-bold text-white">Envoyer</button>
                        </form>
                    </div>
                    <!-- Password -->
                    <div class="bg-slate-800/50 p-6 rounded-xl border border-red-500/30">
                        <h3 class="font-bold text-white mb-4">Mot de passe admin</h3>
                        <form method="POST" class="space-y-3">
                            <input type="password" name="p1" placeholder="Nouveau mot de passe" required>
                            <input type="password" name="p2" placeholder="Confirmer le mot de passe" required>
                            <button name="change_pass" class="w-full bg-red-600 py-2 rounded font-bold text-white">Modifier</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sliders -->
            <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700">
                <h3 class="font-bold text-white mb-4">Sliders de l'application</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <form method="POST" enctype="multipart/form-data" class="space-y-3">
                            <input type="text" name="title" placeholder="Titre">
                            <input type="text" name="sub" placeholder="Sous-titre">
                            <input type="file" name="img" class="text-xs" required>
                            <button name="add_slider" class="w-full bg-purple-600 py-2 rounded font-bold text-white">Ajouter une slide</button>
                        </form>
                    </div>
                    <div class="md:col-span-2 grid grid-cols-2 gap-4">
                        <?php foreach($sliders as $s): ?>
                        <div class="relative group">
                            <img src="assets/sliders/<?= $s['image'] ?>" class="w-full h-32 object-cover rounded-lg">
                            <a href="?del_slider=<?= $s['id'] ?>" class="absolute top-1 right-1 bg-red-600 text-white w-6 h-6 flex items-center justify-center rounded-full text-xs">X</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>
</body>
</html>
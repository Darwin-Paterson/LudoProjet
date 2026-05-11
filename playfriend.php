<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- 1. GESTION DE SESSION & UTILISATEUR ---
// Si l'utilisateur n'est pas connecté, on lui attribue un nom temporaire (votre système de connexion peut remplacer cela)
if (!isset($_SESSION['username'])) { 
    $_SESSION['username'] = "Guest_" . rand(1000, 9999); 
}
$playerName = $_SESSION['username'];

// --- 2. LOGIQUE DE SALON ---
$room_code = "";
$is_in_room = false;

// Création d'un nouveau salon (Create Room)
if (isset($_POST['create_room'])) {
    $room_code = rand(100000, 999999); // ৬ সংখ্যার রুম কোড
    header("Location: playfriend.php?room=" . $room_code);
    exit();
}

// Rejoindre un salon existant (Join Room)
if (isset($_POST['join_room'])) {
    $room_code = trim($_POST['room_input']);
    if (!empty($room_code)) {
        header("Location: playfriend.php?room=" . $room_code);
        exit();
    }
}

// Si un code salon est présent dans l'URL
if (isset($_GET['room'])) {
    $room_code = htmlspecialchars($_GET['room']);
    $is_in_room = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<?php include __DIR__.'/config/pwa.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Jouer entre amis - Ludo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Firebase SDK (Realtime Database) -->
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>

    <style>
        body { 
            background: #0f172a;
            height: 100vh; overflow: hidden; font-family: 'Inter', sans-serif; user-select: none;
            display: flex; flex-direction: column; align-items: center; justify-content: center; color: white;
        }

        /* LOBBY STYLES */
        .lobby-card {
            background: rgba(30, 41, 59, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem; border-radius: 1rem;
            text-align: center; width: 90%; max-width: 400px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .btn-custom {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            padding: 12px 24px; border-radius: 8px; font-weight: bold;
            width: 100%; display: block; margin-bottom: 15px;
            transition: transform 0.2s;
        }
        .btn-custom:active { transform: scale(0.98); }
        .input-code {
            background: #334155; border: 1px solid #475569;
            color: white; padding: 12px; border-radius: 8px;
            width: 100%; text-align: center; letter-spacing: 2px; font-size: 1.2rem;
            margin-bottom: 10px; outline: none;
        }

        /* GAME BOARD STYLES */
        .board-wrapper {
            width: 95vw; max-width: 440px; aspect-ratio: 1/1;
            position: relative; background: #334155; 
            border: 8px solid #1e293b; border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
        .grid-15 {
            display: grid; grid-template-columns: repeat(15, 1fr); grid-template-rows: repeat(15, 1fr);
            width: 100%; height: 100%; gap: 1px; background: #cbd5e1;
        }
        .cell { background: #f8fafc; position: relative; display: flex; justify-content: center; align-items: center; }
        .bg-g { background: #22c55e !important; } 
        .bg-r { background: #ef4444 !important; } 
        .bg-b { background: #3b82f6 !important; } 
        .bg-y { background: #eab308 !important; } 
        
        .base { grid-row: span 6; grid-column: span 6; padding: 10%; z-index: 10; }
        .base-inner { 
            background: #fff; width: 100%; height: 100%; border-radius: 15px;
            display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; place-items: center;
        }
        .spot { width: 55%; height: 55%; border-radius: 50%; border: 3px solid rgba(255,255,255,0.8); }
        .center-home { grid-row: 7/10; grid-column: 7/10; position: relative; background: #fff; }
        .tri { position: absolute; width: 100%; height: 100%; clip-path: polygon(50% 50%, 0 0, 100% 0); }
        .tri-g { background: #22c55e; transform: rotate(-90deg); }
        .tri-r { background: #ef4444; }
        .tri-b { background: #3b82f6; transform: rotate(90deg); }
        .tri-y { background: #eab308; transform: rotate(180deg); }

        .token {
            width: 6.66%; height: 6.66%; position: absolute; z-index: 100;
            display: flex; justify-content: center; align-items: center;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .pawn { 
            width: 80%; height: 80%; border-radius: 50%; border: 2px solid #fff; 
            position: relative; box-shadow: 0 4px 6px rgba(0,0,0,0.4);
        }
        .active-glow { animation: bounce 0.8s infinite; cursor: pointer; border: 2px solid #fbbf24; z-index: 101; pointer-events: auto !important; }
        @keyframes bounce { 0%, 100% { transform: scale(1.1); } 50% { transform: scale(1.3) translateY(-5px); } }

        .dice-area { width: 100%; max-width: 440px; margin-top: 1.5rem; height: 80px; position: relative; }
        .dice-box { 
            width: 70px; height: 70px; background: linear-gradient(145deg, #ffffff, #e2e8f0); 
            border-radius: 18px; display: flex; justify-content: center; align-items: center; 
            font-size: 32px; cursor: pointer; position: absolute; top: 0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
        #box-green { left: 10%; } #box-red { right: 10%; }
        .dice-hidden { opacity: 0; pointer-events: none; transform: scale(0.5); }
        .dice-visible { opacity: 1; transform: scale(1); }
        .active-turn { outline: 4px solid #f59e0b; }

        .hidden { display: none !important; }
        .glass-ui { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; }
        .timer-bar { height: 4px; border-radius: 2px; transition: width 0.1s linear; }
        .star-icon { font-size: 10px; color: #94a3b8; }
        @media (min-width: 400px) { .star-icon { font-size: 14px; } }
    </style>
</head>
<body>

    <?php if (!$is_in_room): ?>
        <!-- === LOBBY SCREEN (ROOM GENERATE/JOIN) === -->
        <div class="lobby-card">
            <h1 class="text-3xl font-bold mb-2 text-yellow-500">Jouer entre amis</h1>
            <p class="text-gray-400 mb-6 text-sm">Créez un salon ou rejoignez celui d'un ami.</p>
            
            <form method="POST">
                <button type="submit" name="create_room" class="btn-custom">
                    <i class="fa fa-plus-circle mr-2"></i> Créer un salon
                </button>
            </form>

            <div class="flex items-center my-4">
                <div class="h-px bg-gray-600 flex-1"></div>
                <span class="px-3 text-gray-500 text-sm">OU REJOINDRE</span>
                <div class="h-px bg-gray-600 flex-1"></div>
            </div>

            <form method="POST">
                <input type="number" name="room_input" class="input-code" placeholder="Saisir le code salon" required>
                <button type="submit" name="join_room" class="btn-custom" style="background: #10b981;">
                    <i class="fa fa-sign-in-alt mr-2"></i> Rejoindre le salon
                </button>
            </form>
        </div>

    <?php else: ?>
        <!-- === GAME SCREEN === -->
        
        <!-- Room Info Header -->
        <div class="fixed top-4 left-4 z-50">
            <div class="glass-ui px-4 py-2 flex items-center gap-2">
                <span class="text-xs text-gray-400">SALON :</span>
                <span class="text-xl font-mono font-bold text-yellow-400"><?= $room_code ?></span>
                <button onclick="copyCode()" class="ml-2 text-gray-400 hover:text-white"><i class="fa fa-copy"></i></button>
            </div>
        </div>

        <!-- WIN OVERLAY -->
        <div id="win-overlay" class="hidden fixed inset-0 z-[2000] bg-slate-900/95 flex flex-col items-center justify-center backdrop-blur-md">
            <i class="fa fa-trophy text-yellow-500 text-7xl mb-4 animate-bounce"></i>
            <h1 id="win-message" class="text-5xl font-black text-white uppercase tracking-widest mb-2">GAGNANT !</h1>
            <p class="text-gray-400 mb-8 font-bold">Partie amicale terminée</p>
            <a href="playfriend.php" class="bg-blue-600 px-8 py-3 rounded-full font-bold hover:bg-blue-500">Retour au lobby</a>
        </div>

        <!-- PLAYER INFO -->
        <div class="w-full max-w-[440px] flex justify-between items-center px-4 mb-4 mt-16">
            <!-- Green (Host) -->
            <div class="w-36 glass-ui p-2 border-l-4 border-green-500">
                <div class="flex items-center gap-2 text-white text-xs font-bold">
                    <img id="avatar-green" src="https://api.dicebear.com/7.x/avataaars/svg?seed=Green" class="w-8 h-8 rounded-full bg-white"/>
                    <div class="flex flex-col">
                        <span id="name-green" class="truncate max-w-[80px]">Waiting...</span>
                        <span id="score-green" class="text-[10px] text-green-400 font-mono">HOME: 0/4</span>
                    </div>
                </div>
                <div class="h-1.5 w-full bg-gray-700 mt-2 rounded-full overflow-hidden">
                    <div id="timer-green" class="timer-bar bg-green-500 w-0"></div>
                </div>
            </div>

            <!-- Red (Guest) -->
            <div class="w-36 glass-ui p-2 text-right border-r-4 border-red-500">
                <div class="flex items-center gap-2 flex-row-reverse text-white text-xs font-bold">
                    <img id="avatar-red" src="https://api.dicebear.com/7.x/avataaars/svg?seed=Red" class="w-8 h-8 rounded-full bg-white"/>
                    <div class="flex flex-col items-end">
                        <span id="name-red" class="truncate max-w-[80px]">Waiting...</span>
                        <span id="score-red" class="text-[10px] text-red-400 font-mono">HOME: 0/4</span>
                    </div>
                </div>
                <div class="h-1.5 w-full bg-gray-700 mt-2 rounded-full overflow-hidden">
                    <div id="timer-red" class="timer-bar bg-red-500 w-0"></div>
                </div>
            </div>
        </div>

        <!-- BOARD -->
        <div class="board-wrapper">
            <div class="grid-15" id="ludo-grid"></div>
            <div id="token-layer" class="absolute inset-0 pointer-events-none"></div>
        </div>

        <!-- CONTROLS -->
        <div class="dice-area">
            <div id="box-green" class="dice-box dice-hidden" onclick="handleDiceClick('green')">
                <div id="dice-green"><i class="fa fa-dice-one text-green-600"></i></div>
            </div>
            
            <div class="absolute inset-x-0 top-4 text-center pointer-events-none">
                <div id="status-text" class="text-white/80 text-[10px] font-black uppercase tracking-[0.2em]">Connecting...</div>
            </div>
            
            <div id="box-red" class="dice-box dice-hidden" onclick="handleDiceClick('red')">
                <div id="dice-red"><i class="fa fa-dice-one text-red-600"></i></div>
            </div>
        </div>

        <script>
            // --- CONFIGURATION FIREBASE ---
            const firebaseConfig = {
                databaseURL: "https://imtiazludupro-default-rtdb.firebaseio.com" // URL de votre base Firebase
            };
            if (!firebase.apps.length) firebase.initializeApp(firebaseConfig);
            
            // --- VARIABLES DE JEU ---
            const roomCode = "<?= $room_code ?>";
            const playerName = "<?= $playerName ?>";
            const gameRef = firebase.database().ref('friend_rooms/' + roomCode);
            
            // Système audio
            const sounds = { 
                dice: new Audio('sounds/dice.mp3'), 
                move: new Audio('sounds/move.mp3'), 
                capture: new Audio('sounds/capture.mp3'), 
                home: new Audio('sounds/home.mp3'),
                win: new Audio('https://cdn.pixabay.com/audio/2021/08/04/audio_bb313337f7.mp3')
            };
            function playSfx(name) { try { sounds[name].currentTime = 0; sounds[name].play().catch(()=>{}); } catch(e){} }

            // Données de logique Ludo (chemins, cases sûres, etc.)
            const safeIndices = [1, 9, 14, 22, 27, 35, 40, 48]; 
            const commonPath = [{r:7,c:1},{r:7,c:2},{r:7,c:3},{r:7,c:4},{r:7,c:5},{r:7,c:6},{r:6,c:7},{r:5,c:7},{r:4,c:7},{r:3,c:7},{r:2,c:7},{r:1,c:7},{r:1,c:8},{r:1,c:9},{r:2,c:9},{r:3,c:9},{r:4,c:9},{r:5,c:9},{r:6,c:9},{r:7,c:10},{r:7,c:11},{r:7,c:12},{r:7,c:13},{r:7,c:14},{r:7,c:15},{r:8,c:15},{r:9,c:15},{r:9,c:14},{r:9,c:13},{r:9,c:12},{r:9,c:11},{r:9,c:10},{r:10,c:9},{r:11,c:9},{r:12,c:9},{r:13,c:9},{r:14,c:9},{r:15,c:9},{r:15,c:8},{r:15,c:7},{r:14,c:7},{r:13,c:7},{r:12,c:7},{r:11,c:7},{r:10,c:7},{r:9,c:6},{r:9,c:5},{r:9,c:4},{r:9,c:3},{r:9,c:2},{r:9,c:1},{r:8,c:1}];
            const homePaths = { green: [{r:8,c:2},{r:8,c:3},{r:8,c:4},{r:8,c:5},{r:8,c:6},{r:8,c:7}], red: [{r:2,c:8},{r:3,c:8},{r:4,c:8},{r:5,c:8},{r:6,c:8},{r:7,c:8}] };

            let myColor = null;
            let gameState = {
                turn: 'green', dice: null, rolling: false,
                tokens: { green: [-1,-1,-1,-1], red: [-1,-1,-1,-1] },
                players: { green: null, red: null },
                winner: null, turnStartTime: Date.now()
            };

            // --- LOGIQUE D'ENTRÉE DANS LE SALON & SYNCHRO ---
            gameRef.on('value', (snapshot) => {
                const data = snapshot.val();
                
                // Initialiser le salon s'il n'existe pas encore
                if(!data) {
                    myColor = 'green';
                    gameState.players.green = playerName;
                    gameState.turnStartTime = firebase.database.ServerValue.TIMESTAMP;
                    gameRef.set(gameState);
                } else {
                    gameState = data;
                    
                    // Attribuer une couleur au joueur si ce n'est pas encore fait
                    if(!myColor) {
                        if(gameState.players.green === playerName) myColor = 'green';
                        else if(gameState.players.red === playerName) myColor = 'red';
                        else if(!gameState.players.red) {
                            myColor = 'red';
                            gameRef.child('players').update({ red: playerName });
                        } else {
                            alert("Le salon est complet !");
                            window.location.href = "playfriend.php";
                            return;
                        }
                    }
                    renderUI();
                }
            });

            // --- LOGIQUE DE TIMER ---
            setInterval(() => {
                if(!gameState.turnStartTime || gameState.winner || !gameState.players.red) return; // Ne pas lancer le timer s'il manque un joueur
                
                const elapsed = Date.now() - gameState.turnStartTime;
                const limit = 15000; // 15 secondes par tour
                const percent = Math.max(0, 100 - ((elapsed / limit) * 100));

                if(gameState.turn === 'green') {
                    document.getElementById('timer-green').style.width = percent + '%';
                    document.getElementById('timer-red').style.width = '0%';
                } else {
                    document.getElementById('timer-red').style.width = percent + '%';
                    document.getElementById('timer-green').style.width = '0%';
                }

                // Passer automatiquement au tour suivant lorsque le temps est écoulé (logique côté hôte)
                if (elapsed > limit && gameState.turn === myColor && !gameState.rolling) {
                    switchTurn();
                }
            }, 100);

            // --- RENDU DE L'INTERFACE ---
            function renderUI() {
                document.getElementById('name-green').innerText = gameState.players.green || "Waiting...";
                document.getElementById('name-red').innerText = gameState.players.red || "Waiting...";
                
                // Texte de statut affiché
                if(!gameState.players.red) {
                    document.getElementById('status-text').innerText = "EN ATTENTE D'UN AMI...";
                } else {
                    const isMyTurn = gameState.turn === myColor;
                    document.getElementById('status-text').innerText = isMyTurn ? "VOTRE TOUR" : "TOUR ADVERSE";
                    document.getElementById('status-text').className = isMyTurn ? "text-yellow-400 font-black uppercase animate-pulse" : "text-white/50 font-bold uppercase";
                }

                // Visibilité des dés selon le tour
                const gBox = document.getElementById('box-green');
                const rBox = document.getElementById('box-red');

                if(gameState.turn === 'green') {
                    gBox.className = "dice-box dice-visible active-turn";
                    rBox.className = "dice-box dice-hidden";
                } else {
                    rBox.className = "dice-box dice-visible active-turn";
                    gBox.className = "dice-box dice-hidden";
                }

                // Affichage de l'icône de dé
                const activeDiceBox = gameState.turn === 'green' ? 'dice-green' : 'dice-red';
                const diceContainer = document.getElementById(activeDiceBox);
                if(gameState.dice) {
                    const icons = ['one','two','three','four','five','six'];
                    diceContainer.innerHTML = `<i class="fa fa-dice-${icons[gameState.dice-1]} text-${gameState.turn}-600"></i>`;
                } else if (gameState.rolling) {
                    diceContainer.innerHTML = `<i class="fa fa-spinner fa-spin text-${gameState.turn}-600"></i>`;
                } else {
                    diceContainer.innerHTML = `<i class="fa fa-dice-d6 text-${gameState.turn}-600 opacity-50"></i>`;
                }

                drawTokens();

                // Logique de détermination du gagnant
                if(gameState.winner) {
                    document.getElementById('win-overlay').classList.remove('hidden');
                    const msg = document.getElementById('win-message');
                    if(gameState.winner === myColor) {
                        msg.innerText = "VOUS AVEZ GAGNÉ !";
                        msg.className = "text-5xl font-black text-green-500 uppercase";
                        playSfx('win');
                    } else {
                        msg.innerText = "VOUS AVEZ PERDU !";
                        msg.className = "text-5xl font-black text-red-500 uppercase";
                    }
                }
            }

            // --- ACTIONS DE JEU ---
            function handleDiceClick(color) {
                if (gameState.winner || gameState.turn !== myColor || color !== myColor || gameState.dice || gameState.rolling) return;
                if (!gameState.players.red) return; // Impossible de jouer seul

                playSfx('dice');
                gameRef.update({ rolling: true });

                let roll = Math.floor(Math.random() * 6) + 1;
                setTimeout(() => {
                    gameRef.update({ rolling: false, dice: roll });
                    
                    // Vérifier si au moins un pion peut être joué
                    const canMove = gameState.tokens[myColor].some(pos => (pos === -1 ? roll === 6 : pos + roll <= 56));
                    
                    if(!canMove) {
                        setTimeout(switchTurn, 1000);
                    }
                }, 600);
            }

            function drawTokens() {
                const layer = document.getElementById('token-layer');
                layer.innerHTML = '';
                let scores = { green: 0, red: 0 };

                ['green', 'red'].forEach(color => {
                    gameState.tokens[color].forEach((pos, i) => {
                        if(pos === 56) { scores[color]++; return; }

                        let coords;
                        if (pos === -1) {
                            // Positions de base
                            coords = color === 'green' ? [{r:2,c:2},{r:2,c:5},{r:5,c:2},{r:5,c:5}][i] : [{r:2,c:11},{r:2,c:14},{r:5,c:11},{r:5,c:14}][i];
                        } else if (pos < 51) {
                            // Chemin commun
                            let offset = color === 'green' ? 1 : 14;
                            coords = commonPath[(pos + offset) % 52];
                        } else {
                            // Chemin de retour à la maison
                            coords = homePaths[color][pos - 51];
                        }

                        const token = document.createElement('div');
                        token.className = 'token';
                        token.style.left = (coords.c-1)*6.66 + '%';
                        token.style.top = (coords.r-1)*6.66 + '%';

                        const isMovable = (gameState.turn === myColor && color === myColor && gameState.dice && (pos === -1 ? gameState.dice === 6 : pos + gameState.dice <= 56));
                        
                        token.innerHTML = `<div class="pawn bg-${color === 'green' ? 'green-500' : 'red-500'} ${isMovable ? 'active-glow' : ''}"></div>`;
                        
                        if(isMovable) token.onclick = () => moveToken(i);
                        layer.appendChild(token);
                    });
                });

                document.getElementById('score-green').innerText = `HOME: ${scores.green}/4`;
                document.getElementById('score-red').innerText = `HOME: ${scores.red}/4`;

                // Déclenchement de la victoire (sans mise à jour de solde)
                if(scores.green === 4 && !gameState.winner) gameRef.update({winner: 'green'});
                if(scores.red === 4 && !gameState.winner) gameRef.update({winner: 'red'});
            }

            function moveToken(idx) {
                let pos = gameState.tokens[myColor][idx];
                let dice = gameState.dice;
                playSfx('move');

                if (pos === -1) pos = 0;
                else pos += dice;

                gameState.tokens[myColor][idx] = pos;

                // Logique de capture d'un pion adverse
                if(pos < 51) {
                    let myOffset = myColor === 'green' ? 1 : 14;
                    let myGlobalPos = (pos + myOffset) % 52;
                    
                    if(!safeIndices.includes(myGlobalPos)) {
                        let oppColor = myColor === 'green' ? 'red' : 'green';
                        let oppOffset = oppColor === 'green' ? 1 : 14;
                        
                        gameState.tokens[oppColor].forEach((oppPos, oppIdx) => {
                            if(oppPos > -1 && oppPos < 51) {
                                let oppGlobalPos = (oppPos + oppOffset) % 52;
                                if(myGlobalPos === oppGlobalPos) {
                                    gameState.tokens[oppColor][oppIdx] = -1; // Ramener le pion adverse à la base
                                    playSfx('capture');
                                }
                            }
                        });
                    }
                }
                if(pos === 56) playSfx('home');

                let nextTurn = (dice === 6) ? myColor : (myColor === 'green' ? 'red' : 'green');
                
                gameRef.update({ 
                    tokens: gameState.tokens, dice: null, turn: nextTurn,
                    turnStartTime: firebase.database.ServerValue.TIMESTAMP 
                });
            }

            function switchTurn() {
                let next = (gameState.turn === 'green') ? 'red' : 'green';
                gameRef.update({ 
                    turn: next, dice: null,
                    turnStartTime: firebase.database.ServerValue.TIMESTAMP 
                });
            }

            function copyCode() {
                navigator.clipboard.writeText(roomCode);
                alert("Code du salon copié : " + roomCode);
            }

            // Génération du plateau de jeu
            function initBoard() {
                let h = '';
                for(let r=1; r<=15; r++) {
                    for(let c=1; c<=15; c++) {
                        if(r<=6 && c<=6) { if(r===1 && c===1) h += getBase('green'); continue; }
                        if(r<=6 && c>=10) { if(r===1 && c===10) h += getBase('red'); continue; }
                        if(r>=10 && c<=6) { if(r===10 && c===1) h += getBase('yellow'); continue; }
                        if(r>=10 && c>=10) { if(r===10 && c===10) h += getBase('blue'); continue; }
                        if(r>=7 && r<=9 && c>=7 && c<=9) { if(r===7 && c===7) h += getCenter(); continue; }
                        
                        let bg='', ic='';
                        if(r===7 && c===2) { bg='bg-g'; ic='<i class="fa fa-star star-icon"></i>'; }
                        else if(r===2 && c===9) { bg='bg-r'; ic='<i class="fa fa-star star-icon"></i>'; }
                        else if(r===9 && c===14) { bg='bg-b'; ic='<i class="fa fa-star star-icon"></i>'; }
                        else if(r===14 && c===7) { bg='bg-y'; ic='<i class="fa fa-star star-icon"></i>'; }
                        else if((r===7 && c===13) || (r===13 && c===9) || (r===9 && c===3) || (r===3 && c===7)) { ic='<i class="fa fa-star star-icon"></i>'; }
                        else if(r===8 && c>1 && c<7) bg='bg-g';
                        else if(c===8 && r>1 && r<7) bg='bg-r';
                        else if(r===8 && c>9 && c<15) bg='bg-b';
                        else if(c===8 && r>9 && r<15) bg='bg-y';
                        
                        h += `<div class="cell ${bg}" style="grid-row:${r}; grid-column:${c}">${ic}</div>`;
                    }
                }
                document.getElementById('ludo-grid').innerHTML = h;
            }
            function getBase(c) { return `<div class="base bg-${c[0]}" style="grid-row:span 6; grid-column:span 6"><div class="base-inner"><div class="spot bg-${c[0]}"></div><div class="spot bg-${c[0]}"></div><div class="spot bg-${c[0]}"></div><div class="spot bg-${c[0]}"></div></div></div>`; }
            function getCenter() { return `<div class="center-home" style="grid-row:span 3; grid-column:span 3"><div class="tri tri-g"></div><div class="tri tri-r"></div><div class="tri tri-y"></div><div class="tri tri-b"></div></div>`; }
            
            initBoard();
        </script>
    <?php endif; ?>
</body>
</html>
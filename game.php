<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['username'])) { $_SESSION['username'] = "Aadvik"; }
$playerName = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ludo Royal Premium - Solo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* PREMIUM DESIGN STYLES */
        body { 
            background: #0f172a;
            height: 100vh; overflow: hidden; font-family: 'Inter', sans-serif; user-select: none;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }

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
        
        /* Star Icons */
        .star-icon { font-size: 10px; color: #94a3b8; z-index: 5; position: relative; }
        @media (min-width: 400px) { .star-icon { font-size: 14px; } }
        .cell.bg-g .star-icon, .cell.bg-r .star-icon, .cell.bg-b .star-icon, .cell.bg-y .star-icon { color: white !important; opacity: 0.9; }

        .base { grid-row: span 6; grid-column: span 6; padding: 10%; z-index: 10; }
        .base-inner { 
            background: #fff; width: 100%; height: 100%; border-radius: 15px;
            display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; place-items: center;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.1);
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
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .pawn { 
            width: 80%; height: 80%; border-radius: 50%; border: 2px solid #fff; 
            position: relative; box-shadow: 0 4px 6px rgba(0,0,0,0.4);
        }

        .active-glow { animation: bounce 0.8s infinite; cursor: pointer; pointer-events: auto !important; border: 2px solid #fbbf24; z-index: 101; }
        @keyframes bounce { 0%, 100% { transform: scale(1.1); } 50% { transform: scale(1.3) translateY(-4px); } }

        .glass-ui { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; transition: all 0.3s ease; }
        
        .dice-box { 
            width: 70px; height: 70px; 
            background: linear-gradient(145deg, #ffffff, #e2e8f0); 
            border-radius: 18px; display: flex; justify-content: center; align-items: center; 
            font-size: 32px; cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0.5; transform: scale(0.9);
        }
        .active-turn { opacity: 1; transform: scale(1.05); outline: 3px solid #f59e0b; box-shadow: 0 0 30px rgba(245, 158, 11, 0.4); }
        
        .timer-bar { height: 4px; border-radius: 2px; transition: width 1s linear; }

        /* WIN OVERLAY */
        #win-overlay {
            display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.98);
            z-index: 2000; flex-direction: column; align-items: center; justify-content: center;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>

    <!-- WIN OVERLAY -->
    <div id="win-overlay">
        <div class="text-center animate-bounce">
            <i class="fa fa-crown text-yellow-500 text-7xl mb-4 filter drop-shadow-lg"></i>
            <h1 id="win-message" class="text-5xl font-black text-white uppercase tracking-widest mb-2">VOUS GAGNEZ !</h1>
            <p id="win-sub" class="text-gray-400 mb-8 font-bold">Félicitations pour votre victoire !</p>
            <button onclick="location.reload()" class="bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-400 hover:to-orange-400 text-white font-bold py-4 px-12 rounded-full transition-all active:scale-95 shadow-xl">REJOUER</button>
        </div>
    </div>

    <!-- PLAYER INFO -->
    <div class="w-full max-w-[440px] flex justify-between items-center px-4 mb-4">
        <div class="w-36 glass-ui p-2 border-l-4 border-green-500">
            <div class="flex items-center gap-2 text-white text-xs font-bold">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= $playerName ?>" class="w-8 h-8 rounded-full bg-white ring-2 ring-green-500"/>
                <div class="flex flex-col">
                    <span class="truncate max-w-[80px]"><?= $playerName ?></span>
                    <span id="score-green" class="text-[10px] text-green-400 font-mono bg-green-900/30 px-1 rounded">HOME: 0/4</span>
                </div>
            </div>
            <div class="h-1.5 w-full bg-gray-700 mt-2 rounded-full overflow-hidden">
                <div id="timer-green" class="timer-bar bg-green-500 w-full"></div>
            </div>
        </div>
        <div class="w-36 glass-ui p-2 text-right border-r-4 border-red-500">
            <div class="flex items-center gap-2 flex-row-reverse text-white text-xs font-bold">
                <img src="https://api.dicebear.com/7.x/bottts/svg?seed=Bot" class="w-8 h-8 rounded-full bg-white ring-2 ring-red-500"/>
                <div class="flex flex-col items-end">
                    <span>CPU</span>
                    <span id="score-red" class="text-[10px] text-red-400 font-mono bg-red-900/30 px-1 rounded">HOME: 0/4</span>
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
    <div class="w-full max-w-[440px] mt-8 flex justify-around items-center relative">
        <div id="box-green" class="dice-box active-turn" onclick="rollDice('green')">
            <div id="dice-green"><i class="fa fa-dice-one text-green-600"></i></div>
        </div>
        
        <div class="absolute -top-4 left-0 right-0 text-center">
            <div class="text-white/80 text-[10px] font-black uppercase tracking-[0.2em]" id="status-text">Votre tour</div>
        </div>

        <div id="box-red" class="dice-box">
            <div id="dice-red"><i class="fa fa-dice-one text-red-600"></i></div>
        </div>
    </div>

    <script>
        const sounds = { 
            dice: new Audio('sounds/dice.mp3'), 
            move: new Audio('sounds/move.mp3'), 
            capture: new Audio('sounds/capture.mp3'), 
            home: new Audio('sounds/home.mp3'),
            win: new Audio('https://cdn.pixabay.com/audio/2021/08/04/audio_bb313337f7.mp3') // Son de victoire
        };
        function playSfx(name) { try { sounds[name].currentTime = 0; sounds[name].play().catch(e => {}); } catch(e){} }

        const grid = document.getElementById('ludo-grid');
        const tokenLayer = document.getElementById('token-layer');
        const commonPath = [{r:7,c:1},{r:7,c:2},{r:7,c:3},{r:7,c:4},{r:7,c:5},{r:7,c:6},{r:6,c:7},{r:5,c:7},{r:4,c:7},{r:3,c:7},{r:2,c:7},{r:1,c:7},{r:1,c:8},{r:1,c:9},{r:2,c:9},{r:3,c:9},{r:4,c:9},{r:5,c:9},{r:6,c:9},{r:7,c:10},{r:7,c:11},{r:7,c:12},{r:7,c:13},{r:7,c:14},{r:7,c:15},{r:8,c:15},{r:9,c:15},{r:9,c:14},{r:9,c:13},{r:9,c:12},{r:9,c:11},{r:9,c:10},{r:10,c:9},{r:11,c:9},{r:12,c:9},{r:13,c:9},{r:14,c:9},{r:15,c:9},{r:15,c:8},{r:15,c:7},{r:14,c:7},{r:13,c:7},{r:12,c:7},{r:11,c:7},{r:10,c:7},{r:9,c:6},{r:9,c:5},{r:9,c:4},{r:9,c:3},{r:9,c:2},{r:9,c:1},{r:8,c:1}];
        const safeIndexes = [0, 8, 13, 21, 26, 34, 39, 47];
        const homePaths = { green: [{r:8,c:2},{r:8,c:3},{r:8,c:4},{r:8,c:5},{r:8,c:6},{r:8,c:7}], red: [{r:2,c:8},{r:3,c:8},{r:4,c:8},{r:5,c:8},{r:6,c:8},{r:7,c:8}] };

        let state = { turn: 'green', dice: null, rolling: false, timeLeft: 10, tokens: { green: [-1, -1, -1, -1], red: [-1, -1, -1, -1] }, lastRollWasSix: false, gameEnded: false };

        setInterval(() => {
            if (!state.rolling && !state.dice && !state.gameEnded) {
                state.timeLeft--;
                if (state.timeLeft < 0) nextTurn();
                updateTimerUI();
            }
        }, 1000);

        function updateTimerUI() {
            const g = document.getElementById('timer-green'), r = document.getElementById('timer-red');
            let p = (state.timeLeft / 10) * 100;
            if (state.turn === 'green') { g.style.width = p + '%'; r.style.width = '0%'; }
            else { r.style.width = p + '%'; g.style.width = '0%'; }
        }

        // Génération du plateau (version premium)
        function initBoard() {
            let h = '';
            for(let r=1; r<=15; r++) {
                for(let c=1; c<=15; c++) {
                    // Bases des joueurs
                    if(r<=6 && c<=6) { if(r===1 && c===1) h += getBase('green'); continue; }
                    if(r<=6 && c>=10) { if(r===1 && c===10) h += getBase('red'); continue; }
                    if(r>=10 && c<=6) { if(r===10 && c===1) h += getBase('yellow'); continue; }
                    if(r>=10 && c>=10) { if(r===10 && c===10) h += getBase('blue'); continue; }
                    
                    // Zone centrale
                    if(r>=7 && r<=9 && c>=7 && c<=9) { if(r===7 && c===7) h += getCenter(); continue; }
                    
                    // Cases et étoiles
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
            grid.innerHTML = h;
        }

        function getBase(c) { return `<div class="base bg-${c[0]}" style="grid-row:span 6; grid-column:span 6"><div class="base-inner"><div class="spot bg-${c[0]}"></div><div class="spot bg-${c[0]}"></div><div class="spot bg-${c[0]}"></div><div class="spot bg-${c[0]}"></div></div></div>`; }
        function getCenter() { return `<div class="center-home" style="grid-row:span 3; grid-column:span 3"><div class="tri tri-g"></div><div class="tri tri-r"></div><div class="tri tri-y"></div><div class="tri tri-b"></div></div>`; }

        function renderTokens() {
            tokenLayer.innerHTML = '';
            let scores = { green: 0, red: 0 };
            
            ['green', 'red'].forEach(color => {
                state.tokens[color].forEach((posIdx, i) => {
                    if(posIdx === 56) scores[color]++; // Compter les pions arrivés à la maison

                    let coords;
                    if (posIdx === -1) { coords = color === 'green' ? [{r:2,c:2},{r:2,c:5},{r:5,c:2},{r:5,c:5}][i] : [{r:2,c:11},{r:2,c:14},{r:5,c:11},{r:5,c:14}][i]; }
                    else if (posIdx >= 51 && posIdx < 56) { coords = homePaths[color][posIdx - 51]; }
                    else if (posIdx === 56) { coords = {r:8, c:8}; } // Dans la zone centrale
                    else { let offset = color === 'green' ? 1 : 14; coords = commonPath[(posIdx + offset) % 52]; }
                    
                    const tokenEl = document.createElement('div');
                    tokenEl.className = 'token';
                    tokenEl.style.left = ((coords.c - 1) * 6.66) + '%';
                    tokenEl.style.top = ((coords.r - 1) * 6.66) + '%';
                    if(posIdx === 56) tokenEl.style.opacity = '0'; // Masquer les pions déjà terminés

                    const canMove = (!state.gameEnded && state.turn === color && state.dice && (posIdx === -1 ? state.dice === 6 : posIdx + state.dice <= 56) && posIdx !== 56);
                    tokenEl.innerHTML = `<div class="pawn bg-${color === 'green' ? 'green-500' : 'red-500'} ${canMove ? 'active-glow' : ''}"></div>`;
                    if (canMove && color === 'green') tokenEl.onclick = () => moveToken(color, i);
                    tokenLayer.appendChild(tokenEl);
                });
            });

            document.getElementById('score-green').innerText = `HOME: ${scores.green}/4`;
            document.getElementById('score-red').innerText = `HOME: ${scores.red}/4`;

            if(scores.green === 4) showWin('green');
            if(scores.red === 4) showWin('red');
        }

        function showWin(winner) {
            state.gameEnded = true;
            playSfx('win');
            const overlay = document.getElementById('win-overlay');
            const msg = document.getElementById('win-message');
            const sub = document.getElementById('win-sub');
            overlay.style.display = 'flex';
            if(winner === 'green') {
                msg.innerText = "VOUS GAGNEZ !";
                msg.classList.add('text-green-500');
                sub.innerText = "Excellente partie ! Vous avez battu le CPU.";
            } else {
                msg.innerText = "CPU GAGNE !";
                msg.classList.add('text-red-500');
                sub.innerText = "Bonne chance la prochaine fois ! Le CPU a été plus rapide.";
            }
        }

        function rollDice(p) {
            if (state.gameEnded || state.turn !== p || state.rolling || state.dice) return;
            state.rolling = true;
            playSfx('dice');
            let count = 0;
            let iv = setInterval(() => {
                let r = Math.floor(Math.random() * 6) + 1;
                document.getElementById(`dice-${p}`).innerHTML = `<i class="fa fa-dice-${['one','two','three','four','five','six'][r-1]}"></i>`;
                if (++count > 12) {
                    clearInterval(iv);
                    state.dice = Math.floor(Math.random() * 6) + 1;
                    document.getElementById(`dice-${p}`).innerHTML = `<i class="fa fa-dice-${['one','two','three','four','five','six'][state.dice-1]}"></i>`;
                    state.rolling = false;
                    state.lastRollWasSix = (state.dice === 6);
                    let canMoveAny = state.tokens[p].some(v => v !== 56 && (v === -1 ? state.dice === 6 : v + state.dice <= 56));
                    if (!canMoveAny) setTimeout(nextTurn, 1000);
                    else {
                        renderTokens();
                        if (state.turn === 'red') {
                            setTimeout(() => {
                                let pm = []; state.tokens.red.forEach((v, i) => { if (v !== 56 && (v === -1 ? state.dice === 6 : v + state.dice <= 56)) pm.push(i); });
                                moveToken('red', pm[Math.floor(Math.random()*pm.length)]);
                            }, 800);
                        }
                    }
                }
            }, 60);
        }

        function moveToken(color, idx) {
            playSfx('move');
            let oldPos = state.tokens[color][idx];
            if (oldPos === -1) state.tokens[color][idx] = 0;
            else state.tokens[color][idx] += state.dice;
            
            checkCapture(color, state.tokens[color][idx]);
            if(state.tokens[color][idx] === 56) playSfx('home');
            
            let rolledSix = state.lastRollWasSix;
            state.dice = null;
            renderTokens();

            if (!state.gameEnded) {
                if (rolledSix) {
                    state.timeLeft = 10;
                    document.getElementById('status-text').innerText = color === 'green' ? "Relancez !" : "Le CPU relance !";
                    if (color === 'red') setTimeout(() => rollDice('red'), 1000);
                } else {
                    nextTurn();
                }
            }
        }

        function checkCapture(moverColor, pos) {
            if (pos === -1 || pos >= 51) return;
            let moverOffset = moverColor === 'green' ? 1 : 14;
            let realIndex = (pos + moverOffset) % 52;
            let isSafe = safeIndexes.includes(realIndex);
            if (isSafe) return;
            let opponent = moverColor === 'green' ? 'red' : 'green';
            let oppOffset = opponent === 'green' ? 1 : 14;
            state.tokens[opponent].forEach((oppPos, i) => {
                if (oppPos !== -1 && oppPos < 51) {
                    let oppRealIndex = (oppPos + oppOffset) % 52;
                    if (oppRealIndex === realIndex) { state.tokens[opponent][i] = -1; playSfx('capture'); }
                }
            });
        }

        function nextTurn() {
            if(state.gameEnded) return;
            state.turn = state.turn === 'green' ? 'red' : 'green';
            state.dice = null;
            state.timeLeft = 10;
            state.lastRollWasSix = false;
            document.getElementById('status-text').innerText = state.turn === 'green' ? "Votre tour" : "CPU réfléchit...";
            document.getElementById('box-green').classList.toggle('active-turn', state.turn === 'green');
            document.getElementById('box-red').classList.toggle('active-turn', state.turn === 'red');
            renderTokens();
            if (state.turn === 'red') setTimeout(() => rollDice('red'), 800);
        }

        initBoard(); renderTokens(); updateTimerUI();
    </script>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['username'])) { $_SESSION['username'] = "Joueur"; }
$playerName = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<?php include __DIR__.'/config/pwa.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ludo Royal - Entraînement</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body{background:radial-gradient(ellipse at top,#1e1b4b 0%,#0f172a 55%,#020617 100%);min-height:100vh;overflow:hidden;font-family:'Inter',sans-serif;user-select:none;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:4px 0}
        /* Board */
        .board-wrapper{width:min(92vw,420px);aspect-ratio:1/1;position:relative;padding:5px;background:linear-gradient(135deg,#ffd700 0%,#f97316 35%,#ffd700 65%,#eab308 100%);border-radius:18px;box-shadow:0 25px 60px rgba(0,0,0,.75),0 0 40px rgba(255,215,0,.18)}
        .board-inner{width:100%;height:100%;border-radius:13px;overflow:hidden;position:relative}
        .grid-15{display:grid;grid-template-columns:repeat(15,1fr);grid-template-rows:repeat(15,1fr);width:100%;height:100%;gap:1.5px;background:#64748b}
        .cell{background:#fefce8;position:relative;display:flex;justify-content:center;align-items:center}
        .bg-g{background:linear-gradient(145deg,#4ade80,#15803d)!important}
        .bg-r{background:linear-gradient(145deg,#f87171,#b91c1c)!important}
        .bg-b{background:linear-gradient(145deg,#60a5fa,#1d4ed8)!important}
        .bg-y{background:linear-gradient(145deg,#fde047,#a16207)!important}
        .safe-cell{background:linear-gradient(135deg,#fffbeb,#fef3c7)!important}
        .star-icon{font-size:11px;z-index:5;color:#d97706;filter:drop-shadow(0 0 3px rgba(217,119,6,.7))}
        @media(min-width:380px){.star-icon{font-size:14px}}
        .cell.bg-g .star-icon,.cell.bg-r .star-icon,.cell.bg-b .star-icon,.cell.bg-y .star-icon{color:rgba(255,255,255,.95)!important;filter:drop-shadow(0 0 4px rgba(255,255,255,.6))}
        /* Tokens */
        .token{width:6.66%;height:6.66%;position:absolute;z-index:100;display:flex;justify-content:center;align-items:center;transition:all .35s cubic-bezier(.175,.885,.32,1.275)}
        .pawn{width:85%;height:85%;border-radius:50%}
        .active-glow{animation:bounce .75s infinite;cursor:pointer;pointer-events:auto!important;border:2.5px solid #fbbf24;z-index:101;filter:drop-shadow(0 0 6px #f59e0b)}
        @keyframes bounce{0%,100%{transform:scale(1.1)}50%{transform:scale(1.35) translateY(-5px)}}
        /* Panels */
        .glass-ui{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:12px}
        .timer-bar{height:3px;border-radius:2px;transition:width 1s linear}
        /* Panel Dice */
        .pdice{width:44px;height:44px;min-width:44px;background:linear-gradient(145deg,#fff,#e2e8f0);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 3px 10px rgba(0,0,0,.45);opacity:.3;transition:all .25s;cursor:default}
        .pdice.myturn{opacity:1;cursor:pointer;animation:dice-pulse 1.3s ease-in-out infinite}
        @keyframes dice-pulse{0%,100%{transform:scale(1);box-shadow:0 3px 10px rgba(0,0,0,.4),0 0 12px var(--dc)}50%{transform:scale(1.08);box-shadow:0 5px 18px rgba(0,0,0,.5),0 0 22px var(--dc)}}
        /* Overlays */
        .overlay{display:none;position:fixed;inset:0;background:rgba(8,8,28,.96);z-index:2000;flex-direction:column;align-items:center;justify-content:center;backdrop-filter:blur(14px)}
        .hidden{display:none!important}
        .cfg-btn{cursor:pointer;transition:transform .2s;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:12px;color:#fff;font-weight:900}
        .cfg-btn:hover{transform:scale(1.06)}
        .cfg-btn.sel{outline:3px solid #f59e0b;background:rgba(245,158,11,.15)}
        /* Center star */
        .center-home{grid-row:7/10;grid-column:7/10;position:relative;background:linear-gradient(135deg,#fefce8,#fff8dc)}
        .center-home::after{content:'★';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:20px;color:rgba(255,255,255,.9);z-index:10;text-shadow:0 0 10px rgba(255,200,0,.8);pointer-events:none}
        .tri{position:absolute;width:100%;height:100%;clip-path:polygon(50% 50%,0 0,100% 0)}
        .tri-g{background:linear-gradient(135deg,#4ade80,#15803d);transform:rotate(-90deg)}
        .tri-r{background:linear-gradient(135deg,#f87171,#b91c1c)}
        .tri-b{background:linear-gradient(135deg,#60a5fa,#1d4ed8);transform:rotate(90deg)}
        .tri-y{background:linear-gradient(135deg,#fde047,#a16207);transform:rotate(180deg)}
    </style>
</head>
<body>

<!-- CONFIG OVERLAY -->
<div id="cfg" class="overlay" style="display:flex">
  <div class="text-center px-6 w-full max-w-xs">
    <div class="text-6xl mb-3">🎲</div>
    <h1 class="text-3xl font-black text-white mb-1">LUDO ROYAL</h1>
    <p class="text-gray-400 text-xs mb-7 uppercase tracking-widest">Mode Entraînement</p>
    <div id="s1">
      <p class="text-white text-xs font-bold uppercase tracking-widest mb-4">Nombre de joueurs</p>
      <div class="flex justify-center gap-3 mb-2">
        <button onclick="pickTotal(2)" id="t2" class="cfg-btn text-2xl w-16 h-16">2</button>
        <button onclick="pickTotal(3)" id="t3" class="cfg-btn text-2xl w-16 h-16">3</button>
        <button onclick="pickTotal(4)" id="t4" class="cfg-btn text-2xl w-16 h-16">4</button>
      </div>
    </div>
    <div id="s2" class="hidden">
      <p class="text-white text-xs font-bold uppercase tracking-widest mb-1">Joueurs humains</p>
      <p class="text-gray-500 text-xs mb-4">Le reste sera géré par l'IA</p>
      <div id="hbtns" class="flex justify-center gap-3 mb-5"></div>
      <div id="preview" class="space-y-1 mb-6 text-xs text-gray-400"></div>
      <button onclick="startGame()" class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-black py-3 px-10 rounded-full shadow-xl text-sm uppercase tracking-wider">JOUER</button>
      <button onclick="backS1()" class="block mx-auto mt-3 text-gray-600 text-xs underline">← Retour</button>
    </div>
  </div>
</div>

<!-- WIN OVERLAY -->
<div id="win" class="overlay">
  <div class="text-center px-6">
    <div class="text-8xl mb-4">👑</div>
    <h1 id="wmsg" class="text-5xl font-black uppercase mb-2">VICTOIRE !</h1>
    <p id="wsub" class="text-gray-300 mb-8 font-bold text-lg"></p>
    <button onclick="location.reload()" class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-black py-4 px-12 rounded-full shadow-xl text-base uppercase mb-3">REJOUER</button>
    <br>
    <button onclick="goMenu()" class="bg-white/10 border border-white/25 text-white font-bold py-3 px-10 rounded-full text-sm uppercase tracking-wider hover:bg-white/20 transition-all">← Menu principal</button>
  </div>
</div>

<!-- TOP PANELS -->
<div id="ptop" class="w-full flex gap-2 px-3 mb-2" style="max-width:430px"></div>

<!-- STATUS -->
<div class="mb-1 text-center">
  <span id="stxt" class="text-white/60 text-[10px] font-black uppercase tracking-[.25em]">—</span>
</div>

<!-- BOARD -->
<div class="board-wrapper">
  <div class="board-inner">
    <div class="grid-15" id="ludo-grid"></div>
    <div id="token-layer" class="absolute inset-0 pointer-events-none"></div>
  </div>
</div>

<!-- BOTTOM PANELS -->
<div id="pbot" class="w-full flex gap-2 px-3 mt-2 hidden" style="max-width:430px"></div>

<script>
const PNAME="<?= $playerName ?>";
const COLORS=['green','red','blue','yellow'];
const PC={green:'#22c55e',red:'#ef4444',yellow:'#facc15',blue:'#3b82f6'};
const PL={green:'#86efac',red:'#fca5a5',yellow:'#fef08a',blue:'#93c5fd'};
const PD={green:'#14532d',red:'#7f1d1d',yellow:'#713f12',blue:'#1e3a8a'};
const LABEL={green:'Vert',red:'Rouge',yellow:'Jaune',blue:'Bleu'};
const OFFSETS={green:1,red:14,yellow:40,blue:27};
const BPOS={
    green:[{r:2,c:2},{r:2,c:5},{r:5,c:2},{r:5,c:5}],
    red:[{r:2,c:11},{r:2,c:14},{r:5,c:11},{r:5,c:14}],
    yellow:[{r:11,c:2},{r:11,c:5},{r:14,c:2},{r:14,c:5}],
    blue:[{r:11,c:11},{r:11,c:14},{r:14,c:11},{r:14,c:14}]
};
const HOME={
    green:[{r:8,c:2},{r:8,c:3},{r:8,c:4},{r:8,c:5},{r:8,c:6},{r:8,c:7}],
    red:[{r:2,c:8},{r:3,c:8},{r:4,c:8},{r:5,c:8},{r:6,c:8},{r:7,c:8}],
    yellow:[{r:14,c:8},{r:13,c:8},{r:12,c:8},{r:11,c:8},{r:10,c:8},{r:9,c:8}],
    blue:[{r:8,c:14},{r:8,c:13},{r:8,c:12},{r:8,c:11},{r:8,c:10},{r:8,c:9}]
};
const PATH=[{r:7,c:1},{r:7,c:2},{r:7,c:3},{r:7,c:4},{r:7,c:5},{r:7,c:6},{r:6,c:7},{r:5,c:7},{r:4,c:7},{r:3,c:7},{r:2,c:7},{r:1,c:7},{r:1,c:8},{r:1,c:9},{r:2,c:9},{r:3,c:9},{r:4,c:9},{r:5,c:9},{r:6,c:9},{r:7,c:10},{r:7,c:11},{r:7,c:12},{r:7,c:13},{r:7,c:14},{r:7,c:15},{r:8,c:15},{r:9,c:15},{r:9,c:14},{r:9,c:13},{r:9,c:12},{r:9,c:11},{r:9,c:10},{r:10,c:9},{r:11,c:9},{r:12,c:9},{r:13,c:9},{r:14,c:9},{r:15,c:9},{r:15,c:8},{r:15,c:7},{r:14,c:7},{r:13,c:7},{r:12,c:7},{r:11,c:7},{r:10,c:7},{r:9,c:6},{r:9,c:5},{r:9,c:4},{r:9,c:3},{r:9,c:2},{r:9,c:1},{r:8,c:1}];
const SAFE=[1,9,14,22,27,35,40,48];
const SFX={dice:new Audio('sounds/dice.mp3'),move:new Audio('sounds/move.mp3'),capture:new Audio('sounds/capture.mp3'),home:new Audio('sounds/home.mp3'),win:new Audio('https://cdn.pixabay.com/audio/2021/08/04/audio_bb313337f7.mp3')};
const sfx=n=>{try{SFX[n].currentTime=0;SFX[n].play().catch(()=>{})}catch(e){}};
const ICONS=['one','two','three','four','five','six'];

let active=[],humans=[],selTotal=null,selHuman=null;
let state={turn:'green',dice:null,rolling:false,tokens:{},timeLeft:10,wasSix:false,over:false,missedTurns:{}};

// ── CONFIG ──
function pickTotal(n){
    selTotal=n;selHuman=null;
    ['t2','t3','t4'].forEach(id=>document.getElementById(id).classList.remove('sel'));
    document.getElementById('t'+n).classList.add('sel');
    const btns=document.getElementById('hbtns');btns.innerHTML='';
    for(let h=1;h<=n;h++){
        const b=document.createElement('button');
        b.className='cfg-btn text-xl w-14 h-14';b.textContent=h;
        b.onclick=()=>pickHuman(h,n,b);btns.appendChild(b);
    }
    document.getElementById('s1').classList.add('hidden');
    document.getElementById('s2').classList.remove('hidden');
}
function pickHuman(h,total,btn){
    selHuman=h;
    document.querySelectorAll('#hbtns button').forEach(b=>b.classList.remove('sel'));
    btn.classList.add('sel');
    let html='';
    for(let i=0;i<total;i++){
        const col=COLORS[i],isH=i<h;
        const nm=isH?(i===0?PNAME:`Humain ${i+1}`):'🤖 CPU';
        html+=`<div class="flex items-center justify-center gap-2"><span style="width:10px;height:10px;border-radius:50%;background:${PC[col]};display:inline-block"></span><span>${nm} <span style="color:${PC[col]}">(${LABEL[col]})</span></span></div>`;
    }
    document.getElementById('preview').innerHTML=html;
}
function backS1(){document.getElementById('s1').classList.remove('hidden');document.getElementById('s2').classList.add('hidden');}
function startGame(){
    if(!selTotal||!selHuman)return;
    active=COLORS.slice(0,selTotal);humans=active.slice(0,selHuman);
    state.tokens={};active.forEach(c=>{state.tokens[c]=[-1,-1,-1,-1];});
    state.missedTurns={};active.forEach(c=>{state.missedTurns[c]=0;});
    state.turn='green';state.dice=null;state.rolling=false;state.timeLeft=10;state.wasSix=false;state.over=false;
    buildPanels();initBoard();
    document.getElementById('cfg').style.display='none';
    renderTokens();updateUI();
    if(!isHuman('green'))setTimeout(()=>rollDice('green'),900);
}
const isHuman=c=>humans.includes(c);

// ── PANELS (each with its own dice) ──
function buildPanels(){
    const top=document.getElementById('ptop'),bot=document.getElementById('pbot');
    top.innerHTML='';bot.innerHTML='';
    const DORDER=['green','red','yellow','blue'];
    const displayActive=DORDER.filter(c=>active.includes(c));
    displayActive.forEach((col,i)=>{
        const hIdx=humans.indexOf(col);
        const nm=hIdx>=0?(hIdx===0?PNAME:`Humain ${hIdx+1}`):'CPU';
        const av=hIdx>=0?`https://api.dicebear.com/7.x/avataaars/svg?seed=${nm}`:`https://api.dicebear.com/7.x/bottts/svg?seed=${col}`;
        const p=document.createElement('div');
        p.id='panel-'+col;p.className='glass-ui p-2 flex-1 min-w-0 flex items-center gap-2';
        p.style.borderLeft=`3px solid ${PC[col]}`;
        p.innerHTML=`<img src="${av}" style="width:28px;height:28px;border-radius:50%;background:#fff;outline:2px solid ${PC[col]};flex-shrink:0"/>
            <div style="flex:1;min-width:0">
                <div class="text-white font-bold truncate" style="font-size:10px">${nm}</div>
                <div id="sc-${col}" class="font-mono" style="font-size:9px;color:${PC[col]}">HOME:0/4</div>
                <div style="height:3px;background:#1e293b;border-radius:2px;margin-top:3px;overflow:hidden">
                    <div id="tm-${col}" class="timer-bar" style="width:0%;background:${PC[col]}"></div>
                </div>
            </div>
            <div id="pd-${col}" class="pdice" style="--dc:${PC[col]}55">
                <div id="di-${col}"><i class="fa fa-dice-d6" style="color:#64748b"></i></div>
            </div>`;
        if(i<2)top.appendChild(p);
        else{bot.classList.remove('hidden');bot.appendChild(p);}
    });
}

// ── TIMER ──
setInterval(()=>{
    if(state.rolling||state.over||document.getElementById('cfg').style.display!=='none')return;
    if(!isHuman(state.turn))return;
    state.timeLeft--;
    const p=Math.max(0,(state.timeLeft/10)*100);
    active.forEach(c=>{const el=document.getElementById('tm-'+c);if(el)el.style.width=(c===state.turn?p:0)+'%';});
    if(state.timeLeft<=3&&state.timeLeft>0)document.getElementById('stxt').innerText=`⚠️ ${state.timeLeft}s — jouez !`;
    if(state.timeLeft<0)autoPlayForHuman(state.turn);
},1000);
function autoPlayForHuman(col){
    state.missedTurns[col]=(state.missedTurns[col]||0)+1;
    state.timeLeft=10;
    if(state.missedTurns[col]>5){disqualifyPlayer(col);return;}
    document.getElementById('stxt').innerText=`⏱ Auto-jeu ${LABEL[col]} (${state.missedTurns[col]}/5)`;
    if(!state.dice){
        const roll=Math.floor(Math.random()*6)+1;
        state.dice=roll;state.wasSix=(roll===6);
        const di=document.getElementById('di-'+col);
        if(di)di.innerHTML=`<i class="fa fa-dice-${ICONS[roll-1]}" style="color:${PC[col]}"></i>`;
        const can=state.tokens[col].some((_,i)=>canMoveToken(col,i));
        if(!can){setTimeout(nextTurn,800);}
        else{renderTokens();setTimeout(()=>doCpu(col),600);}
    }else{doCpu(col);}
}
function disqualifyPlayer(col){
    const txt=document.getElementById('stxt');
    txt.innerText=`🚫 ${LABEL[col]} est disqualifié !`;
    const curIdx=active.indexOf(col);
    if(curIdx===-1)return;
    active.splice(curIdx,1);
    const hi=humans.indexOf(col);
    if(hi!==-1)humans.splice(hi,1);
    if(active.length<=1){if(active.length===1)setTimeout(()=>showWin(active[0]),1200);return;}
    state.dice=null;state.wasSix=false;state.timeLeft=10;
    state.turn=active[curIdx%active.length];
    renderTokens();updateUI();
    if(!isHuman(state.turn))setTimeout(()=>rollDice(state.turn),800);
}

// ── UI UPDATE ──
function updateUI(){
    if(!active.length)return;
    const human=isHuman(state.turn);
    document.getElementById('stxt').innerText=human?'▶ VOTRE TOUR':`🤖 ${LABEL[state.turn]} réfléchit...`;
    active.forEach(col=>{
        const pd=document.getElementById('pd-'+col);
        const di=document.getElementById('di-'+col);
        const panel=document.getElementById('panel-'+col);
        if(!pd||!di)return;
        const isCur=state.turn===col;
        const canClick=isCur&&isHuman(col)&&!state.over&&!state.rolling&&!state.dice;
        pd.className='pdice'+(canClick?' myturn':'');
        pd.onclick=canClick?()=>rollDice(col):null;
        if(panel)panel.style.boxShadow=isCur?`0 0 18px ${PC[col]}50,inset 0 0 8px ${PC[col]}15`:'none';
        if(isCur&&state.rolling)di.innerHTML=`<i class="fa fa-spinner fa-spin" style="color:${PC[col]}"></i>`;
        else if(isCur&&state.dice)di.innerHTML=`<i class="fa fa-dice-${ICONS[state.dice-1]}" style="color:${PC[col]}"></i>`;
        else di.innerHTML=`<i class="fa fa-dice-d6" style="color:${isCur?PC[col]:'#475569'}"></i>`;
    });
}

// ── DICE ──
function rollDice(col){
    if(state.over||state.rolling||state.dice||state.turn!==col)return;
    state.rolling=true;sfx('dice');
    const di=document.getElementById('di-'+col);
    let cnt=0,iv=setInterval(()=>{
        const r=Math.floor(Math.random()*6)+1;
        if(di)di.innerHTML=`<i class="fa fa-dice-${ICONS[r-1]}" style="color:${PC[col]}"></i>`;
        if(++cnt>12){
            clearInterval(iv);
            state.dice=Math.floor(Math.random()*6)+1;
            state.rolling=false;state.wasSix=(state.dice===6);
            if(di)di.innerHTML=`<i class="fa fa-dice-${ICONS[state.dice-1]}" style="color:${PC[col]}"></i>`;
            const can=state.tokens[col].some((_,i)=>canMoveToken(col,i));
            if(!can)setTimeout(nextTurn,1000);
            else{renderTokens();if(!isHuman(col))setTimeout(()=>doCpu(col),700);}
        }
    },60);
}
function canMoveToken(col,idx){
    const pos=state.tokens[col][idx];
    if(pos===56)return false;
    const d=state.dice;
    if(pos===-1){if(d!==6)return false;}
    else if(pos+d>56)return false;
    const dest=pos===-1?0:pos+d;
    if(dest>=51)return true;
    const ri=(dest+OFFSETS[col])%52;
    const isStar=SAFE.includes(ri);
    const ownHere=state.tokens[col].filter((v,j)=>j!==idx&&v>=0&&v<51&&(v+OFFSETS[col])%52===ri).length;
    if(!isStar&&ownHere>=1)return false;
    const blocked=active.some(opp=>{
        if(opp===col)return false;
        return state.tokens[opp].filter(v=>v>=0&&v<51&&(v+OFFSETS[opp])%52===ri).length>=2;
    });
    return !blocked;
}
function doCpu(col){
    const m=[];state.tokens[col].forEach((_,i)=>{if(canMoveToken(col,i))m.push(i);});
    if(m.length)moveToken(col,m[Math.floor(Math.random()*m.length)]);
    else nextTurn();
}

// ── MOVE ──
function moveToken(col,idx){
    sfx('move');
    const pos=state.tokens[col][idx];
    state.tokens[col][idx]=(pos===-1)?0:pos+state.dice;
    checkCapture(col,state.tokens[col][idx]);
    if(state.tokens[col][idx]===56)sfx('home');
    const six=state.wasSix;state.dice=null;
    renderTokens();
    if(!state.over){
        if(six){
            state.timeLeft=10;
            document.getElementById('stxt').innerText=isHuman(col)?'🎲 Relancez !':`${LABEL[col]} relance !`;
            if(!isHuman(col))setTimeout(()=>rollDice(col),900);
        }else nextTurn();
    }
}
function checkCapture(mover,pos){
    if(pos<0||pos>=51)return;
    const ri=(pos+OFFSETS[mover])%52;
    if(SAFE.includes(ri))return;
    active.forEach(opp=>{
        if(opp===mover)return;
        const cnt=state.tokens[opp].filter(v=>v>-1&&v<51&&(v+OFFSETS[opp])%52===ri).length;
        if(cnt>=2)return;
        state.tokens[opp].forEach((op,i)=>{
            if(op>-1&&op<51&&(op+OFFSETS[opp])%52===ri){state.tokens[opp][i]=-1;sfx('capture');}
        });
    });
}
function nextTurn(){
    if(state.over)return;
    const idx=active.indexOf(state.turn);
    state.turn=active[(idx+1)%active.length];
    state.dice=null;state.timeLeft=10;state.wasSix=false;
    renderTokens();updateUI();
    if(!isHuman(state.turn))setTimeout(()=>rollDice(state.turn),800);
}

// offsets [dx,dy] en % du plateau pour 1/2/3/4 pions dans la même case
const STACK_OFFSETS=[
    [[0,0]],
    [[-1.5,-1.5],[1.5,1.5]],
    [[-1.5,-1.5],[1.5,-1.5],[0,1.7]],
    [[-1.5,-1.5],[1.5,-1.5],[-1.5,1.5],[1.5,1.5]]
];

// ── RENDER TOKENS ──
function renderTokens(){
    document.getElementById('token-layer').innerHTML='';
    const fin={};active.forEach(c=>{fin[c]=0;});
    // Collecte tous les pions visibles
    const all=[];
    active.forEach(col=>{
        state.tokens[col].forEach((pos,i)=>{
            if(pos===56){fin[col]++;return;}
            let xy;
            if(pos===-1)xy=BPOS[col][i];
            else if(pos>=51&&pos<56)xy=HOME[col][pos-51];
            else xy=PATH[(pos+OFFSETS[col])%52];
            const canMove=!state.over&&state.turn===col&&state.dice&&canMoveToken(col,i)&&isHuman(col);
            all.push({col,i,xy,canMove});
        });
    });
    // Regroupe par case (r,c)
    const groups={};
    all.forEach(t=>{
        const k=`${t.xy.r},${t.xy.c}`;
        (groups[k]=groups[k]||[]).push(t);
    });
    // Rendu avec décalage selon le nombre de pions sur la case
    all.forEach(t=>{
        const k=`${t.xy.r},${t.xy.c}`;
        const grp=groups[k];
        const n=Math.min(grp.length,4);
        const slot=grp.indexOf(t);
        const [dx,dy]=(STACK_OFFSETS[n-1]||STACK_OFFSETS[0])[slot]||[0,0];
        const sz=n>1?'62%':'85%';
        const el=document.createElement('div');
        el.className='token';
        el.style.left=((t.xy.c-1)*6.66+dx)+'%';
        el.style.top=((t.xy.r-1)*6.66+dy)+'%';
        el.innerHTML=`<div class="pawn${t.canMove?' active-glow':''}" style="width:${sz};height:${sz};background:radial-gradient(circle at 30% 22%,rgba(255,255,255,.92) 0%,rgba(255,255,255,.4) 22%,${PC[t.col]} 45%,${PD[t.col]} 100%);box-shadow:0 6px 16px rgba(0,0,0,.7),0 2px 5px rgba(0,0,0,.5),inset 0 -3px 8px rgba(0,0,0,.25),inset 0 3px 6px rgba(255,255,255,.15)"></div>`;
        if(t.canMove)el.onclick=()=>moveToken(t.col,t.i);
        document.getElementById('token-layer').appendChild(el);
    });
    active.forEach(col=>{
        const el=document.getElementById('sc-'+col);
        if(el)el.innerText=`HOME:${fin[col]}/4`;
        if(fin[col]===4&&!state.over)showWin(col);
    });
    updateUI();
}
function showWin(col){
    state.over=true;sfx('win');
    const hIdx=humans.indexOf(col);
    const nm=hIdx>=0?(hIdx===0?PNAME:`Humain ${hIdx+1}`):`CPU (${LABEL[col]})`;
    document.getElementById('wmsg').innerText=hIdx>=0?'VOUS GAGNEZ !':'CPU GAGNE !';
    document.getElementById('wmsg').style.color=PC[col];
    document.getElementById('wsub').innerText=`${nm} a remporté la partie !`;
    document.getElementById('win').style.display='flex';
}
function goMenu(){
    state.over=true;
    document.getElementById('win').style.display='none';
    active=[];humans=[];selTotal=null;selHuman=null;
    state={turn:'green',dice:null,rolling:false,tokens:{},timeLeft:10,wasSix:false,over:false,missedTurns:{}};
    document.getElementById('ptop').innerHTML='';
    document.getElementById('pbot').innerHTML='';
    document.getElementById('pbot').classList.add('hidden');
    document.getElementById('token-layer').innerHTML='';
    document.getElementById('stxt').innerText='—';
    document.getElementById('s1').classList.remove('hidden');
    document.getElementById('s2').classList.add('hidden');
    ['t2','t3','t4'].forEach(id=>document.getElementById(id).classList.remove('sel'));
    document.getElementById('cfg').style.display='flex';
}

// ── BOARD ──
function initBoard(){
    let h='';
    for(let r=1;r<=15;r++){
        for(let c=1;c<=15;c++){
            if(r<=6&&c<=6){if(r===1&&c===1)h+=base('g','#4ade80','#22c55e','#14532d');continue;}
            if(r<=6&&c>=10){if(r===1&&c===10)h+=base('r','#f87171','#ef4444','#7f1d1d');continue;}
            if(r>=10&&c<=6){if(r===10&&c===1)h+=base('y','#fde047','#eab308','#713f12');continue;}
            if(r>=10&&c>=10){if(r===10&&c===10)h+=base('b','#93c5fd','#3b82f6','#1e3a8a');continue;}
            if(r>=7&&r<=9&&c>=7&&c<=9){if(r===7&&c===7)h+=`<div class="center-home" style="grid-row:span 3;grid-column:span 3"><div class="tri tri-g"></div><div class="tri tri-r"></div><div class="tri tri-y"></div><div class="tri tri-b"></div></div>`;continue;}
            let bg='',ic='';
            if(r===7&&c===2){bg='bg-g';ic='<i class="fa fa-star star-icon"></i>';}
            else if(r===2&&c===9){bg='bg-r';ic='<i class="fa fa-star star-icon"></i>';}
            else if(r===9&&c===14){bg='bg-b';ic='<i class="fa fa-star star-icon"></i>';}
            else if(r===14&&c===7){bg='bg-y';ic='<i class="fa fa-star star-icon"></i>';}
            else if((r===7&&c===13)||(r===13&&c===9)||(r===9&&c===3)||(r===3&&c===7)){bg='safe-cell';ic='<i class="fa fa-star star-icon"></i>';}
            else if(r===8&&c>1&&c<7)bg='bg-g';
            else if(c===8&&r>1&&r<7)bg='bg-r';
            else if(r===8&&c>9&&c<15)bg='bg-b';
            else if(c===8&&r>9&&r<15)bg='bg-y';
            h+=`<div class="cell ${bg}" style="grid-row:${r};grid-column:${c}">${ic}</div>`;
        }
    }
    document.getElementById('ludo-grid').innerHTML=h;
}
function base(k,light,mid,dark){
    const dot=`background:rgba(0,0,0,.22);width:28%;height:28%;border-radius:50%;box-shadow:inset 0 2px 4px rgba(0,0,0,.3)`;
    return `<div class="base bg-${k}" style="grid-row:span 6;grid-column:span 6;background:radial-gradient(ellipse at 38% 35%,${light} 0%,${mid} 45%,${dark} 100%);padding:10%">
        <div style="background:rgba(255,255,255,.1);width:100%;height:100%;border-radius:15px;display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;place-items:center;box-shadow:inset 0 2px 18px rgba(0,0,0,.2)">
            <div style="${dot}"></div><div style="${dot}"></div><div style="${dot}"></div><div style="${dot}"></div>
        </div></div>`;
}

// ── TESTS (appeler runTests() dans la console) ──
function runTests(){
    const OK='✅',KO='❌';let p=0,f=0;
    function assert(label,got,exp){
        const ok=got===exp;
        console[ok?'log':'error'](ok?OK:KO,label,ok?'':`→ obtenu:${JSON.stringify(got)} attendu:${JSON.stringify(exp)}`);
        ok?p++:f++;
    }
    const bak={state:JSON.parse(JSON.stringify(state)),active:[...active]};
    active=['green','red','blue','yellow'];

    // 1. Protection étoile: pion seul sur PATH[1] (ri=1, étoile verte)
    state.tokens={green:[0,-1,-1,-1],red:[39,-1,-1,-1],blue:[-1,-1,-1,-1],yellow:[-1,-1,-1,-1]};
    // rouge atterrit à pos=39: ri=(39+14)%52=1 → étoile → ne doit PAS capturer le vert
    checkCapture('red',39);
    assert('Étoile: pion seul protégé',state.tokens.green[0],0);

    // 2. Capture normale (hors étoile)
    state.tokens={green:[2,-1,-1,-1],red:[41,-1,-1,-1],blue:[-1,-1,-1,-1],yellow:[-1,-1,-1,-1]};
    // rouge à pos=41: ri=(41+14)%52=3 → pas étoile → DOIT capturer
    checkCapture('red',41);
    assert('Hors étoile: capture correcte',state.tokens.green[0],-1);

    // 3. Bloc 2 sur étoile: imprenable
    state.tokens={green:[0,0,-1,-1],red:[39,-1,-1,-1],blue:[-1,-1,-1,-1],yellow:[-1,-1,-1,-1]};
    checkCapture('red',39);
    assert('Bloc 2 sur étoile: pion 1 protégé',state.tokens.green[0],0);
    assert('Bloc 2 sur étoile: pion 2 protégé',state.tokens.green[1],0);

    // 4. Bloc 2 hors étoile: cnt>=2 → skippé
    state.tokens={green:[2,2,-1,-1],red:[41,-1,-1,-1],blue:[-1,-1,-1,-1],yellow:[-1,-1,-1,-1]};
    checkCapture('red',41);
    assert('Bloc 2 hors étoile: protégé (cnt>=2)',state.tokens.green[0],2);

    // 5. canMoveToken: interdit d'empiler hors étoile
    state.tokens={green:[2,1,-1,-1],red:[-1,-1,-1,-1],blue:[-1,-1,-1,-1],yellow:[-1,-1,-1,-1]};
    state.dice=1; // pion1 pos=1 +1=2, ri=3 → non-étoile, pion0 déjà là
    assert('canMoveToken: empilement interdit hors étoile',canMoveToken('green',1),false);

    // 6. canMoveToken: autorisé sur étoile
    state.tokens={green:[0,-1,-1,-1],red:[-1,-1,-1,-1],blue:[-1,-1,-1,-1],yellow:[-1,-1,-1,-1]};
    state.dice=6; // pion1 (base) sort → dest=0 (PATH[1], étoile)
    assert('canMoveToken: empilement autorisé sur étoile',canMoveToken('green',1),true);

    // 7. canMoveToken: bloc adverse bloque
    state.tokens={green:[-1,-1,-1,-1],red:[39,39,-1,-1],blue:[-1,-1,-1,-1],yellow:[-1,-1,-1,-1]};
    state.dice=6; // vert sort → dest=0, ri=1 bloqué par 2 rouges
    assert('canMoveToken: bloc adverse bloque',canMoveToken('green',0),false);

    // 8. Cases de départ toutes dans SAFE
    assert('Départ vert (ri=1) dans SAFE',SAFE.includes(OFFSETS.green),true);
    assert('Départ rouge (ri=14) dans SAFE',SAFE.includes(OFFSETS.red),true);
    assert('Départ bleu (ri=27) dans SAFE',SAFE.includes(OFFSETS.blue),true);
    assert('Départ jaune (ri=40) dans SAFE',SAFE.includes(OFFSETS.yellow),true);

    // 9. SAFE correspond exactement aux étoiles du plateau
    const boardStars=[{r:7,c:2},{r:3,c:7},{r:2,c:9},{r:7,c:13},{r:9,c:14},{r:13,c:9},{r:14,c:7},{r:9,c:3}];
    let safeMatch=SAFE.every(idx=>boardStars.some(s=>s.r===PATH[idx].r&&s.c===PATH[idx].c));
    assert('SAFE correspond aux étoiles du plateau',safeMatch,true);

    // 10. Ordre de jeu
    assert('Ordre de jeu: vert→rouge→bleu→jaune',
        COLORS.join(','),'green,red,blue,yellow');

    // Restore
    state=bak.state;active=bak.active;
    console.log(`\n=== Résultat: ${p} ✅ réussis, ${f} ❌ échoués ===`);
}

initBoard();
</script>
</body>
</html>

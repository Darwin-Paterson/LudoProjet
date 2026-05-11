<?php /* Balises PWA — inclure dans chaque <head> */ ?>
<link rel="manifest" href="/ludo/manifest.json">
<meta name="theme-color" content="#f59e0b">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Ludo Royal">
<link rel="apple-touch-icon" href="/ludo/assets/icons/icon-192.svg">
<script>
if('serviceWorker' in navigator){
  window.addEventListener('load',()=>{
    navigator.serviceWorker.register('/ludo/sw.js').catch(()=>{});
  });
}
</script>

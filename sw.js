const CACHE = 'ludo-royal-v2';

self.addEventListener('install', e => {
  // Cache uniquement les assets locaux (pas les CDN)
  e.waitUntil(
    caches.open(CACHE).then(c => c.addAll([
      '/ludo/assets/icons/icon-192.png',
      '/ludo/assets/icons/icon-512.png',
      '/ludo/manifest.json'
    ])).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  if (e.request.method !== 'GET') return;
  const url = new URL(e.request.url);

  // Pages PHP : réseau en priorité, fallback cache
  if (url.pathname.endsWith('.php')) {
    e.respondWith(
      fetch(e.request)
        .then(res => {
          const clone = res.clone();
          caches.open(CACHE).then(c => c.put(e.request, clone));
          return res;
        })
        .catch(() => caches.match(e.request).then(r => r || caches.match('/ludo/index.php')))
    );
    return;
  }

  // Assets statiques locaux : cache en priorité
  if (url.origin === self.location.origin) {
    e.respondWith(
      caches.match(e.request).then(cached => {
        if (cached) return cached;
        return fetch(e.request).then(res => {
          if (res && res.status === 200) {
            caches.open(CACHE).then(c => c.put(e.request, res.clone()));
          }
          return res;
        });
      })
    );
  }
});

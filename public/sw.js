/**
 * CashTimeline — Service Worker
 * Strategy: Network-first for HTML/PHP, Cache-first for static assets.
 */

const CACHE_NAME   = 'cashtimeline-v1';
const STATIC_CACHE = 'cashtimeline-static-v1';

const STATIC_ASSETS = [
    '/cashtimeline/public/assets/css/app.css',
    '/cashtimeline/public/assets/js/app.js',
    '/cashtimeline/public/assets/icons/icon-192.png',
    '/cashtimeline/public/assets/icons/icon-512.png',
];

// ── Install: pre-cache static assets ──────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// ── Activate: delete old caches ───────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(k => k !== CACHE_NAME && k !== STATIC_CACHE)
                    .map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

// ── Fetch ──────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle same-origin requests
    if (url.origin !== location.origin) return;

    // Cache-first for static assets (CSS, JS, images, fonts)
    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Network-first for PHP pages (always try to get fresh data)
    event.respondWith(networkFirst(request));
});

function isStaticAsset(url) {
    return /\.(css|js|png|jpg|jpeg|svg|ico|woff2?|ttf)(\?.*)?$/.test(url.pathname);
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('Offline — static asset not cached.', { status: 503 });
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;
        // Return offline page fallback
        return new Response(`
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Offline — CashTimeline</title>
  <style>
    body{margin:0;font-family:system-ui,sans-serif;background:#0f1117;color:#9ca3af;
         display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;}
    h1{color:#6366f1;font-size:2rem;margin-bottom:.5rem;}
    p{font-size:.95rem;}
  </style>
</head>
<body>
  <div>
    <h1>📶 You're offline</h1>
    <p>CashTimeline needs a connection to load your data.<br>Please check your internet and try again.</p>
  </div>
</body>
</html>
        `, { status: 503, headers: { 'Content-Type': 'text/html' } });
    }
}

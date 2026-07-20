/* StudentMove service worker — offline shell + static asset cache */
const VERSION = 'studentmove-v1';
const PRECACHE = `${VERSION}-precache`;
const RUNTIME = `${VERSION}-runtime`;

const PRECACHE_URLS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/icons/icon.svg',
    '/images/route-placeholder.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(PRECACHE).then((cache) => cache.addAll(PRECACHE_URLS)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.filter((key) => key.startsWith('studentmove-') && key !== PRECACHE && key !== RUNTIME)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

function isNavigation(request) {
    return request.mode === 'navigate' || (request.method === 'GET' && request.headers.get('accept')?.includes('text/html'));
}

function isStaticAsset(url) {
    return /\.(css|js|png|jpg|jpeg|svg|webp|woff2?|ico)(\?|$)/i.test(url.pathname)
        || url.pathname.startsWith('/css/')
        || url.pathname.startsWith('/js/')
        || url.pathname.startsWith('/images/')
        || url.pathname.startsWith('/icons/')
        || url.pathname.startsWith('/build/');
}

function isApi(url) {
    return url.pathname.startsWith('/api/') || url.pathname.includes('/subscription/sslcommerz/');
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;
    if (isApi(url)) return;

    if (isNavigation(request)) {
        event.respondWith(networkFirstPage(request));
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(staleWhileRevalidate(request));
    }
});

async function networkFirstPage(request) {
    const cache = await caches.open(RUNTIME);
    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        const cached = await cache.match(request);
        if (cached) return cached;
        return caches.match('/offline.html');
    }
}

async function staleWhileRevalidate(request) {
    const cache = await caches.open(RUNTIME);
    const cached = await cache.match(request);

    const networkFetch = fetch(request).then((response) => {
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    }).catch(() => null);

    return cached || networkFetch || caches.match('/offline.html');
}

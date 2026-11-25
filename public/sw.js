// Solo se guardan en caché archivos estáticos públicos. Las páginas privadas
// (como /empleados) nunca se almacenan: siempre se piden al servidor, que
// comprueba la sesión. Al cambiar la versión se borra la caché anterior (v2),
// que sí contenía /empleados.
const CACHE_NAME = 'sudcali-v4';
const ASSETS_TO_CACHE = [
    '/js/empleados-db.js',
    '/js/empleados.js'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS_TO_CACHE))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => Promise.all(
            cacheNames
                .filter((cache) => cache !== CACHE_NAME)
                .map((cache) => caches.delete(cache))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (event.request.method !== 'GET' || url.origin !== self.location.origin || !ASSETS_TO_CACHE.includes(url.pathname)) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((response) => response || fetch(event.request))
    );
});

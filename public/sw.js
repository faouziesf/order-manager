// Service Worker pour Order Manager
const CACHE_NAME = 'order-manager-v1';
const OFFLINE_URL = '/offline';

// Fichiers essentiels à mettre en cache
const ESSENTIAL_ASSETS = [
    '/offline',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
];

// Installation du Service Worker
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Service Worker: Cache ouvert');
                return cache.addAll(ESSENTIAL_ASSETS.map(url => {
                    return new Request(url, {
                        cache: 'reload',
                        mode: url.startsWith('http') ? 'cors' : 'same-origin'
                    });
                })).catch(err => {
                    console.warn('Service Worker: Échec de mise en cache de certains assets', err);
                });
            })
    );
    self.skipWaiting();
});

// Activation du Service Worker
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Service Worker: Suppression ancien cache', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Interception des requêtes
self.addEventListener('fetch', (event) => {
    // Ignorer les requêtes non-GET
    if (event.request.method !== 'GET') {
        return;
    }

    // Ignorer les requêtes vers des domaines externes (sauf fonts/icons)
    const url = new URL(event.request.url);
    if (url.origin !== location.origin && 
        !url.hostname.includes('googleapis.com') && 
        !url.hostname.includes('cloudflare.com') &&
        !url.hostname.includes('cdnjs.com')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Cloner la réponse pour la mettre en cache
                if (response && response.status === 200) {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return response;
            })
            .catch(() => {
                // En cas d'échec, chercher dans le cache
                return caches.match(event.request)
                    .then((response) => {
                        if (response) {
                            return response;
                        }
                        
                        // Si c'est une requête de navigation (page HTML)
                        if (event.request.mode === 'navigate' || 
                            event.request.headers.get('accept').includes('text/html')) {
                            return caches.match(OFFLINE_URL);
                        }
                        
                        // Pour les autres types de requêtes, retourner une erreur
                        return new Response('Offline', {
                            status: 503,
                            statusText: 'Service Unavailable',
                            headers: new Headers({
                                'Content-Type': 'text/plain'
                            })
                        });
                    });
            })
    );
});

// Message pour communiquer avec le client
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

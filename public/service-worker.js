const CACHE_NAME = 'speed-bumps-v4';
const urlsToCache = [
    '/',
    '/manifest.json',
    '/css/app.css',
    '/js/app.js',
    '/icon-192x192.png',
    '/icon-512x512.png',
    // صفحات واردة من الجلسة (مثل /dashboard و /profile) لا تُخزَّن لتجنُّب مشاكل إعادة التوجيه
];

// Install event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                // Cache each URL individually to handle failures gracefully
                const cachePromises = urlsToCache.map(url => {
                    return cache.add(url).catch(err => {
                        console.warn(`Failed to cache ${url}:`, err);
                        // Continue with other URLs even if one fails
                    });
                });
                return Promise.all(cachePromises);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - Network first, then cache
function isRedirectResponse(res) {
    if(!res) return false;
    // opaque redirect or explicitly redirected
    if(res.type === 'opaqueredirect' || res.redirected === true) return true;
    // also treat 3xx HTTP statuses as redirects when cached
    if(res.status >= 300 && res.status < 400) return true;
    return false;
}

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // API requests: network-first, no cache of error HTML
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(event.request, { redirect: 'follow' })
                .catch(() => caches.match(event.request).then(cached => cached || new Response('Offline', {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: new Headers({ 'Content-Type': 'text/plain' })
                })))
        );
        return;
    }

    // Static assets: cache-first
    if (url.origin === location.origin && (
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.svg') ||
        url.pathname.endsWith('.ico') ||
        url.pathname === '/'
    )) {
        event.respondWith(
            caches.match(event.request)
                .then(response => {
                    // don't serve cached redirect responses
                    if (response && isRedirectResponse(response)) response = null;
                    return response || fetch(event.request, { redirect: 'follow' }).then(res => {
                        // only cache successful, non-redirect responses
                        if (res && res.status === 200 && !isRedirectResponse(res)) {
                            const copy = res.clone();
                            caches.open(CACHE_NAME).then(c => c.put(event.request, copy));
                        }
                        return res;
                    }).catch(() => response);
                })
        );
        return;
    }

    // Default dynamic behavior: network first, then cache
    // Special handling for navigations (HTML pages)
    if (event.request.mode === 'navigate' || (event.request.headers.get('accept') || '').includes('text/html')) {
        event.respondWith(
            fetch(event.request, { redirect: 'follow' })
                .then(response => {
                    // If server returned a redirect (e.g. language switch redirect), do NOT forward a cached redirect to the client
                    if (isRedirectResponse(response) || (response && response.status >= 400)) {
                        return caches.match('/').then(fallback => fallback || new Response('Offline', {
                            status: response && response.status ? response.status : 503,
                            statusText: response && response.statusText ? response.statusText : 'Service Unavailable',
                            headers: new Headers({ 'Content-Type': 'text/plain' })
                        }));
                    }
                    // cache successful HTML responses
                    if (response && response.status === 200 && !isRedirectResponse(response)) {
                        const responseToCache = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, responseToCache));
                    }
                    return response;
                })
                .catch(() => caches.match('/').then(fallback => fallback || new Response('Offline', {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: new Headers({ 'Content-Type': 'text/plain' })
                })))
        );
        return;
    }

    // Default dynamic behavior: network first, then cache
    event.respondWith(
        fetch(event.request, { redirect: 'follow' })
            .then(response => {
                if (response && response.status === 200 && !isRedirectResponse(response)) {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return response;
            })
            .catch(() => caches.match(event.request).then(cached => {
                // Don't return a cached redirect response; prefer a safe cached '/' or an offline response
                if (cached && isRedirectResponse(cached)) {
                    return caches.match('/').then(fallback => fallback || new Response('Offline', {
                        status: 503,
                        statusText: 'Service Unavailable',
                        headers: new Headers({ 'Content-Type': 'text/plain' })
                    }));
                }
                return cached || new Response('Offline', {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: new Headers({ 'Content-Type': 'text/plain' })
                });
            }))
    );
});

// Background sync for offline data and bump predictions
self.addEventListener('sync', event => {
    console.log('🔄 Background sync triggered:', event.tag);
    
    if (event.tag === 'sync-events') {
        event.waitUntil(syncEvents());
    }
    
    if (event.tag === 'sync-bump-predictions') {
        event.waitUntil(syncBumpPredictions());
    }
});

async function syncBumpPredictions() {
    try {
        const cache = await caches.open(CACHE_NAME);
        const requests = await cache.keys();

        for (const request of requests) {
            if (request.url.includes('/api/predictions')) {
                const response = await cache.match(request);
                if (response) {
                    try {
                        await fetch(request, {
                            method: 'POST',
                            body: await response.text()
                        });
                        await cache.delete(request);
                    } catch (error) {
                        console.warn('Failed to sync prediction:', error);
                    }
                }
            }
        }
    } catch (error) {
        console.error('Background sync error:', error);
    }
}

async function syncEvents() {
    try {
        const db = await openIndexedDB();
        const events = await getAllEvents(db);
        
        for (const event of events) {
            await fetch('/api/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(event)
            });
        }
        
        await clearEvents(db);
    } catch (error) {
        console.error('Sync failed:', error);
    }
}

// Push notifications - Advanced with iOS support
self.addEventListener('push', event => {
    console.log('📲 Push notification received');
    
    let notificationData = {
        title: 'تطبيق المطبات الذكي',
        body: 'لديك تنبيه جديد',
        icon: '/icon-192x192.png',
        badge: '/icon-72x72.png',
        tag: 'speed-bumps-notification',
        requireInteraction: true,
        vibrate: [200, 100, 200]
    };

    if (event.data) {
        try {
            notificationData = {
                ...notificationData,
                ...event.data.json()
            };
        } catch (error) {
            notificationData.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, {
            body: notificationData.body,
            icon: notificationData.icon,
            badge: notificationData.badge,
            tag: notificationData.tag,
            requireInteraction: notificationData.requireInteraction,
            vibrate: notificationData.vibrate,
            data: {
                url: notificationData.url || '/',
                action: notificationData.action || 'open'
            },
            actions: [
                {
                    action: 'open',
                    title: 'فتح',
                    icon: '/icon-72x72.png'
                },
                {
                    action: 'close',
                    title: 'إغلاق',
                    icon: '/icon-72x72.png'
                }
            ]
        })
    );
});

// Notification click - Advanced with iOS support
self.addEventListener('notificationclick', event => {
    console.log('👆 Notification clicked:', event.action);
    
    event.notification.close();

    if (event.action === 'close') {
        return;
    }

    const urlToOpen = event.notification.data.url || '/';

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then((clientList) => {
            // Check if app is already open
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }

            // Open new window if not already open
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Notification close event
self.addEventListener('notificationclose', (event) => {
    console.log('❌ Notification closed');
});

// IndexedDB helpers
function openIndexedDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('SpeedBumpsDB', 1);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('events')) {
                db.createObjectStore('events', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

function getAllEvents(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['events'], 'readonly');
        const store = transaction.objectStore('events');
        const request = store.getAll();
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function clearEvents(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['events'], 'readwrite');
        const store = transaction.objectStore('events');
        const request = store.clear();
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}

// Message event for communication with clients
self.addEventListener('message', (event) => {
    console.log('💬 Service Worker received message:', event.data);

    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'SEND_NOTIFICATION') {
        const { title, options } = event.data;
        self.registration.showNotification(title, options);
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.delete(CACHE_NAME).then(() => {
            event.ports[0].postMessage({ success: true });
        });
    }

    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    }
});

// Periodic background sync for continuous monitoring
self.addEventListener('periodicsync', (event) => {
    console.log('⏰ Periodic sync triggered:', event.tag);

    if (event.tag === 'check-bumps') {
        event.waitUntil(checkBumpsInBackground());
    }
});

// Check bumps in background
async function checkBumpsInBackground() {
    try {
        const response = await fetch('/api/nearby-bumps', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();

            if (data.bumps && data.bumps.length > 0) {
                const nearestBump = data.bumps[0];
                await self.registration.showNotification('⚠️ مطب قريب', {
                    body: `مطب على بعد ${nearestBump.distance}م`,
                    icon: '/icon-192x192.png',
                    badge: '/icon-72x72.png',
                    tag: 'bump-alert',
                    requireInteraction: true,
                    vibrate: [200, 100, 200],
                    data: {
                        url: '/bumps/map',
                        action: 'view-map'
                    }
                });
            }
        }
    } catch (error) {
        console.warn('Background bump check error:', error);
    }
}

// Online/Offline events
self.addEventListener('online', () => {
    console.log('🟢 Service Worker online');
    self.clients.matchAll().then((clients) => {
        clients.forEach((client) => {
            client.postMessage({
                type: 'ONLINE_STATUS',
                online: true
            });
        });
    });
});

self.addEventListener('offline', () => {
    console.log('🔴 Service Worker offline');
    self.clients.matchAll().then((clients) => {
        clients.forEach((client) => {
            client.postMessage({
                type: 'ONLINE_STATUS',
                online: false
            });
        });
    });
});

console.log('✅ Service Worker loaded successfully');
console.log('📦 Cache version:', CACHE_NAME);

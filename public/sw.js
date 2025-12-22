const CACHE_NAME = "kopontren-kasir-v1";
const urlsToCache = ["/", "/css/app.css", "/js/app.js", "/offline"];

// Install service worker
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log("Opened cache");
            // Cache essential files individually to avoid failures
            const cachePromises = urlsToCache.map(url => 
                cache.add(url).catch(err => {
                    console.warn(`Failed to cache ${url}:`, err);
                    return Promise.resolve(); // Continue even if one fails
                })
            );
            return Promise.all(cachePromises);
        })
    );
    self.skipWaiting();
});

// Fetch event - Network First, falling back to cache
self.addEventListener("fetch", (event) => {
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Clone the response
                const responseToCache = response.clone();

                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseToCache);
                });

                return response;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request).then((response) => {
                    if (response) {
                        return response;
                    }

                    // If not in cache and network failed, show offline page
                    if (event.request.destination === "document") {
                        return caches.match("/offline");
                    }
                });
            })
    );
});

// Activate event - Clean up old caches
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log("Deleting old cache:", cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Background sync for offline transactions
self.addEventListener("sync", (event) => {
    if (event.tag === "sync-transactions") {
        event.waitUntil(syncTransactions());
    }
});

async function syncTransactions() {
    // This will be called when online connection is restored
    const allClients = await clients.matchAll();
    allClients.forEach((client) => {
        client.postMessage({
            type: "SYNC_TRANSACTIONS",
        });
    });
}

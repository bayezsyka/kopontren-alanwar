/**
 * Service Worker for PWA
 * Handles caching strategies and offline functionality
 */

const CACHE_NAME = "kasir-v1";
const STATIC_CACHE = "kasir-static-v1";
const API_CACHE = "kasir-api-v1";

// Static assets to cache on install
const STATIC_ASSETS = [
    "/",
    "/login",
    "/offline",
    "/build/assets/app.css",
    "/build/assets/app.js",
];

// Install event - cache static assets
self.addEventListener("install", (event) => {
    console.log("[SW] Installing...");
    event.waitUntil(
        caches
            .open(STATIC_CACHE)
            .then((cache) => {
                console.log("[SW] Caching static assets");
                return cache.addAll(STATIC_ASSETS).catch((err) => {
                    console.error("[SW] Failed to cache some assets:", err);
                });
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener("activate", (event) => {
    console.log("[SW] Activating...");
    event.waitUntil(
        caches
            .keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cache) => {
                        if (cache !== STATIC_CACHE && cache !== API_CACHE) {
                            console.log("[SW] Deleting old cache:", cache);
                            return caches.delete(cache);
                        }
                    })
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch event - handle requests
self.addEventListener("fetch", (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests (POST, PUT, DELETE cannot be cached)
    if (request.method !== "GET") {
        return;
    }

    // API requests - Network first, cache fallback (only GET)
    if (url.pathname.startsWith("/api/")) {
        event.respondWith(networkFirstStrategy(request));
        return;
    }

    // Navigation requests - Network first, offline page fallback
    if (request.mode === "navigate") {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match("/offline");
            })
        );
        return;
    }

    // Static assets - Cache first, network fallback
    event.respondWith(cacheFirstStrategy(request));
});

/**
 * Cache first strategy - for static assets
 */
async function cacheFirstStrategy(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error("[SW] Fetch failed:", error);
        throw error;
    }
}

/**
 * Network first strategy - for API requests
 */
async function networkFirstStrategy(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(API_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        throw error;
    }
}

// Handle messages from clients
self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

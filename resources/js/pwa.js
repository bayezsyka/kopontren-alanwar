/**
 * PWA Helper - Service Worker registration and management
 */

if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("/sw.js")
            .then((registration) => {
                console.log(
                    "✅ Service Worker registered:",
                    registration.scope
                );

                // Check for updates
                registration.addEventListener("updatefound", () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener("statechange", () => {
                        if (
                            newWorker.state === "installed" &&
                            navigator.serviceWorker.controller
                        ) {
                            // New service worker available
                            if (
                                confirm(
                                    "Ada pembaruan aplikasi. Muat ulang sekarang?"
                                )
                            ) {
                                newWorker.postMessage({ type: "SKIP_WAITING" });
                                window.location.reload();
                            }
                        }
                    });
                });
            })
            .catch((error) => {
                console.error("❌ Service Worker registration failed:", error);
            });

        // Handle service worker updates
        let refreshing = false;
        navigator.serviceWorker.addEventListener("controllerchange", () => {
            if (!refreshing) {
                refreshing = true;
                window.location.reload();
            }
        });
    });
}

/**
 * PWA Install Prompt
 */
let deferredPrompt;

window.addEventListener("beforeinstallprompt", (e) => {
    // Prevent the mini-infobar from appearing
    e.preventDefault();
    // Stash the event so it can be triggered later
    deferredPrompt = e;

    // Show install button/banner
    const installBanner = document.getElementById("pwa-install-banner");
    if (installBanner) {
        installBanner.classList.remove("hidden");
    }
});

// Install PWA function
window.installPWA = async () => {
    if (!deferredPrompt) {
        return;
    }

    // Show the install prompt
    deferredPrompt.prompt();

    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.userChoice;

    if (outcome === "accepted") {
        console.log("✅ PWA installed");
    } else {
        console.log("❌ PWA installation dismissed");
    }

    // Clear the deferredPrompt
    deferredPrompt = null;

    // Hide install banner
    const installBanner = document.getElementById("pwa-install-banner");
    if (installBanner) {
        installBanner.classList.add("hidden");
    }
};

// Detect if app is running in standalone mode
window.isPWA = () => {
    return (
        window.matchMedia("(display-mode: standalone)").matches ||
        window.navigator.standalone === true
    );
};

// Online/Offline detection
window.addEventListener("online", () => {
    console.log("✅ Online");
    const offlineBanner = document.getElementById("offline-banner");
    if (offlineBanner) {
        offlineBanner.classList.add("hidden");
    }
});

window.addEventListener("offline", () => {
    console.log("❌ Offline");
    const offlineBanner = document.getElementById("offline-banner");
    if (offlineBanner) {
        offlineBanner.classList.remove("hidden");
    }
});

// Check initial online status
document.addEventListener("DOMContentLoaded", () => {
    if (!navigator.onLine) {
        const offlineBanner = document.getElementById("offline-banner");
        if (offlineBanner) {
            offlineBanner.classList.remove("hidden");
        }
    }
});

// PWA Install Handler

class PWAInstaller {
    constructor() {
        this.deferredPrompt = null;
        this.init();
    }

    init() {
        // Register service worker
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker
                .register("/sw.js")
                .then((registration) => {
                    console.log("Service Worker registered:", registration);
                })
                .catch((error) => {
                    console.error("Service Worker registration failed:", error);
                });
        }

        // Listen for install prompt
        window.addEventListener("beforeinstallprompt", (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });

        // Listen for app installed
        window.addEventListener("appinstalled", () => {
            console.log("PWA installed");
            this.hideInstallButton();
            this.showNotification("Aplikasi berhasil diinstall!", "success");
        });

        // Check if already installed
        if (window.matchMedia("(display-mode: standalone)").matches) {
            console.log("App is running in standalone mode");
        }
    }

    async install() {
        if (!this.deferredPrompt) {
            console.log("Install prompt not available");
            return;
        }

        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;

        console.log(`User response: ${outcome}`);
        this.deferredPrompt = null;
        this.hideInstallButton();
    }

    showInstallButton() {
        const installBtn = document.getElementById("pwa-install-btn");
        if (installBtn) {
            installBtn.style.display = "block";
            installBtn.addEventListener("click", () => this.install());
        }
    }

    hideInstallButton() {
        const installBtn = document.getElementById("pwa-install-btn");
        if (installBtn) {
            installBtn.style.display = "none";
        }
    }

    showNotification(message, type = "info") {
        window.dispatchEvent(
            new CustomEvent("pwa-notification", {
                detail: { message, type },
            })
        );
    }
}

// Initialize PWA installer ketika DOM ready
document.addEventListener("DOMContentLoaded", () => {
    window.pwaInstaller = new PWAInstaller();
});

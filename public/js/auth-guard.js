// Auth guard untuk halaman protected
class AuthGuard {
    constructor() {
        this.user = null;
        this.isLoading = true;
    }

    async init() {
        const token = localStorage.getItem("auth_token");

        // Jika di halaman login, skip check
        if (window.location.pathname === "/login") {
            this.isLoading = false;
            return;
        }

        // Jika tidak ada token, redirect ke login
        if (!token) {
            window.location.href = "/login";
            return;
        }

        try {
            // Get user data
            const response = await window.api.getMe();
            this.user = response.user || response;

            // Store user di localStorage
            localStorage.setItem("user", JSON.stringify(this.user));

            // Check role guard
            this.checkRoleAccess();
        } catch (error) {
            console.error("Auth check failed:", error);
            window.location.href = "/login";
        } finally {
            this.isLoading = false;
        }
    }

    checkRoleAccess() {
        const path = window.location.pathname;

        // Owner-only pages
        if (path.startsWith("/owner/")) {
            if (this.user.role !== "owner") {
                showToast(
                    "Akses ditolak. Hanya owner yang bisa mengakses halaman ini.",
                    "error"
                );
                setTimeout(() => {
                    window.location.href = "/pos";
                }, 1500);
            }
        }
    }

    getUser() {
        return this.user;
    }

    isOwner() {
        return this.user && this.user.role === "owner";
    }

    isKasir() {
        return this.user && this.user.role === "kasir";
    }

    getMode() {
        return this.user ? this.user.ui_mode : null;
    }

    async switchMode(mode) {
        try {
            await window.api.setMode(mode);

            // Update user data
            this.user.ui_mode = mode;
            localStorage.setItem("user", JSON.stringify(this.user));

            // Reload page untuk apply mode baru
            showToast("Mode berhasil diubah", "success");
            setTimeout(() => {
                if (mode === "owner") {
                    window.location.href = "/owner/dashboard";
                } else {
                    window.location.href = "/pos";
                }
            }, 500);
        } catch (error) {
            console.error("Failed to switch mode:", error);
            showToast("Gagal mengubah mode", "error");
        }
    }
}

// Global auth guard instance
window.authGuard = new AuthGuard();

// Auto init auth guard
document.addEventListener("DOMContentLoaded", async () => {
    await window.authGuard.init();
});

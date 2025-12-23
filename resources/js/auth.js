window.logout = async () => {
    try {
        await fetch(`${getApiBaseUrl()}/logout`, {
            method: "POST",
            headers: {
                Authorization: `Bearer ${getAuthToken()}`,
                Accept: "application/json",
            },
        });
    } catch (e) {
        console.warn("Logout API gagal, lanjut clear local");
    }

    // Clear local data
    localStorage.removeItem("auth_token");
    localStorage.removeItem("auth_user");
    localStorage.removeItem("pos_cart");
    localStorage.removeItem("pos_held_carts");

    window.location.href = "/login";
};

/**
 * Check if user is authenticated
 */
window.isAuthenticated = () => {
    return !!localStorage.getItem("auth_token");
};

/**
 * Get current user role
 */
window.getUserRole = () => {
    const user = window.getAuthUser();
    return user ? user.role : null;
};

/**
 * Get current user UI mode
 */
window.getUserMode = () => {
    const user = window.getAuthUser();
    return user ? user.ui_mode : null;
};

/**
 * Check if user has specific role
 */
window.hasRole = (role) => {
    return window.getUserRole() === role;
};

/**
 * Check if user is in specific mode
 */
window.isMode = (mode) => {
    return window.getUserMode() === mode;
};

/**
 * Get home URL based on role and mode
 */
window.getHomeUrl = () => {
    const role = window.getUserRole();
    const mode = window.getUserMode();

    if (role === "owner") {
        return mode === "owner" ? "/owner/dashboard" : "/pos";
    }

    return "/pos"; // kasir
};

/**
 * Protect page - redirect to login if not authenticated
 */
window.protectPage = () => {
    if (!window.isAuthenticated()) {
        window.location.href = "/login";
        return false;
    }
    return true;
};

/**
 * Initialize auth check on page load
 */
document.addEventListener("DOMContentLoaded", () => {
    const protectedPages = [
        "/pos",
        "/restock",
        "/items",
        "/stock",
        "/owner",
        "/settings",
    ];
    const currentPath = window.location.pathname;

    // Check if current page needs protection
    const needsProtection = protectedPages.some((page) =>
        currentPath.startsWith(page)
    );

    if (needsProtection && !window.isAuthenticated()) {
        window.location.href = "/login";
    }

    // If on login page and already authenticated, redirect to home
    if (currentPath === "/login" && window.isAuthenticated()) {
        window.location.href = window.getHomeUrl();
    }
});

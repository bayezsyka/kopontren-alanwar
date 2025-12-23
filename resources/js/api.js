/**
 * API Helper - HTTP Client untuk komunikasi dengan backend API
 */

// Get API base URL from meta tag or fallback
const getApiBaseUrl = () => {
    const meta = document.querySelector('meta[name="api-base-url"]');
    return meta ? meta.content : "http://127.0.0.1:8000/api";
};

// Get auth token from localStorage
const getAuthToken = () => {
    return localStorage.getItem("auth_token");
};

// Get current user from localStorage
const getAuthUser = () => {
    const user = localStorage.getItem("auth_user");
    return user ? JSON.parse(user) : null;
};

// Clear auth data and redirect to login
const clearAuthAndRedirect = () => {
    localStorage.removeItem("auth_token");
    localStorage.removeItem("auth_user");
    window.location.href = "/login";
};

/**
 * Main API request function
 */
const apiRequest = async (endpoint, options = {}) => {
    const url = `${getApiBaseUrl()}${endpoint}`;
    const token = getAuthToken();

    const headers = {
        "Content-Type": "application/json",
        Accept: "application/json",
        ...options.headers,
    };

    // Add Authorization header if token exists
    if (token) {
        headers["Authorization"] = `Bearer ${token}`;
    }

    try {
        const response = await fetch(url, {
            ...options,
            headers,
        });

        // Handle 401 Unauthorized
        if (response.status === 401) {
            clearAuthAndRedirect();
            throw new Error("Unauthorized");
        }

        // Parse JSON response
        const data = await response.json();

        // Handle non-2xx responses
        if (!response.ok) {
            throw {
                status: response.status,
                message: data.message || "Request failed",
                errors: data.errors || null,
                data: data,
            };
        }

        return data;
    } catch (error) {
        // Re-throw API errors
        if (error.status) {
            throw error;
        }
        // Network or parsing errors
        throw {
            status: 0,
            message: error.message || "Network error",
            errors: null,
        };
    }
};

/**
 * Convenience methods
 */
window.api = {
    get: (endpoint, params = {}) => {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return apiRequest(url, { method: "GET" });
    },

    post: (endpoint, body = {}) => {
        return apiRequest(endpoint, {
            method: "POST",
            body: JSON.stringify(body),
        });
    },

    put: (endpoint, body = {}) => {
        return apiRequest(endpoint, {
            method: "PUT",
            body: JSON.stringify(body),
        });
    },

    delete: (endpoint) => {
        return apiRequest(endpoint, { method: "DELETE" });
    },

    // Auth helpers
    auth: {
        getUser: getAuthUser,
        getToken: getAuthToken,
        clearAndRedirect: clearAuthAndRedirect,
    },
};

// Make helper functions globally available
window.getApiBaseUrl = getApiBaseUrl;
window.getAuthToken = getAuthToken;
window.getAuthUser = getAuthUser;
window.clearAuthAndRedirect = clearAuthAndRedirect;

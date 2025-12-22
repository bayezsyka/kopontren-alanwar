// API Client dengan auth handling
class ApiClient {
    constructor() {
        this.baseURL = window.location.origin + "/api";
        this.token = localStorage.getItem("auth_token");
    }

    setToken(token) {
        this.token = token;
        localStorage.setItem("auth_token", token);
    }

    clearToken() {
        this.token = null;
        localStorage.removeItem("auth_token");
    }

    getHeaders() {
        const headers = {
            "Content-Type": "application/json",
            Accept: "application/json",
        };

        if (this.token) {
            headers["Authorization"] = `Bearer ${this.token}`;
        }

        return headers;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            ...options,
            headers: {
                ...this.getHeaders(),
                ...options.headers,
            },
        };

        try {
            const response = await fetch(url, config);

            // Handle 401 Unauthorized
            if (response.status === 401) {
                this.clearToken();
                window.location.href = "/login";
                throw new Error("Unauthorized");
            }

            const data = await response.json();

            if (!response.ok) {
                throw {
                    status: response.status,
                    message: data.message || "Request failed",
                    details: data.details || null,
                    data: data,
                };
            }

            return data;
        } catch (error) {
            if (error.message === "Unauthorized") {
                throw error;
            }
            throw error;
        }
    }

    async get(endpoint) {
        return this.request(endpoint, { method: "GET" });
    }

    async post(endpoint, body) {
        return this.request(endpoint, {
            method: "POST",
            body: JSON.stringify(body),
        });
    }

    async put(endpoint, body) {
        return this.request(endpoint, {
            method: "PUT",
            body: JSON.stringify(body),
        });
    }

    async delete(endpoint) {
        return this.request(endpoint, { method: "DELETE" });
    }

    // Auth methods
    async login(email, password) {
        const data = await this.post("/auth/login", {
            email,
            password,
            device_name: navigator.userAgent,
        });

        if (data.token) {
            this.setToken(data.token);
        }

        return data;
    }

    async logout() {
        try {
            await this.post("/auth/logout");
        } finally {
            this.clearToken();
            window.location.href = "/login";
        }
    }

    async getMe() {
        return this.get("/me");
    }

    async setMode(uiMode) {
        return this.post("/me/mode", { ui_mode: uiMode });
    }
}

// Global API client instance
window.api = new ApiClient();

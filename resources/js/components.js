export default (Alpine) => {
    // User Info Dropdown Component (Header)
    Alpine.data("userInfoDropdown", () => ({
        open: false,
        userName: "User",
        userRole: "kasir",
        userMode: "kasir",
        badgeText: "",
        badgeClass: "",
        avatarClass: "",
        initials: "U",

        init() {
            try {
                // Check if getAuthUser is available
                if (typeof window.getAuthUser !== "function") {
                    console.warn("getAuthUser not available yet");
                    // Try to get from localStorage directly as fallback
                    const storedUser = localStorage.getItem("auth_user");
                    if (storedUser) {
                        const user = JSON.parse(storedUser);
                        this.setUser(user);
                    }
                    return;
                }

                const user = window.getAuthUser();
                if (user) {
                    this.setUser(user);
                }
            } catch (e) {
                console.error("UserInfo init error:", e);
            }
        },

        setUser(user) {
            this.userName = user.name || user.email || "User";
            this.userRole = user.role || "kasir";
            this.userMode = user.ui_mode || "kasir";

            // Get initials
            const names = this.userName.split(" ");
            this.initials =
                names.length > 1
                    ? names[0][0] + names[1][0]
                    : this.userName.substring(0, 2);
            this.initials = this.initials.toUpperCase();

            // Set badge based on role and mode
            if (this.userRole === "owner" && this.userMode === "owner") {
                this.badgeText = "👑 Owner";
                this.badgeClass = "bg-purple-100 text-purple-700";
                this.avatarClass = "bg-purple-600";
            } else if (this.userRole === "owner" && this.userMode === "kasir") {
                this.badgeText = "👑 Mode Kasir";
                this.badgeClass = "bg-orange-100 text-orange-700";
                this.avatarClass = "bg-orange-600";
            } else {
                this.badgeText = "👤 Kasir";
                this.badgeClass = "bg-green-100 text-green-700";
                this.avatarClass = "bg-green-600";
            }
        },

        async doLogout() {
            // Use global logout if available (defined in auth.js by user)
            if (typeof window.logout === "function") {
                await window.logout();
                return;
            }

            try {
                if (window.api && window.api.post) {
                    await window.api.post("/auth/logout");
                }
            } catch (error) {
                console.error("Logout error:", error);
            }

            // Clear localStorage
            localStorage.removeItem("auth_token");
            localStorage.removeItem("auth_user");
            localStorage.removeItem("pos_cart");
            localStorage.removeItem("pos_held_carts");

            // Redirect to login
            window.location.href = "/login";
        },
    }));

    // Bottom Navigation Component
    Alpine.data("bottomNav", () => ({
        navHtml: "",

        init() {
            this.renderNav();
        },

        renderNav() {
            if (typeof window.getAuthUser !== "function") return;

            const user = window.getAuthUser();
            if (!user) return;

            const role = user.role;
            const mode = user.ui_mode;

            let navItems = [];
            let gridCols = "grid-cols-5";

            // Check current path
            const currentPath = window.location.pathname;

            if (role === "owner" && mode === "owner") {
                // Owner Mode: Dashboard, Laporan, Barang, Stok, Mode Kasir
                gridCols = "grid-cols-5";
                navItems = [
                    {
                        icon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
                        label: "Dashboard",
                        url: "/owner/dashboard",
                        active: currentPath === "/owner/dashboard",
                    },
                    {
                        icon: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
                        label: "Laporan",
                        url: "/owner/reports",
                        active: currentPath.startsWith("/owner/reports"),
                    },
                    {
                        icon: "M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4",
                        label: "Barang",
                        url: "/items",
                        active: currentPath === "/items",
                    },
                    {
                        icon: "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
                        label: "Stok",
                        url: "/stock",
                        active: currentPath === "/stock",
                    },
                    {
                        icon: "M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z",
                        label: "Kasir",
                        url: "#",
                        click: "switchMode",
                        special: true,
                    },
                ];
            } else if (role === "owner" && mode === "kasir") {
                // Owner in Kasir Mode: POS, Restock, Barang, Stok, Mode Owner
                gridCols = "grid-cols-5";
                navItems = [
                    {
                        icon: "M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z",
                        label: "POS",
                        url: "/pos",
                        active: currentPath === "/pos" || currentPath === "/",
                    },
                    {
                        icon: "M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4",
                        label: "Isi Stok",
                        url: "/restock",
                        active: currentPath === "/restock",
                    },
                    {
                        icon: "M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4",
                        label: "Barang",
                        url: "/items",
                        active: currentPath === "/items",
                    },
                    {
                        icon: "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
                        label: "Stok",
                        url: "/stock",
                        active: currentPath === "/stock",
                    },
                    {
                        icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
                        label: "Owner",
                        url: "#",
                        click: "switchToOwnerMode",
                        special: true,
                    },
                ];
            } else {
                gridCols = "grid-cols-4";
                navItems = [
                    {
                        icon: "M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z",
                        label: "POS",
                        url: "/pos",
                        active: currentPath === "/pos" || currentPath === "/",
                    },
                    {
                        icon: "M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4",
                        label: "Isi Stok",
                        url: "/restock",
                        active: currentPath === "/restock",
                    },
                    {
                        icon: "M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4",
                        label: "Barang",
                        url: "/items",
                        active: currentPath === "/items",
                    },
                    {
                        icon: "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
                        label: "Stok",
                        url: "/stock",
                        active: currentPath === "/stock",
                    },
                ];
            }

            // Update grid layout manually for now as the ID is on the parent
            const navContainer = document.getElementById("bottom-nav");
            if (navContainer) {
                navContainer.className = navContainer.className.replace(
                    /grid-cols-\d+/,
                    gridCols
                );
            }

            this.navHtml = navItems
                .map((item) => {
                    const activeClass = item.active
                        ? "text-white bg-[var(--color-primary)] shadow-lg shadow-green-900/20"
                        : item.special
                        ? "text-orange-600 bg-orange-50 hover:bg-orange-100"
                        : "text-gray-500 hover:bg-gray-50";

                    const clickHandler =
                        item.click === "switchMode"
                            ? 'onclick="switchToKasirMode(); return false;"'
                            : item.click === "switchToOwnerMode"
                            ? 'onclick="switchToOwnerMode(); return false;"'
                            : "";

                    const href =
                        item.url === "#" ? "javascript:void(0)" : item.url;

                    return `
                    <a href="${href}" ${clickHandler} class="flex flex-col items-center justify-center gap-1.5 py-2.5 px-1 rounded-xl transition-all ${activeClass}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${item.icon}"/>
                        </svg>
                        <span class="text-[10px] font-semibold leading-tight text-center">${item.label}</span>
                    </a>
                `;
                })
                .join("");
        },
    }));

    // POS App Component
    Alpine.data("posAppComponent", () => ({
        searchQuery: "",
        searchResults: [],
        quickItems: [],
        cart: [],
        paymentMethod: "cash",
        loading: false,
        stockError: null,

        init() {
            this.loadQuickItems();
            this.loadCart();
        },

        get total() {
            return this.cart.reduce(
                (sum, item) => sum + item.price * item.qty,
                0
            );
        },

        async loadQuickItems() {
            try {
                const response = await api.get("/items", { quick: 1 });
                this.quickItems = response.items || [];
            } catch (error) {
                console.error("Failed to load quick items:", error);
            }
        },

        async searchItems() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            try {
                const response = await api.get("/items", {
                    search: this.searchQuery,
                });
                this.searchResults = (response.items || []).map((item) => ({
                    ...item,
                    stock_display:
                        item.type === "bundle"
                            ? item.stock_computed || 0
                            : item.stock_cached || 0,
                }));
            } catch (error) {
                console.error("Search failed:", error);
            }
        },

        selectFirstItem() {
            if (this.searchResults.length > 0) {
                this.addToCart(this.searchResults[0]);
            }
        },

        addToCart(item) {
            const existingIndex = this.cart.findIndex(
                (c) => c.item_id === item.id
            );

            if (existingIndex >= 0) {
                this.cart[existingIndex].qty++;
            } else {
                this.cart.push({
                    item_id: item.id,
                    name: item.name,
                    price: item.price_sell,
                    qty: 1,
                });
            }

            this.searchQuery = "";
            this.searchResults = [];
            this.saveCart();
            this.$refs.searchInput?.focus();
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
            this.saveCart();
        },

        incrementQty(index) {
            this.cart[index].qty++;
            this.saveCart();
        },

        decrementQty(index) {
            if (this.cart[index].qty > 1) {
                this.cart[index].qty--;
                this.saveCart();
            }
        },

        updateCartItem(index) {
            if (this.cart[index].qty < 1) {
                this.cart[index].qty = 1;
            }
            this.saveCart();
        },

        clearCart() {
            if (confirm("Yakin ingin mengosongkan keranjang?")) {
                this.cart = [];
                this.saveCart();
            }
        },

        saveCart() {
            localStorage.setItem("pos_cart", JSON.stringify(this.cart));
        },

        loadCart() {
            const saved = localStorage.getItem("pos_cart");
            if (saved) {
                this.cart = JSON.parse(saved);
            }
        },

        holdCart() {
            if (this.cart.length === 0) return;

            const held = JSON.parse(
                localStorage.getItem("pos_held_carts") || "[]"
            );
            held.push({
                cart: [...this.cart],
                timestamp: new Date().toISOString(),
            });
            localStorage.setItem("pos_held_carts", JSON.stringify(held));

            this.cart = [];
            this.saveCart();
            showToast("Transaksi di-hold", "success");
        },

        resumeCart() {
            const held = JSON.parse(
                localStorage.getItem("pos_held_carts") || "[]"
            );
            if (held.length === 0) {
                showToast("Tidak ada transaksi yang di-hold", "info");
                return;
            }

            const last = held.pop();
            this.cart = last.cart;
            localStorage.setItem("pos_held_carts", JSON.stringify(held));
            this.saveCart();
            showToast("Transaksi di-resume", "success");
        },

        async checkout() {
            if (this.cart.length === 0) return;

            this.loading = true;

            try {
                const payload = {
                    payment_method: this.paymentMethod,
                    lines: this.cart.map((item) => ({
                        item_id: item.item_id,
                        qty: item.qty,
                        unit_price: item.price,
                    })),
                };

                await api.post("/sales", payload);

                showToast("✅ Transaksi berhasil!", "success");
                this.cart = [];
                this.saveCart();
                this.$refs.searchInput?.focus();
            } catch (error) {
                if (error.status === 422 && error.data?.details) {
                    // Stock error
                    const details = error.data.details;
                    let errorMsg = "";
                    details.forEach((d) => {
                        errorMsg += `<p><strong>${d.item_name}</strong><br>Stok: ${d.available} | Butuh: ${d.required}</p>`;
                    });
                    this.stockError = errorMsg;
                } else {
                    showToast("❌ " + error.message, "error");
                }
            } finally {
                this.loading = false;
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
            }).format(value);
        },
    }));

    // Restock App Component
    Alpine.data("restockAppComponent", () => ({
        searchQuery: "",
        searchResults: [],
        lines: [],
        notes: "",
        loading: false,

        get isValid() {
            return this.lines.length > 0 && this.lines.every((l) => l.qty > 0);
        },

        async searchItems() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            try {
                const response = await api.get("/items", {
                    search: this.searchQuery,
                });
                this.searchResults = response.items || [];
            } catch (error) {
                console.error("Search failed:", error);
            }
        },

        selectFirstItem() {
            if (this.searchResults.length > 0) {
                this.addToLines(this.searchResults[0]);
            }
        },

        addToLines(item) {
            if (item.type === "bundle") {
                if (
                    !confirm(
                        `"${item.name}" adalah bundle. Yakin ingin restock bundle? (Biasanya restock dilakukan pada item normal)`
                    )
                ) {
                    this.searchQuery = "";
                    this.searchResults = [];
                    return;
                }
            }

            const existing = this.lines.find((l) => l.item_id === item.id);
            if (existing) {
                showToast("Item sudah ada di list", "info");
                this.searchQuery = "";
                this.searchResults = [];
                return;
            }

            this.lines.push({
                item_id: item.id,
                item_name: item.name,
                current_stock: item.stock_cached || 0,
                qty: 1,
                unit_cost: null,
            });

            this.searchQuery = "";
            this.searchResults = [];
        },

        removeLine(index) {
            this.lines.splice(index, 1);
        },

        clearLines() {
            if (confirm("Yakin ingin mengosongkan semua item?")) {
                this.lines = [];
                this.notes = "";
            }
        },

        async submitRestock() {
            if (!this.isValid) return;

            this.loading = true;

            try {
                const payload = {
                    notes: this.notes || null,
                    lines: this.lines.map((l) => ({
                        item_id: l.item_id,
                        qty: l.qty,
                        unit_cost: l.unit_cost || null,
                    })),
                };

                await api.post("/purchases", payload);

                showToast("✅ Restock berhasil!", "success");
                this.lines = [];
                this.notes = "";
            } catch (error) {
                showToast("❌ " + error.message, "error");
            } finally {
                this.loading = false;
            }
        },
    }));

    // Items App Component
    Alpine.data("itemsAppComponent", () => ({
        items: [],
        searchQuery: "",
        loading: false,
        showModal: false,
        editingItem: null,
        saving: false,
        form: {
            name: "",
            type: "normal",
            price_sell: 0,
            sku: "",
            barcode: "",
            low_stock_threshold: 5,
            is_quick: false,
            quick_order: 0,
        },

        init() {
            this.loadItems();
        },

        async loadItems() {
            this.loading = true;
            try {
                const params = this.searchQuery
                    ? { search: this.searchQuery }
                    : {};
                const response = await api.get("/items", params);
                this.items = response.items || [];
            } catch (error) {
                showToast("Gagal memuat data: " + error.message, "error");
            } finally {
                this.loading = false;
            }
        },

        openModal() {
            this.resetForm();
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.editingItem = null;
            this.resetForm();
        },

        resetForm() {
            this.form = {
                name: "",
                type: "normal",
                price_sell: 0,
                sku: "",
                barcode: "",
                low_stock_threshold: 5,
                is_quick: false,
                quick_order: 0,
            };
        },

        editItem(item) {
            this.editingItem = item;
            this.form = {
                name: item.name,
                type: item.type,
                price_sell: item.price_sell,
                sku: item.sku || "",
                barcode: item.barcode || "",
                low_stock_threshold: item.low_stock_threshold || 5,
                is_quick: item.is_quick || false,
                quick_order: item.quick_order || 0,
            };
            this.showModal = true;
        },

        async saveItem() {
            this.saving = true;
            try {
                if (this.editingItem) {
                    await api.put(`/items/${this.editingItem.id}`, this.form);
                    showToast("✅ Barang berhasil diupdate", "success");
                } else {
                    await api.post("/items", this.form);
                    showToast("✅ Barang berhasil ditambahkan", "success");
                }
                this.closeModal();
                this.loadItems();
            } catch (error) {
                showToast("❌ " + error.message, "error");
            } finally {
                this.saving = false;
            }
        },

        editBundleComponents(item) {
            window.location.href = `/items/${item.id}/bundle`;
        },

        async deleteItem(item) {
            if (
                !confirm(
                    `Yakin ingin menghapus "${item.name}"?\n\nJika barang ini punya riwayat transaksi, barang akan dinonaktifkan (tidak dihapus permanen).`
                )
            ) {
                return;
            }

            try {
                const response = await api.delete(`/items/${item.id}`);
                showToast(`✅ ${response.message}`, "success");
                this.loadItems();
            } catch (error) {
                showToast("❌ " + error.message, "error");
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
            }).format(value);
        },
    }));

    // Stock App Component
    Alpine.data("stockAppComponent", () => ({
        lowStockItems: [],
        items: [],
        searchQuery: "",
        loading: false,
        showAdjustModal: false,
        adjustingItem: null,
        saving: false,
        adjustForm: {
            new_stock: 0,
            notes: "",
        },

        init() {
            this.loadLowStock();
            this.searchItems();
        },

        async loadLowStock() {
            try {
                const response = await api.get("/stock/low");
                this.lowStockItems = response.items || [];
            } catch (error) {
                console.error("Failed to load low stock:", error);
            }
        },

        async searchItems() {
            this.loading = true;
            try {
                const params = this.searchQuery
                    ? { search: this.searchQuery }
                    : {};
                const response = await api.get("/items", params);
                this.items = response.items || [];
            } catch (error) {
                showToast("Gagal memuat data: " + error.message, "error");
            } finally {
                this.loading = false;
            }
        },

        getStockColor(item) {
            const stock =
                item.type === "bundle"
                    ? item.stock_computed || 0
                    : item.stock_cached || 0;
            const threshold = item.low_stock_threshold || 0;

            if (stock === 0) return "text-red-600";
            if (stock <= threshold) return "text-orange-600";
            return "text-green-600";
        },

        isOwner() {
            const user = getAuthUser();
            // Owner can adjust only in owner mode
            return user && user.role === "owner" && user.ui_mode === "owner";
        },

        openAdjustModal(item) {
            this.adjustingItem = item;
            this.adjustForm.new_stock =
                item.type === "bundle"
                    ? item.stock_computed || 0
                    : item.stock_cached || 0;
            this.adjustForm.notes = "";
            this.showAdjustModal = true;
        },

        closeAdjustModal() {
            this.showAdjustModal = false;
            this.adjustingItem = null;
        },

        async adjustStock() {
            this.saving = true;
            try {
                await api.post("/stock/adjust", {
                    item_id: this.adjustingItem.id,
                    new_stock: this.adjustForm.new_stock,
                    notes: this.adjustForm.notes || null,
                });

                showToast("✅ Stok berhasil di-adjust", "success");
                this.closeAdjustModal();
                this.searchItems();
                this.loadLowStock();
            } catch (error) {
                showToast("❌ " + error.message, "error");
            } finally {
                this.saving = false;
            }
        },
    }));

    // Owner Dashboard Component
    Alpine.data("ownerDashboardComponent", () => ({
        period: "week", // week, month, year
        dateFrom: "",
        dateTo: "",
        periodLabel: "",
        summary: {},
        series: [],
        topItems: [],
        loading: false,

        init() {
            this.setPeriod("week");
        },

        setPeriod(period) {
            this.period = period;
            const today = new Date();

            if (period === "week") {
                // Get Monday of current week
                const day = today.getDay();
                const diff = today.getDate() - day + (day === 0 ? -6 : 1); // Monday
                const monday = new Date(today);
                monday.setDate(diff);

                const sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);

                this.dateFrom = monday.toISOString().split("T")[0];
                this.dateTo = sunday.toISOString().split("T")[0];
                this.periodLabel = `Senin ${this.formatDateShort(
                    monday
                )} - Minggu ${this.formatDateShort(sunday)}`;
            } else if (period === "month") {
                // First day of current month
                const firstDay = new Date(
                    today.getFullYear(),
                    today.getMonth(),
                    1
                );
                const lastDay = new Date(
                    today.getFullYear(),
                    today.getMonth() + 1,
                    0
                );

                this.dateFrom = firstDay.toISOString().split("T")[0];
                this.dateTo = lastDay.toISOString().split("T")[0];

                const monthNames = [
                    "Januari",
                    "Februari",
                    "Maret",
                    "April",
                    "Mei",
                    "Juni",
                    "Juli",
                    "Agustus",
                    "September",
                    "Oktober",
                    "November",
                    "Desember",
                ];
                this.periodLabel = `${
                    monthNames[today.getMonth()]
                } ${today.getFullYear()}`;
            } else if (period === "year") {
                // First day of current year
                const firstDay = new Date(today.getFullYear(), 0, 1);
                const lastDay = new Date(today.getFullYear(), 11, 31);

                this.dateFrom = firstDay.toISOString().split("T")[0];
                this.dateTo = lastDay.toISOString().split("T")[0];
                this.periodLabel = `Tahun ${today.getFullYear()}`;
            }

            this.loadData();
        },

        async loadData() {
            this.loading = true;
            try {
                const params = {
                    from: this.dateFrom,
                    to: this.dateTo,
                };

                const [summaryRes, seriesRes, topItemsRes] = await Promise.all([
                    api.get("/dashboard/summary", params),
                    api.get("/dashboard/series", params),
                    api.get("/dashboard/top-items", { ...params, limit: 10 }),
                ]);

                this.summary = summaryRes || {};
                this.series = seriesRes.series || [];
                this.topItems = topItemsRes.items || [];
            } catch (error) {
                showToast("Gagal memuat data: " + error.message, "error");
            } finally {
                this.loading = false;
            }
        },

        formatDateShort(date) {
            return date.toLocaleDateString("id-ID", {
                day: "numeric",
                month: "short",
            });
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString("id-ID", {
                weekday: "short",
                day: "numeric",
                month: "short",
            });
        },

        formatCurrency(value) {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
            }).format(value);
        },
    }));

    // Reports App Component
    Alpine.data("reportsAppComponent", () => ({
        year: new Date().getFullYear(),
        month: new Date().getMonth() + 1,
        reports: [],
        loading: false,

        init() {
            this.loadReports();
        },

        async loadReports() {
            this.loading = true;
            try {
                const response = await api.get("/reports/weekly", {
                    year: this.year,
                    month: this.month,
                });
                this.reports = response.reports || [];
            } catch (error) {
                showToast("Gagal memuat laporan: " + error.message, "error");
            } finally {
                this.loading = false;
            }
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString("id-ID", {
                weekday: "short",
                day: "numeric",
                month: "short",
                year: "numeric",
            });
        },

        async downloadPdf(reportId) {
            try {
                // Get download URL
                const baseUrl = window.FRONTEND_API_BASE_URL || "/api";
                const token = localStorage.getItem("auth_token");
                const url = `${baseUrl}/reports/weekly/${reportId}/download`;

                // Open in new tab
                window.open(url + `?token=${token}`, "_blank");
            } catch (error) {
                showToast("Gagal mengunduh PDF: " + error.message, "error");
            }
        },
    }));

    // Report Detail Component
    Alpine.data("reportDetailAppComponent", (reportId) => ({
        reportId: reportId,
        report: null,
        loading: false,

        init() {
            this.loadReport();
        },

        async loadReport() {
            this.loading = true;
            try {
                const response = await api.get(`/reports/${this.reportId}`);
                this.report = response.data;
            } catch (error) {
                showToast(
                    "Gagal memuat detail laporan: " + error.message,
                    "error"
                );
            } finally {
                this.loading = false;
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
            }).format(value);
        },
    }));

    // Login Form Component
    Alpine.data("loginForm", () => ({
        form: {
            email: "",
            password: "",
            device_name: "pwa",
        },
        showPassword: false,
        loading: false,
        error: "",

        async login() {
            this.loading = true;
            this.error = "";

            try {
                const response = await api.post("/auth/login", this.form);

                // Save token and user to localStorage
                localStorage.setItem("auth_token", response.token);
                localStorage.setItem(
                    "auth_user",
                    JSON.stringify(response.user)
                );

                // Redirect based on role and mode
                const role = response.user.role;
                const mode = response.user.ui_mode;

                if (role === "owner" && mode === "owner") {
                    window.location.href = "/owner/dashboard";
                } else {
                    window.location.href = "/pos";
                }
            } catch (error) {
                this.error =
                    error.message ||
                    "Login gagal. Periksa email dan password Anda.";
                this.loading = false;
            }
        },
    }));
};

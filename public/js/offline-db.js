// Offline Database untuk Kopontren Kasir
// Menggunakan IndexedDB untuk storage yang persisten (tidak hilang meskipun cache dihapus)

class OfflineDatabase {
    constructor() {
        this.dbName = "kopontren-kasir-db";
        this.dbVersion = 1;
        this.db = null;
    }

    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Store untuk transaksi offline
                if (!db.objectStoreNames.contains("transactions")) {
                    const transactionStore = db.createObjectStore(
                        "transactions",
                        {
                            keyPath: "id",
                            autoIncrement: true,
                        }
                    );
                    transactionStore.createIndex("synced", "synced", {
                        unique: false,
                    });
                    transactionStore.createIndex("created_at", "created_at", {
                        unique: false,
                    });
                }

                // Store untuk cart
                if (!db.objectStoreNames.contains("cart")) {
                    db.createObjectStore("cart", {
                        keyPath: "item_id",
                    });
                }
            };
        });
    }

    // === TRANSACTIONS ===
    async saveTransaction(transaction) {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(["transactions"], "readwrite");
            const store = tx.objectStore("transactions");

            const transactionData = {
                ...transaction,
                synced: false,
                created_at: new Date().toISOString(),
                offline_id: `offline-${Date.now()}-${Math.random()
                    .toString(36)
                    .substr(2, 9)}`,
            };

            const request = store.add(transactionData);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async getUnsyncedTransactions() {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(["transactions"], "readonly");
            const store = tx.objectStore("transactions");
            const index = store.index("synced");

            const results = [];
            const request = index.openCursor();

            request.onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    if (cursor.value.synced === false) {
                        results.push(cursor.value);
                    }
                    cursor.continue();
                } else {
                    resolve(results);
                }
            };

            request.onerror = () => reject(request.error);
        });
    }

    async getAllTransactions() {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(["transactions"], "readonly");
            const store = tx.objectStore("transactions");
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async markTransactionSynced(id) {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(["transactions"], "readwrite");
            const store = tx.objectStore("transactions");
            const getRequest = store.get(id);

            getRequest.onsuccess = () => {
                const transaction = getRequest.result;
                if (transaction) {
                    transaction.synced = true;
                    transaction.synced_at = new Date().toISOString();
                    const updateRequest = store.put(transaction);
                    updateRequest.onsuccess = () => resolve();
                    updateRequest.onerror = () => reject(updateRequest.error);
                } else {
                    resolve();
                }
            };
            getRequest.onerror = () => reject(getRequest.error);
        });
    }

    async deleteTransaction(id) {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(["transactions"], "readwrite");
            const store = tx.objectStore("transactions");
            const request = store.delete(id);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    // === CART ===
    async addToCart(item) {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(["cart"], "readwrite");
            const store = tx.objectStore("cart");
            const request = store.put(item);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async removeFromCart(itemId) {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(["cart"], "readwrite");
            const store = tx.objectStore("cart");
            const request = store.delete(itemId);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async getCart() {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(["cart"], "readonly");
            const store = tx.objectStore("cart");
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async clearCart() {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(["cart"], "readwrite");
            const store = tx.objectStore("cart");
            const request = store.clear();

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async updateCartItemQuantity(itemId, quantity) {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(["cart"], "readwrite");
            const store = tx.objectStore("cart");
            const getRequest = store.get(itemId);

            getRequest.onsuccess = () => {
                const item = getRequest.result;
                if (item) {
                    item.quantity = quantity;
                    const updateRequest = store.put(item);
                    updateRequest.onsuccess = () => resolve();
                    updateRequest.onerror = () => reject(updateRequest.error);
                } else {
                    resolve();
                }
            };
            getRequest.onerror = () => reject(getRequest.error);
        });
    }
}

// Global instance
window.offlineDB = new OfflineDatabase();

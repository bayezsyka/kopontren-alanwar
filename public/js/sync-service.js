// Sync Service - Mensinkronkan transaksi offline ke server

class SyncService {
    constructor() {
        this.isSyncing = false;
        this.syncInterval = null;
        this.API_BASE_URL = window.location.origin + "/api";
    }

    async init() {
        await window.offlineDB.init();

        // Listen untuk online event
        window.addEventListener("online", () => {
            console.log("Connection restored, syncing...");
            this.showNotification(
                "Koneksi kembali! Mensinkronkan data...",
                "info"
            );
            this.syncTransactions();
        });

        // Listen untuk message dari service worker
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.addEventListener("message", (event) => {
                if (event.data.type === "SYNC_TRANSACTIONS") {
                    this.syncTransactions();
                }
            });
        }

        // Auto sync setiap 5 menit jika online
        this.syncInterval = setInterval(() => {
            if (navigator.onLine) {
                this.syncTransactions();
            }
        }, 5 * 60 * 1000);

        // Sync saat pertama load jika online
        if (navigator.onLine) {
            this.syncTransactions();
        }
    }

    async syncTransactions() {
        if (this.isSyncing) {
            console.log("Sync already in progress");
            return;
        }

        try {
            this.isSyncing = true;
            const transactions =
                await window.offlineDB.getUnsyncedTransactions();

            if (transactions.length === 0) {
                console.log("No transactions to sync");
                return;
            }

            console.log(`Syncing ${transactions.length} transactions...`);
            let successCount = 0;
            let errorCount = 0;

            for (const transaction of transactions) {
                try {
                    await this.sendTransactionToServer(transaction);
                    await window.offlineDB.markTransactionSynced(
                        transaction.id
                    );
                    successCount++;
                } catch (error) {
                    console.error("Failed to sync transaction:", error);
                    errorCount++;
                }
            }

            if (successCount > 0) {
                this.showNotification(
                    `Berhasil sinkronisasi ${successCount} transaksi`,
                    "success"
                );
            }

            if (errorCount > 0) {
                this.showNotification(
                    `Gagal sinkronisasi ${errorCount} transaksi`,
                    "error"
                );
            }
        } catch (error) {
            console.error("Sync error:", error);
        } finally {
            this.isSyncing = false;
        }
    }

    async sendTransactionToServer(transaction) {
        const token = localStorage.getItem("auth_token");

        const response = await fetch(`${this.API_BASE_URL}/sales`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: token ? `Bearer ${token}` : "",
            },
            body: JSON.stringify({
                items: transaction.items,
                total: transaction.total,
                payment_method: transaction.payment_method,
                customer_name: transaction.customer_name || null,
                notes: transaction.notes || null,
                offline_created_at: transaction.created_at,
                offline_id: transaction.offline_id,
            }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    showNotification(message, type = "info") {
        // Emit custom event untuk notification
        window.dispatchEvent(
            new CustomEvent("sync-notification", {
                detail: { message, type },
            })
        );

        // Fallback console log
        console.log(`[${type.toUpperCase()}] ${message}`);
    }

    destroy() {
        if (this.syncInterval) {
            clearInterval(this.syncInterval);
        }
    }
}

// Global instance
window.syncService = new SyncService();

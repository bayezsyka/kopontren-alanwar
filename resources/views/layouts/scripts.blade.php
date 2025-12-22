<!-- Base Scripts -->
<script>
    // CSRF Token
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Toast System
    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        
        toast.innerHTML = `
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">${icons[type] || 'ℹ'}</span>
                    <span class="text-sm">${message}</span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" 
                        class="text-gray-500 hover:text-gray-700 text-xl leading-none">
                    ✕
                </button>
            </div>
        `;
        
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    window.showToast = showToast;

    // Currency Formatter
    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    window.formatRupiah = formatRupiah;

    // Number formatter
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    window.formatNumber = formatNumber;

    // Date formatter
    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    window.formatDate = formatDate;

    // Loading Overlay
    function showLoading() {
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(overlay);
        document.body.classList.add('modal-open');
    }

    function hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
            document.body.classList.remove('modal-open');
        }
    }

    window.showLoading = showLoading;
    window.hideLoading = hideLoading;

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    window.debounce = debounce;
</script>

<!-- PWA Scripts -->
<script src="/js/offline-db.js"></script>
<script src="/js/sync-service.js"></script>
<script src="/js/pwa-installer.js"></script>
<script src="/js/api-client.js"></script>
<script src="/js/auth-guard.js"></script>

<!-- Init Sync Service -->
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        await window.syncService.init();
    });
</script>

@vite(['resources/js/app.js'])

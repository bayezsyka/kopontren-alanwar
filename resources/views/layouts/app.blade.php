<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#06b6d4">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Kopontren Kasir">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-152x152.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/icons/icon-72x72.png">
    
    <title>@yield('title', 'Kopontren Kasir')</title>
    
    <!-- Styles -->
    @vite(['resources/css/app.css'])
    
    <style>
        /* Loading States */
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #06b6d4;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Offline Indicator */
        #offline-indicator {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #f59e0b;
            color: white;
            padding: 0.5rem;
            text-align: center;
            display: none;
            z-index: 9999;
            font-size: 0.875rem;
        }

        #offline-indicator.show {
            display: block;
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            min-width: 250px;
            max-width: 400px;
            background: white;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateX(500px);
            transition: transform 0.3s ease;
            z-index: 9999;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            border-left: 4px solid #10b981;
        }

        .toast.error {
            border-left: 4px solid #ef4444;
        }

        .toast.info {
            border-left: 4px solid #3b82f6;
        }

        .toast.warning {
            border-left: 4px solid #f59e0b;
        }

        /* Mobile-first button sizes */
        .btn {
            @apply px-4 py-3 rounded-lg font-semibold transition min-h-[48px] flex items-center justify-center;
        }

        .btn-primary {
            @apply bg-cyan-500 hover:bg-cyan-600 text-white;
        }

        .btn-secondary {
            @apply bg-gray-200 hover:bg-gray-300 text-gray-800;
        }

        .btn-danger {
            @apply bg-red-500 hover:bg-red-600 text-white;
        }

        /* Input styles */
        .input {
            @apply w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent min-h-[48px];
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Offline Indicator -->
    <div id="offline-indicator">
        <span>⚠️ Offline - Data akan disinkronkan saat online kembali</span>
    </div>

    <!-- PWA Install Button -->
    <button id="pwa-install-btn" style="display: none;" 
            class="fixed bottom-20 right-4 bg-cyan-500 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-cyan-600 transition z-50">
        📱 Install App
    </button>

    <!-- Main Content -->
    <div id="app">
        @yield('content')
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Base Scripts -->
    <script>
        // CSRF Token
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Online/Offline Status
        function updateOnlineStatus() {
            const indicator = document.getElementById('offline-indicator');
            if (navigator.onLine) {
                indicator.classList.remove('show');
            } else {
                indicator.classList.add('show');
            }
        }

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();

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
                        <span>${message}</span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            class="text-gray-500 hover:text-gray-700 text-xl">
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
                minimumFractionDigits: 0
            }).format(amount);
        }

        window.formatRupiah = formatRupiah;

        // Number formatter
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        window.formatNumber = formatNumber;
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
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base-url" content="{{ env('FRONTEND_API_BASE_URL', '/api') }}">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#008362">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Kasir">

    <title>@yield('title', 'Kasir') - Al-Anwar</title>

    <!-- PWA Links -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite('resources/css/app.css')

    <!-- Scripts (loaded in head to register Alpine components before body is parsed) -->
    @vite('resources/js/app.js')

    @stack('styles')
</head>
<body class="antialiased">
    <!-- Offline Banner -->
    <div id="offline-banner" class="hidden fixed top-0 left-0 right-0 z-50 bg-red-500 text-white text-center py-2 text-sm font-medium shadow-lg">
        ⚠️ Anda sedang offline
    </div>

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner" class="hidden fixed bottom-20 left-4 right-4 z-40 bg-gradient-to-r from-[var(--color-primary)] to-[var(--color-primary-dark)] text-white rounded-2xl p-4 shadow-2xl">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <h3 class="font-semibold text-sm">Install Aplikasi</h3>
                <p class="text-xs opacity-90 mt-0.5">Akses lebih cepat dari layar utama</p>
            </div>
            <button onclick="installPWA()" class="btn bg-white text-[var(--color-primary)] hover:bg-gray-100 text-sm px-4 py-2">
                Install
            </button>
        </div>
    </div>

    <!-- Header -->
    @if(!isset($hideHeader) || !$hideHeader)
    <header class="sticky top-0 z-30 bg-white border-b border-gray-100 shadow-sm">
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if(isset($showBack) && $showBack)
                <button onclick="history.back()" class="p-2 -ml-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                @endif
                <h1 class="text-lg font-bold text-gray-900">@yield('page-title', 'Kasir')</h1>
            </div>

            <div class="flex items-center gap-2">
                @yield('header-actions')

                <!-- User Info with Dropdown -->
                <div x-data="userInfoDropdown()" x-init="init()" class="relative flex items-center gap-2 ml-2">
                    <button @click="open = !open" class="flex items-center gap-2 hover:bg-gray-50 rounded-lg px-2 py-1 transition-colors">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-semibold text-gray-900 leading-tight" x-text="userName"></p>
                            <span
                                class="inline-block text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                :class="badgeClass"
                                x-text="badgeText"
                            ></span>
                        </div>
                        <div
                            class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm"
                            :class="avatarClass"
                            x-text="initials"
                        ></div>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        x-show="open"
                        @click.away="open = false"
                        x-cloak
                        class="absolute right-0 top-full mt-2 bg-white rounded-xl shadow-xl border border-gray-100 py-2 min-w-40 z-50"
                    >
                        <div class="px-4 py-2 border-b border-gray-100 sm:hidden">
                            <p class="text-sm font-semibold text-gray-900" x-text="userName"></p>
                            <span
                                class="inline-block text-[10px] font-semibold px-1.5 py-0.5 rounded mt-1"
                                :class="badgeClass"
                                x-text="badgeText"
                            ></span>
                        </div>
                        <button
                            @click="doLogout()"
                            class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Keluar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    @endif

    <!-- Main Content -->
    <main class="pb-20">
        @yield('content')
    </main>

    <!-- Bottom Navigation -->
    @if(!isset($hideBottomNav) || !$hideBottomNav)
    <nav class="fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-100 shadow-lg safe-area-inset-bottom" x-data="bottomNav">
        <div class="grid grid-cols-5 gap-1 px-2 py-2" id="bottom-nav" x-html="navHtml">
            <!-- Navigation items will be injected by Alpine.js based on user mode -->
        </div>
    </nav>
    @endif

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-20 right-4 z-50 space-y-2">
        <!-- Toasts will be injected here -->
    </div>

    <!-- Inline Scripts -->
    <script>
        // Switch to kasir mode (for owner)
        async function switchToKasirMode() {
            if (!confirm('Beralih ke mode Kasir?')) return;

            try {
                await api.post('/me/mode', { ui_mode: 'kasir' });
                const user = getAuthUser();
                user.ui_mode = 'kasir';
                localStorage.setItem('auth_user', JSON.stringify(user));
                showToast('✅ Beralih ke mode Kasir', 'success');
                setTimeout(() => {
                    window.location.href = '/pos';
                }, 500);
            } catch (error) {
                showToast('❌ Gagal mengubah mode: ' + error.message, 'error');
            }
        }

        // Switch to owner mode (for owner in kasir mode)
        async function switchToOwnerMode() {
            if (!confirm('Kembali ke mode Owner?')) return;

            try {
                await api.post('/me/mode', { ui_mode: 'owner' });
                const user = getAuthUser();
                user.ui_mode = 'owner';
                localStorage.setItem('auth_user', JSON.stringify(user));
                showToast('✅ Kembali ke mode Owner', 'success');
                setTimeout(() => {
                    window.location.href = '/owner/dashboard';
                }, 500);
            } catch (error) {
                showToast('❌ Gagal mengubah mode: ' + error.message, 'error');
            }
        }

        // Toast helper
        window.showToast = (message, type = 'success') => {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';

            toast.className = `${bgColor} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 animate-slide-in-right max-w-sm`;
            toast.innerHTML = `
                <span class="flex-1 text-sm font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="p-1 hover:bg-white/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 5000);
        };
    </script>

    <!-- ✅ Alpine component untuk dropdown user + logout -->


    @stack('scripts')
</body>
</html>

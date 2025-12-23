<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base-url" content="{{ env('FRONTEND_API_BASE_URL', 'http://127.0.0.1:8000/api') }}">
    <meta name="theme-color" content="#008362">
    
    <title>Logout - Kasir Al-Anwar</title>
    
    @vite('resources/css/app.css')
</head>
<body class="antialiased bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-md mx-auto">
        <div class="w-20 h-20 bg-white rounded-3xl mx-auto mb-6 flex items-center justify-center shadow-2xl">
            <svg class="w-12 h-12 text-[var(--color-primary)] animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-3">Sedang Keluar...</h1>
        <p class="text-gray-600 mb-6">
            Mohon tunggu sebentar
        </p>

        <div class="flex justify-center">
            <svg class="animate-spin h-8 w-8 text-[var(--color-primary)]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    @vite('resources/js/app.js')
    
    <script>
        fetch(`${document.querySelector('meta[name="api-base-url"]').content}/logout`, {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${localStorage.getItem('auth_token')}`,
                "Accept": "application/json"
            }
        }).finally(() => {
            localStorage.clear();
            window.location.href = "/login";
        });

        const api = window.api;
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // Call logout API
                await api.post('/auth/logout');
            } catch (error) {
                console.error('Logout API failed:', error);
                // Continue anyway
            } finally {
                // Clear localStorage
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
                localStorage.removeItem('pos_cart');
                localStorage.removeItem('pos_held_carts');

                // Redirect to login after short delay
                setTimeout(() => {
                    window.location.href = '/login';
                }, 500);
            }
        });
    </script>
</body>
</html>

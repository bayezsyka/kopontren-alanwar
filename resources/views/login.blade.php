<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Kopontren Kasir</title>
    
    @vite(['resources/css/app.css'])
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/images/icons/icon-72x72.png">
    
    @include('layouts.styles')
</head>
<body class="bg-gradient-to-br from-cyan-500 to-blue-600 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <img src="/images/icons/icon-152x152.png" alt="Logo" class="w-24 h-24 mx-auto mb-4">
                <h1 class="text-3xl font-bold text-white mb-2">Kopontren Kasir</h1>
                <p class="text-cyan-100">Sistem Kasir & Inventory</p>
            </div>

            <!-- Login Card -->
            <div class="card">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Login</h2>
                
                <form id="loginForm" class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="input" 
                            placeholder="email@example.com"
                            required
                            autofocus
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="input" 
                            placeholder="••••••••"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                        <span id="loginBtnText">Login</span>
                        <span id="loginBtnLoader" class="hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Loading...
                        </span>
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500 text-center">
                        Demo: owner@test.com / kasir@test.com (password: password)
                    </p>
                </div>
            </div>

            <!-- PWA Info -->
            <div class="mt-6 text-center">
                <p class="text-white text-sm">
                    <i class="fas fa-mobile-alt mr-1"></i>
                    Install aplikasi untuk pengalaman terbaik
                </p>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <script src="/js/api-client.js"></script>
    @include('layouts.scripts')

    <script>
        // Redirect if already logged in
        const token = localStorage.getItem('auth_token');
        if (token) {
            window.location.href = '/pos';
        }

        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const loginBtnText = document.getElementById('loginBtnText');
        const loginBtnLoader = document.getElementById('loginBtnLoader');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Disable button
            loginBtn.disabled = true;
            loginBtnText.classList.add('hidden');
            loginBtnLoader.classList.remove('hidden');

            try {
                const response = await window.api.login(email, password);
                
                showToast('Login berhasil!', 'success');

                // Redirect based on role
                setTimeout(() => {
                    if (response.user.role === 'owner') {
                        window.location.href = '/owner/dashboard';
                    } else {
                        window.location.href = '/pos';
                    }
                }, 500);

            } catch (error) {
                console.error('Login error:', error);
                showToast(error.message || 'Login gagal. Periksa email dan password Anda.', 'error');

                // Enable button
                loginBtn.disabled = false;
                loginBtnText.classList.remove('hidden');
                loginBtnLoader.classList.add('hidden');
            }
        });
    </script>
</body>
</html>

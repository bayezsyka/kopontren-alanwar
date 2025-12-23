<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#008362">
    <title>Offline - Kasir Al-Anwar</title>
    @vite('resources/css/app.css')
</head>
<body class="antialiased bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-md mx-auto">
       <div class="w-24 h-24 bg-white rounded-3xl mx-auto mb-6 flex items-center justify-center shadow-2xl">
            <svg class="w-14 h-14 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-800 mb-3">Anda Sedang Offline</h1>
        <p class="text-gray-600 mb-6">
            Koneksi internet tidak tersedia. Beberapa fitur mungkin tidak berfungsi dengan baik.
        </p>

        <button 
            onclick="window.location.reload()" 
            class="btn btn-primary px-6 py-3"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Coba Lagi
        </button>

        <p class="text-xs text-gray-500 mt-8">
            Halaman akan otomatis dimuat ulang saat koneksi kembali
        </p>
    </div>

    <script>
        // Auto reload when online
        window.addEventListener('online', () => {
            window.location.reload();
        });
    </script>
</body>
</html>

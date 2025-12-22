<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
    <link rel="icon" type="image/png" href="/images/icons/icon-72x72.png">
    
    <title>@yield('title', 'Kopontren Kasir')</title>
    
    <!-- Styles -->
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @include('layouts.styles')
    @stack('styles')
</head>
<body class="bg-gray-50">
    @include('layouts.offline-indicator')
    @include('layouts.header')
    
    <!-- Main Content -->
    <main class="pb-20">
        @yield('content')
    </main>

    <!-- Bottom Navigation (Mobile) -->
    @include('layouts.bottom-nav')

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- PWA Install Button -->
    <button id="pwa-install-btn" style="display: none;" 
            class="fixed bottom-24 right-4 bg-cyan-500 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-cyan-600 transition z-50 text-sm">
        <i class="fas fa-download mr-1"></i> Install App
    </button>

    @include('layouts.scripts')
    @stack('scripts')
</body>
</html>

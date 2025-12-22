@extends('layouts.app')

@section('title', 'Offline - Kopontren Kasir')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="text-center">
        <div class="mb-8">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path>
            </svg>
        </div>

        <h1 class="text-4xl font-bold text-gray-900 mb-4">Anda Sedang Offline</h1>
        
        <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
            Koneksi internet terputus. Tenang, Anda masih bisa melakukan transaksi dan data akan tersimpan secara lokal.
        </p>

        <div class="space-y-4">
            <div class="bg-cyan-50 border border-cyan-200 rounded-lg p-4 max-w-md mx-auto">
                <h3 class="font-semibold text-cyan-900 mb-2">✅ Fitur yang Tetap Berfungsi:</h3>
                <ul class="text-left text-sm text-cyan-800 space-y-1">
                    <li>• Melakukan transaksi penjualan</li>
                    <li>• Menambah item ke keranjang</li>
                    <li>• Melihat data yang sudah di-cache</li>
                    <li>• Mencatat transaksi offline</li>
                </ul>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 max-w-md mx-auto">
                <h3 class="font-semibold text-amber-900 mb-2">⏳ Akan Tersinkronisasi sat Online:</h3>
                <ul class="text-left text-sm text-amber-800 space-y-1">
                    <li>• Semua transaksi offline</li>
                    <li>• Data yang belum tersimpan ke server</li>
                    <li>• Update stok barang</li>
                </ul>
            </div>
        </div>

        <div class="mt-8 space-x-4">
            <button onclick="window.location.reload()" 
                    class="bg-cyan-500 hover:bg-cyan-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                Coba Lagi
            </button>
            
            <a href="/" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-semibold transition">
                Kembali ke Beranda
            </a>
        </div>

        <div class="mt-8 text-sm text-gray-500">
            <p>Data offline akan otomatis tersinkronisasi ketika koneksi kembali.</p>
        </div>
    </div>
</div>

<script>
    // Auto redirect when online
    window.addEventListener('online', () => {
        showToast('Koneksi kembali! Mengalihkan...', 'success');
        setTimeout(() => {
            window.location.href = '/';
        }, 1500);
    });
</script>
@endsection

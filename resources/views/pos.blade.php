@extends('layouts.app')

@section('title', 'POS Kasir')
@section('page-title', 'POS Kasir')

@section('content')
<div class="p-4 space-y-4" x-data="posAppComponent">
    <!-- Search Bar -->
    <div class="card">
        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Cari Barang</label>
        <div class="relative">
            <input 
                type="text" 
                x-model="searchQuery"
                @input.debounce.250ms="searchItems"
                @keydown.enter.prevent="selectFirstItem"
                class="input-field pl-11"
                placeholder="Ketik nama atau scan barcode..."
                autofocus
                x-ref="searchInput"
            >
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>

            <!-- Search Dropdown -->
            <div 
                x-show="searchResults.length > 0 && searchQuery.length > 0" 
                @click.away="searchResults = []"
                class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl border border-gray-200 shadow-xl max-h-64 overflow-y-auto z-20"
            >
                <template x-for="item in searchResults" :key="item.id">
                    <button 
                        @click="addToCart(item)"
                        class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0"
                    >
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex-1">
                                <p class="font-semibold text-sm text-gray-900" x-text="item.name"></p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <span x-text="item.type === 'bundle' ? '📦 Bundle' : '📋 Normal'"></span>
                                    <span class="mx-1">•</span>
                                    <span>Stok: <span x-text="item.stock_display"></span></span>
                                </p>
                            </div>
                            <p class="font-bold text-sm text-[var(--color-primary)]" x-text="formatCurrency(item.price_sell)"></p>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Quick Items -->
    <div class="card" x-show="quickItems.length > 0">
        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Item Cepat</h3>
        <div class="grid grid-cols-3 gap-2">
            <template x-for="item in quickItems" :key="item.id">
                <button 
                    @click="addToCart(item)"
                    class="bg-gradient-to-br from-[var(--color-primary)] to-[var(--color-primary-dark)] text-white rounded-xl p-3 text-center hover:scale-105 active:scale-95 transition-transform shadow-lg"
                >
                    <p class="font-bold text-sm mb-1" x-text="item.name"></p>
                    <p class="text-xs opacity-90" x-text="formatCurrency(item.price_sell)"></p>
                </button>
            </template>
        </div>
    </div>

    <!-- Cart -->
    <div class="card">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-xs font-semibold text-gray-500 uppercase">Keranjang (<span x-text="cart.length"></span>)</h3>
            <button 
                x-show="cart.length > 0"
                @click="clearCart"
                class="text-xs text-red-500 hover:text-red-700 font-semibold"
            >
                Kosongkan
            </button>
        </div>

        <div x-show="cart.length === 0" class="text-center py-8 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-sm font-medium">Keranjang kosong</p>
        </div>

        <div x-show="cart.length > 0" class="space-y-2">
            <template x-for="(cartItem, index) in cart" :key="index">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <p class="font-semibold text-sm text-gray-900" x-text="cartItem.name"></p>
                            <p class="text-xs text-gray-500" x-text="formatCurrency(cartItem.price)"></p>
                        </div>
                        <button 
                            @click="removeFromCart(index)"
                            class="p-1 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button 
                                @click="decrementQty(index)"
                                class="w-10 h-10 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 active:scale-95 transition-all font-bold text-lg"
                            >-</button>
                            <input 
                                type="number" 
                                x-model.number="cartItem.qty"
                                @change="updateCartItem(index)"
                                min="1"
                                class="w-16 text-center bg-white border border-gray-200 rounded-lg py-2 font-semibold text-sm"
                            >
                            <button 
                                @click="incrementQty(index)"
                                class="w-10 h-10 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 active:scale-95 transition-all font-bold text-lg"
                            >+</button>
                        </div>
                        <p class="font-bold text-[var(--color-primary)]" x-text="formatCurrency(cartItem.price * cartItem.qty)"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Checkout -->
    <div class="card bg-gradient-to-br from-gray-50 to-white" x-show="cart.length > 0">
        <!-- Payment Method -->
        <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Metode Pembayaran</label>
            <select x-model="paymentMethod" class="input-field">
                <option value="cash">💵 Tunai</option>
                <option value="qris">📱 QRIS</option>
                <option value="transfer">🏦 Transfer</option>
            </select>
        </div>

        <!-- Total -->
        <div class="bg-white rounded-xl p-4 mb-4 border-2 border-[var(--color-primary)] shadow-lg">
            <div class="flex justify-between items-center">
                <span class="text-sm font-semibold text-gray-700">Total</span>
                <span class="text-2xl font-bold text-[var(--color-primary)]" x-text="formatCurrency(total)"></span>
            </div>
        </div>

        <!-- Checkout Button -->
        <button 
            @click="checkout"
            :disabled="loading"
            class="btn btn-primary w-full text-base py-4 shadow-xl"
        >
            <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-show="!loading">💳 Selesaikan Pembayaran</span>
            <span x-show="loading">Memproses...</span>
        </button>

        <!-- Secondary Actions -->
        <div class="grid grid-cols-2 gap-2 mt-2">
            <button @click="holdCart" class="btn btn-secondary text-xs py-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                Tahan
            </button>
            <button @click="resumeCart" class="btn btn-secondary text-xs py-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Lanjutkan
            </button>
        </div>
    </div>

    <!-- Stock Error Modal -->
    <div 
        x-show="stockError" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="stockError = null"
    >
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-red-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Stok Tidak Cukup</h3>
                <template x-if="stockError">
                    <div class="text-sm text-gray-600 space-y-2" x-html="stockError"></div>
                </template>
            </div>
            <button @click="stockError = null" class="btn btn-primary w-full">OK</button>
        </div>
    </div>
</div>
@endsection

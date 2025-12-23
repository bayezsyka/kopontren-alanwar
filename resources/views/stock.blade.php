@extends('layouts.app')

@section('title', 'Stok')
@section('page-title', 'Manajemen Stok')

@section('content')
<div class="p-4 space-y-4" x-data="stockAppComponent">
    <!-- Low Stock Alert -->
    <div x-show="lowStockItems.length > 0" class="card bg-red-50 border-red-200">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
                <h3 class="font-bold text-sm text-red-900 mb-1">⚠️ Stok Rendah</h3>
                <p class="text-xs text-red-700 mb-2"><span x-text="lowStockItems.length"></span> barang stoknya rendah!</p>
                <div class="space-y-1">
                    <template x-for="item in lowStockItems" :key="item.id">
                        <div class="text-xs bg-white rounded-lg px-3 py-2 flex justify-between">
                            <span class="font-semibold" x-text="item.name"></span>
                            <span class="text-red-600" x-text="`Stok: ${item.stock} (min: ${item.threshold})`"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="card">
        <input 
            type="text" 
            x-model="searchQuery"
            @input.debounce.300ms="searchItems"
            class="input-field"
            placeholder="Cari barang..."
        >
    </div>

    <!-- Items List -->
    <div class="space-y-2">
        <template x-for="item in items" :key="item.id">
            <div class="card">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <h3 class="font-bold text-sm text-gray-900 mb-1" x-text="item.name"></h3>
                        <div class="text-xs text-gray-500 space-y-0.5">
                            <p><strong>Tipe:</strong> <span x-text="item.type === 'bundle' ? '📦 Bundle' : '📋 Normal'"></span></p>
                            <p><strong>Stok:</strong> 
                                <span 
                                    class="font-semibold"
                                    :class="getStockColor(item)"
                                    x-text="item.type === 'bundle' ? (item.stock_computed || 0) : (item.stock_cached || 0)"
                                ></span>
                                <span x-show="item.low_stock_threshold" class="text-gray-400" x-text="`(min: ${item.low_stock_threshold})`"></span>
                            </p>
                        </div>
                    </div>

                    <button 
                        x-show="isOwner()"
                        @click="openAdjustModal(item)"
                        class="btn btn-primary text-xs px-3 py-2"
                    >
                        Sesuaikan
                    </button>
                </div>
            </div>
        </template>

        <div x-show="items.length === 0 && !loading" class="card text-center py-8 text-gray-400">
            <p class="text-sm font-medium">Tidak ada barang</p>
        </div>
    </div>

    <!-- Adjust Stock Modal (Owner Only) -->
    <div 
        x-show="showAdjustModal" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="closeAdjustModal"
    >
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full">
            <h2 class="text-lg font-bold mb-4">Sesuaikan Stok</h2>
            <template x-if="adjustingItem">
                <form @submit.prevent="adjustStock" class="space-y-4">
                    <div>
                        <p class="font-semibold text-sm mb-1" x-text="adjustingItem.name"></p>
                        <p class="text-xs text-gray-500">Stok saat ini: <span x-text="adjustingItem.type === 'bundle' ? (adjustingItem.stock_computed || 0) : (adjustingItem.stock_cached || 0)"></span></p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Stok Baru <span class="text-red-500">*</span></label>
                        <input type="number" x-model.number="adjustForm.new_stock" class="input-field" min="0" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Catatan</label>
                        <textarea x-model="adjustForm.notes" class="input-field resize-none" rows="2" placeholder="Alasan adjust..."></textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" @click="closeAdjustModal" class="btn btn-secondary flex-1">Batal</button>
                        <button type="submit" class="btn btn-primary flex-1" :disabled="saving">
                            <span x-show="!saving">Simpan</span>
                            <span x-show="saving">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection


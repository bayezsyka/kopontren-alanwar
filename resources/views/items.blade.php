@extends('layouts.app')

@section('title', 'Barang')
@section('page-title', 'Daftar Barang')

@section('content')
<div class="p-4 space-y-4" x-data="itemsAppComponent">
    <!-- Header with Add Button -->
    <div class="flex justify-between items-center">
        <h2 class="text-lg font-bold text-gray-800">Kelola Barang</h2>
        <button @click="openModal()" class="btn btn-primary text-sm px-3 py-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah
        </button>
    </div>

    <!-- Search -->
    <div class="card">
        <input 
            type="text" 
            x-model="searchQuery"
            @input.debounce.300ms="loadItems"
            class="input-field"
            placeholder="Cari barang..."
        >
    </div>

    <!-- Items List -->
    <div class="space-y-2">
        <template x-for="item in items" :key="item.id">
            <div class="card hover:shadow-md transition-shadow">
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-bold text-sm text-gray-900" x-text="item.name"></h3>
                            <span 
                                class="text-xs px-2 py-0.5 rounded-full font-semibold"
                                :class="item.type === 'bundle' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                x-text="item.type === 'bundle' ? '📦 Bundle' : '📋 Normal'"
                            ></span>
                            <span 
                                x-show="item.is_quick" 
                                class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-semibold"
                            >⚡ Cepat</span>
                        </div>
                        
                        <div class="text-xs text-gray-500 space-y-0.5">
                            <p><strong>Harga Jual:</strong> <span x-text="formatCurrency(item.price_sell)"></span></p>
                            <p><strong>Stok:</strong> <span x-text="item.type === 'bundle' ? (item.stock_computed || 0) : (item.stock_cached || 0)"></span></p>
                            <p x-show="item.sku"><strong>SKU:</strong> <span x-text="item.sku"></span></p>
                            <p x-show="item.barcode"><strong>Barcode:</strong> <span x-text="item.barcode"></span></p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <button 
                            @click="editItem(item)"
                            class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors"
                            title="Edit Barang"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button 
                            x-show="item.type === 'bundle'"
                            @click="editBundleComponents(item)"
                            class="p-2 text-purple-500 hover:bg-purple-50 rounded-lg transition-colors"
                            title="Edit Komponen Bundle"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </button>
                        <button 
                            @click="deleteItem(item)"
                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                            title="Hapus Barang"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="items.length === 0 && !loading" class="card text-center py-8 text-gray-400">
            <p class="text-sm font-medium">Tidak ada barang</p>
        </div>

        <div x-show="loading" class="card text-center py-8">
            <svg class="animate-spin w-8 h-8 mx-auto text-[var(--color-primary)]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    <!-- Item Modal -->
    <div 
        x-show="showModal" 
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm"
        @click.self="closeModal()"
    >
        <div class="bg-white rounded-t-3xl sm:rounded-2xl w-full sm:max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center">
                <h2 class="text-lg font-bold" x-text="editingItem ? 'Edit Barang' : 'Tambah Barang'"></h2>
                <button @click="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="saveItem()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name" class="input-field" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tipe <span class="text-red-500">*</span></label>
                        <select x-model="form.type" class="input-field" required>
                            <option value="normal">Normal</option>
                            <option value="bundle">Bundle</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Harga Jual <span class="text-red-500">*</span></label>
                        <input type="number" x-model="form.price_sell" class="input-field" min="0" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">SKU</label>
                        <input type="text" x-model="form.sku" class="input-field">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Barcode</label>
                        <input type="text" x-model="form.barcode" class="input-field">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Batas Stok Rendah</label>
                        <input type="number" x-model="form.low_stock_threshold" class="input-field" min="0">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Urutan Cepat</label>
                        <input type="number" x-model="form.quick_order" class="input-field" min="0" placeholder="0 = tidak tampil">
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="form.is_quick" class="w-5 h-5 text-[var(--color-primary)] border-gray-300 rounded focus:ring-[var(--color-primary)]">
                        <span class="text-sm font-semibold">Tampilkan di Item Cepat</span>
                    </label>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="button" @click="closeModal()" class="btn btn-secondary flex-1">Batal</button>
                    <button type="submit" class="btn btn-primary flex-1" :disabled="saving">
                        <span x-show="!saving">Simpan</span>
                        <span x-show="saving">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

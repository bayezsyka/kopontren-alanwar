@extends('layouts.app')

@section('title', 'Restock')
@section('page-title', 'Restock Barang')

@section('content')
<div class="p-4 space-y-4" x-data="restockAppComponent">
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
                placeholder="Cari barang normal untuk restock..."
                autofocus
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
                        @click="addToLines(item)"
                        class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0"
                    >
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex-1">
                                <p class="font-semibold text-sm text-gray-900" x-text="item.name"></p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <span x-text="item.type === 'bundle' ? '⚠️ Bundle (tidak direkomendasikan)' : '✅ Normal'"></span>
                                    <span class="mx-1">•</span>
                                    <span>Stok: <span x-text="item.stock_cached || 0"></span></span>
                                </p>
                            </div>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Restock Lines -->
    <div class="card">
        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Item Restock (<span x-text="lines.length"></span>)</h3>

        <div x-show="lines.length === 0" class="text-center py-8 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-sm font-medium">Belum ada item</p>
        </div>

        <div x-show="lines.length > 0" class="space-y-3">
            <template x-for="(line, index) in lines" :key="index">
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-200">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <p class="font-semibold text-sm text-gray-900" x-text="line.item_name"></p>
                            <p class="text-xs text-gray-500">Stok saat ini: <span x-text="line.current_stock"></span></p>
                        </div>
                        <button 
                            @click="removeLine(index)"
                            class="p-1 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Quantity -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Jumlah Masuk</label>
                            <input 
                                type="number" 
                                x-model.number="line.qty"
                                min="1"
                                class="input-field text-center font-semibold"
                                placeholder="0"
                            >
                        </div>

                        <!-- Unit Cost (optional) -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Harga Beli (opsional)</label>
                            <input 
                                type="number" 
                                x-model.number="line.unit_cost"
                                min="0"
                                class="input-field text-center"
                                placeholder="0"
                            >
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Notes -->
    <div class="card" x-show="lines.length > 0">
        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Catatan (opsional)</label>
        <textarea 
            x-model="notes"
            rows="2"
            class="input-field resize-none"
            placeholder="Misal: Restock dari supplier X..."
        ></textarea>
    </div>

    <!-- Submit -->
    <div class="card bg-gradient-to-br from-gray-50 to-white" x-show="lines.length > 0">
        <button 
            @click="submitRestock"
            :disabled="loading || !isValid"
            class="btn btn-primary w-full text-base py-4 shadow-xl"
            :class="{ 'opacity-50 cursor-not-allowed': !isValid }"
        >
            <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-show="!loading">📦 Simpan Restock</span>
            <span x-show="loading">Memproses...</span>
        </button>

        <button 
            @click="clearLines"
            class="btn btn-secondary w-full text-sm py-2 mt-2"
        >
            Kosongkan
        </button>
    </div>
</div>
@endsection


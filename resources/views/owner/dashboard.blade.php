@extends('layouts.app')

@section('title', 'Dashboard Owner')
@section('page-title', 'Dashboard Owner')

@section('content')
<div class="p-4 space-y-4" x-data="ownerDashboardComponent">
    <!-- Period Selector -->
    <div class="card">
        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Periode</label>
        <div class="grid grid-cols-3 gap-2">
            <button 
                @click="setPeriod('week')"
                :class="period === 'week' ? 'btn btn-primary' : 'btn btn-secondary'"
                class="text-sm py-2"
            >
                Minggu Ini
            </button>
            <button 
                @click="setPeriod('month')"
                :class="period === 'month' ? 'btn btn-primary' : 'btn btn-secondary'"
                class="text-sm py-2"
            >
                Bulan Ini
            </button>
            <button 
                @click="setPeriod('year')"
                :class="period === 'year' ? 'btn btn-primary' : 'btn btn-secondary'"
                class="text-sm py-2"
            >
                Tahun Ini
            </button>
        </div>
        <p class="text-xs text-gray-500 mt-2 text-center" x-text="periodLabel"></p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 gap-3">
        <div class="card bg-gradient-to-br from-green-500 to-green-600 text-white">
            <p class="text-xs opacity-80 mb-1">Total Penjualan</p>
            <p class="text-2xl font-bold" x-text="formatCurrency(summary.sales_total || 0)"></p>
            <p class="text-xs opacity-80 mt-1"><span x-text="summary.sales_count || 0"></span> transaksi</p>
        </div>

        <div class="card bg-gradient-to-br from-blue-500 to-blue-600 text-white">
            <p class="text-xs opacity-80 mb-1">Total Belanja</p>
            <p class="text-2xl font-bold" x-text="formatCurrency(summary.purchase_total || 0)"></p>
            <p class="text-xs opacity-80 mt-1"><span x-text="summary.purchase_count || 0"></span> transaksi</p>
        </div>

        <div class="card bg-gradient-to-br from-purple-500 to-purple-600 text-white col-span-2">
            <p class="text-xs opacity-80 mb-1">Laba Kotor</p>
            <p class="text-2xl font-bold" x-text="formatCurrency(summary.gross_profit_simple || 0)"></p>
        </div>
    </div>

    <!-- Series Chart (Simple Table) -->
    <div class="card">
        <h3 class="text-sm font-bold text-gray-900 mb-3">Penjualan Harian</h3>
        <div class="overflow-x-auto" x-show="series.length > 0" x-cloak>
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 font-semibold text-gray-600">Tanggal</th>
                        <th class="text-right py-2 font-semibold text-gray-600">Penjualan</th>
                        <th class="text-right py-2 font-semibold text-gray-600">Belanja</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in series" :key="item.date">
                        <tr class="border-b border-gray-100">
                            <td class="py-2" x-text="formatDate(item.date)"></td>
                            <td class="text-right text-green-600 font-semibold" x-text="formatCurrency(item.sales_total)"></td>
                            <td class="text-right text-blue-600 font-semibold" x-text="formatCurrency(item.purchase_total)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="series.length === 0 && !loading" class="text-center py-6 text-gray-400">
            <p class="text-sm">Tidak ada data</p>
        </div>
    </div>

    <!-- Top Items -->
    <div class="card">
        <h3 class="text-sm font-bold text-gray-900 mb-3">Top 10 Barang Terlaris</h3>
        <div class="space-y-2" x-show="topItems.length > 0" x-cloak>
            <template x-for="(item, index) in topItems" :key="item.id">
                <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-3">
                    <div class="w-6 h-6 bg-[var(--color-primary)] text-white rounded-full flex items-center justify-center text-xs font-bold" x-text="index + 1"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm" x-text="item.name"></p>
                        <p class="text-xs text-gray-500"><span x-text="item.qty"></span> terjual • <span x-text="formatCurrency(item.revenue)"></span></p>
                    </div>
                </div>
            </template>
        </div>
        <div x-show="topItems.length === 0 && !loading" class="text-center py-6 text-gray-400">
            <p class="text-sm">Tidak ada data</p>
        </div>
    </div>

    <div x-show="loading" class="card text-center py-8">
        <svg class="animate-spin w-8 h-8 mx-auto text-[var(--color-primary)]" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div>
@endsection

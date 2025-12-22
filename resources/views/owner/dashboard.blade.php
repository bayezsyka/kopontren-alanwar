@extends('layouts.main')

@section('title', 'Owner Dashboard - Kopontren Kasir')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2 bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">Dashboard Owner</h2>
        <p class="text-gray-600 text-sm">Monitor penjualan dan performa kopontren Anda</p>
        
        <!-- Date Range Filter -->
        <div class="mt-6 flex gap-3 items-center flex-wrap bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 flex-1 min-w-[200px]">
                <i class="fas fa-calendar-alt text-cyan-500"></i>
                <input type="date" id="dateFrom" class="input input-sm flex-1">
            </div>
            <span class="text-gray-400 hidden sm:block">-</span>
            <div class="flex items-center gap-2 flex-1 min-w-[200px]">
                <i class="fas fa-calendar-alt text-cyan-500"></i>
                <input type="date" id="dateTo" class="input input-sm flex-1">
            </div>
            <button id="loadDataBtn" class="btn btn-primary btn-sm">
                <i class="fas fa-sync-alt mr-2"></i>Load Data
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Penjualan -->
        <div class="stat-card" style="--from-color: #10b981; --to-color: #059669; --shadow-color: rgba(16, 185, 129, 0.4);">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm opacity-90 mb-2 font-medium">Total Penjualan</p>
                    <p id="salesTotal" class="text-3xl md:text-4xl font-bold mb-1">Rp 0</p>
                    <p class="text-xs opacity-75">Periode dipilih</p>
                </div>
                <div class="ml-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-shopping-cart text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pembelian -->
        <div class="stat-card" style="--from-color: #ef4444; --to-color: #dc2626; --shadow-color: rgba(239, 68, 68, 0.4);">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm opacity-90 mb-2 font-medium">Total Pembelian</p>
                    <p id="purchaseTotal" class="text-3xl md:text-4xl font-bold mb-1">Rp 0</p>
                    <p class="text-xs opacity-75">Periode dipilih</p>
                </div>
                <div class="ml-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-truck text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profit -->
        <div class="stat-card" style="--from-color: #06b6d4; --to-color: #0284c7; --shadow-color: rgba(6, 182, 212, 0.4);">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm opacity-90 mb-2 font-medium">Profit (Estimasi)</p>
                    <p id="profitSimple" class="text-3xl md:text-4xl font-bold mb-1">Rp 0</p>
                    <p class="text-xs opacity-75">Keuntungan bersih</p>
                </div>
                <div class="ml-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Sales Chart -->
        <div class="card">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                    <i class="fas fa-chart-area text-cyan-500"></i>
                    Grafik Penjualan vs Pembelian
                </h3>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Top Items -->
        <div class="card">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                    <i class="fas fa-fire text-orange-500"></i>
                    Top Selling Items
                </h3>
            </div>
            <div id="topItemsTable" class="overflow-x-auto -mx-5">
                <div class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <div class="spinner mb-3"></div>
                        <p class="text-gray-400 text-sm">Loading data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let salesChart = null;

// Set default dates (this week)
function setDefaultDates() {
    const today = new Date();
    const weekAgo = new Date(today);
    weekAgo.setDate(weekAgo.getDate() - 7);

    document.getElementById('dateTo').value = today.toISOString().split('T')[0];
    document.getElementById('dateFrom').value = weekAgo.toISOString().split('T')[0];
}

async function loadDashboardData() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;

    if (!dateFrom || !dateTo) {
        showToast('Pilih tanggal terlebih dahulu', 'warning');
        return;
    }

    showLoading();

    try {
        const [summary, series, topItems] = await Promise.all([
            window.api.get(`/dashboard/summary?from=${dateFrom}&to=${dateTo}`),
            window.api.get(`/dashboard/series?from=${dateFrom}&to=${dateTo}`),
            window.api.get(`/dashboard/top-items?from=${dateFrom}&to=${dateTo}&limit=10`)
        ]);

        hideLoading();

        // Update summary cards
        document.getElementById('salesTotal').textContent = formatRupiah(summary.sales_total || 0);
        document.getElementById('purchaseTotal').textContent = formatRupiah(summary.purchase_total || 0);
        document.getElementById('profitSimple').textContent = formatRupiah(summary.profit_simple || 0);

        // Render chart
        renderChart(series);

        // Render top items
        renderTopItems(topItems);

    } catch (error) {
        hideLoading();
        console.error('Dashboard load error:', error);
        showToast(error.message || 'Gagal memuat data dashboard', 'error');
    }
}

function renderChart(series) {
    const ctx = document.getElementById('salesChart').getContext('2d');

    if (salesChart) {
        salesChart.destroy();
    }

    // Ensure series is an array
    if (!series || !Array.isArray(series)) {
        console.warn('Invalid series data:', series);
        series = [];
    }

    const dates = series.map(s => formatDate(s.date));
    const sales = series.map(s => s.sales_total || 0);
    const purchases = series.map(s => s.purchase_total || 0);

    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Penjualan',
                    data: sales,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.3
                },
                {
                    label: 'Pembelian',
                    data: purchases,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
}

function renderTopItems(items) {
    const table = document.getElementById('topItemsTable');

    if (!items || items.length === 0) {
        table.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 px-5">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 font-medium mb-1">Tidak ada data</p>
                <p class="text-gray-400 text-xs">Belum ada penjualan pada periode ini</p>
            </div>
        `;
        return;
    }

    table.innerHTML = `
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="sticky left-0 bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Item</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Qty</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                ${items.map((item, index) => `
                    <tr class="hover:bg-gradient-to-r hover:from-cyan-50/50 hover:to-transparent transition-all duration-150">
                        <td class="sticky left-0 bg-white px-5 py-3.5 text-gray-500 font-medium">${index + 1}</td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-gray-900">${item.name}</div>
                            ${item.code ? `<div class="text-xs text-gray-500 mt-0.5">${item.code}</div>` : ''}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-semibold text-xs">
                                ${formatNumber(item.qty_sold)}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold text-cyan-600">${formatRupiah(item.total_sales)}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

document.getElementById('loadDataBtn').addEventListener('click', loadDashboardData);

// Init
setDefaultDates();
loadDashboardData();
</script>
@endpush

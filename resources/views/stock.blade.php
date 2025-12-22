@extends('layouts.main')

@section('title', 'Stock - Kopontren Kasir')

@section('content')
<div class="container mx-auto px-4 py-4 max-w-6xl">
    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
        <i class="fas fa-warehouse text-cyan-600"></i>
        Monitoring Stock
    </h2>

    <!-- Low Stock Alert -->
    <div id="lowStockAlert" class="hidden mb-4">
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
                <h3 class="font-bold text-red-900">Peringatan Stock Rendah!</h3>
            </div>
            <div id="lowStockItems"></div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="card">
        <div class="mb-4">
            <input type="text" id="searchStock" class="input input-sm" placeholder="Cari item...">
        </div>

        <div id="stockTable" class="overflow-x-auto">
            <div class="text-center py-8">
                <div class="spinner mx-auto mb-3"></div>
                <p class="text-gray-500">Loading...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let stockItems = [];
let lowStockItems = [];

async function loadStock() {
    try {
        const [itemsResponse, lowStockResponse] = await Promise.all([
            window.api.get('/items'),
            window.api.get('/stock/low')
        ]);

        stockItems = itemsResponse.data || itemsResponse;
        lowStockItems = lowStockResponse.data || lowStockResponse;

        renderLowStockAlert();
        renderStockTable();
    } catch (error) {
        console.error('Load stock error:', error);
        showToast('Gagal memuat data stock', 'error');
    }
}

function renderLowStockAlert() {
    const alertDiv = document.getElementById('lowStockAlert');
    const itemsDiv = document.getElementById('lowStockItems');

    if (lowStockItems.length === 0) {
        alertDiv.classList.add('hidden');
        return;
    }

    alertDiv.classList.remove('hidden');
    itemsDiv.innerHTML = lowStockItems.map(item => `
        <div class="text-sm text-red-800 mt-1">
            • <span class="font-semibold">${item.name}</span>: 
            Stock ${item.stock_cached} (min ${item.low_stock_threshold})
        </div>
    `).join('');
}

function renderStockTable() {
    const searchQuery = document.getElementById('searchStock').value.toLowerCase();
    const filtered = stockItems.filter(item => 
        item.name.toLowerCase().includes(searchQuery)
    );

    const table = document.getElementById('stockTable');
    
    if (filtered.length === 0) {
        table.innerHTML = '<p class="text-gray-400 text-center py-8">Tidak ada data</p>';
        return;
    }

    table.innerHTML = `
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Min Stock</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                ${filtered.map(item => {
                    const stock = item.stock_cached || 0;
                    const threshold = item.low_stock_threshold || 0;
                    const isLow = item.type === 'normal' && stock <= threshold;
                    
                    return `
                        <tr class="${isLow ? 'bg-red-50' : 'hover:bg-gray-50'}">
                            <td class="px-4 py-3">
                                <div class="font-semibold">${item.name}</div>
                                ${item.sku ? `<div class="text-xs text-gray-500">${item.sku}</div>` : ''}
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge ${item.type === 'bundle' ? 'badge-info' : 'badge-success'}">
                                    ${item.type}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-bold ${isLow ? 'text-red-600' : 'text-gray-900'}">
                                ${stock}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                ${item.type === 'normal' ? threshold : '-'}
                            </td>
                            <td class="px-4 py-3 text-center">
                                ${isLow ? 
                                    '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Low</span>' :
                                    '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>OK</span>'
                                }
                            </td>
                        </tr>
                    `;
                }).join('')}
            </tbody>
        </table>
    `;
}

document.getElementById('searchStock').addEventListener('input', renderStockTable);

// Init
loadStock();

// Auto refresh every 30 seconds
setInterval(loadStock, 30000);
</script>
@endpush

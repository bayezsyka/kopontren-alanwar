@extends('layouts.main')

@section('title', 'Restock - Kopontren Kasir')

@section('content')
<div class="container mx-auto px-4 py-4 max-w-4xl">
    <div class="card">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <i class="fas fa-truck-loading text-cyan-600"></i>
            Restock Barang
        </h2>

        <!-- Search Item -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Cari Item</label>
            <div class="relative">
                <input 
                    type="text" 
                    id="searchItem" 
                    class="input pl-12" 
                    placeholder="Cari item untuk restock..."
                >
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
            <div id="itemSearchResults" class="mt-2 space-y-2 hidden"></div>
        </div>

        <!-- Purchase Lines -->
        <div class="mb-6">
            <h3 class="font-semibold text-gray-900 mb-3">Daftar Item Restock</h3>
            <div id="purchaseLines" class="space-y-3">
                <p class="text-gray-400 text-center py-8">Belum ada item. Cari dan tambahkan item di atas.</p>
            </div>
        </div>

        <!-- Notes -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
            <textarea id="notes" class="input" rows="3" placeholder="Catatan pembelian..."></textarea>
        </div>

        <!-- Total & Submit -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between items-center mb-4">
                <span class="font-bold">Total Biaya:</span>
                <span id="totalCost" class="text-2xl font-bold text-cyan-600">Rp 0</span>
            </div>
            <button id="submitPurchaseBtn" class="btn btn-primary btn-block" disabled>
                <i class="fas fa-check-circle mr-2"></i>
                Submit Restock
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let purchaseLines = [];

// Search Item
const searchItem = document.getElementById('searchItem');
const itemSearchResults = document.getElementById('itemSearchResults');

const searchItems = debounce(async function(query) {
    if (query.length < 2) {
        itemSearchResults.classList.add('hidden');
        return;
    }

    try {
        const response = await window.api.get(`/items?search=${encodeURIComponent(query)}&type=normal`);
        const items = (response.data || response).filter(item => item.type === 'normal');
        
        if (items.length === 0) {
            itemSearchResults.innerHTML = '<p class="text-gray-400 text-sm p-2">Tidak ada hasil</p>';
        } else {
            itemSearchResults.innerHTML = items.map(item => `
                <button onclick="addPurchaseLine(${JSON.stringify(item).replace(/"/g, '&quot;')})" 
                        class="w-full text-left p-3 hover:bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between">
                        <div>
                            <div class="font-semibold">${item.name}</div>
                            <div class="text-sm text-gray-500">Stock: ${item.stock_cached || 0}</div>
                        </div>
                        <div class="text-cyan-600">${formatRupiah(item.price_sell)}</div>
                    </div>
                </button>
            `).join('');
        }
        
        itemSearchResults.classList.remove('hidden');
    } catch (error) {
        console.error('Search error:', error);
    }
}, 300);

searchItem.addEventListener('input', (e) => searchItems(e.target.value));

function addPurchaseLine(item) {
    const existing = purchaseLines.find(l => l.item_id === item.id);
    if (existing) {
        showToast('Item sudah ada dalam daftar', 'warning');
        return;
    }

    purchaseLines.push({
        item_id: item.id,
        name: item.name,
        qty: 1,
        unit_cost: 0
    });

    searchItem.value = '';
    itemSearchResults.classList.add('hidden');
    renderPurchaseLines();
}

function renderPurchaseLines() {
    const container = document.getElementById('purchaseLines');
    const totalCostEl = document.getElementById('totalCost');
    const submitBtn = document.getElementById('submitPurchaseBtn');

    if (purchaseLines.length === 0) {
        container.innerHTML = '<p class="text-gray-400 text-center py-8">Belum ada item.</p>';
        totalCostEl.textContent = 'Rp 0';
        submitBtn.disabled = true;
        return;
    }

    let total = 0;
    container.innerHTML = purchaseLines.map((line, index) => {
        const subtotal = line.qty * line.unit_cost;
        total += subtotal;

        return `
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between items-start mb-3">
                    <div class="font-semibold text-gray-900">${line.name}</div>
                    <button onclick="removeLine(${index})" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-gray-600">Qty</label>
                        <input type="number" value="${line.qty}" min="1" 
                               onchange="updateLine(${index}, 'qty', parseInt(this.value))"
                               class="input input-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Harga Beli</label>
                        <input type="number" value="${line.unit_cost}" min="0" 
                               onchange="updateLine(${index}, 'unit_cost', parseInt(this.value))"
                               class="input input-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Subtotal</label>
                        <div class="font-bold text-cyan-600 mt-2">${formatRupiah(subtotal)}</div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    totalCostEl.textContent = formatRupiah(total);
    submitBtn.disabled = purchaseLines.length === 0 || purchaseLines.some(l => l.qty <= 0 || l.unit_cost <= 0);
}

function updateLine(index, field, value) {
    purchaseLines[index][field] = value;
    renderPurchaseLines();
}

function removeLine(index) {
    purchaseLines.splice(index, 1);
    renderPurchaseLines();
}

document.getElementById('submitPurchaseBtn').addEventListener('click', async () => {
    const notes = document.getElementById('notes').value;

    const purchaseData = {
        notes: notes || null,
        lines: purchaseLines.map(line => ({
            item_id: line.item_id,
            qty: line.qty,
            unit_cost: line.unit_cost
        }))
    };

    showLoading();
    
    try {
        await window.api.post('/purchases', purchaseData);
        hideLoading();
        
        showToast('Restock berhasil!', 'success');
        
        // Reset form
        purchaseLines = [];
        document.getElementById('notes').value = '';
        renderPurchaseLines();
        
    } catch (error) {
        hideLoading();
        console.error('Purchase error:', error);
        showToast(error.message || 'Restock gagal', 'error');
    }
});
</script>
@endpush

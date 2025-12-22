@extends('layouts.main')

@section('title', 'Items - Kopontren Kasir')

@section('content')
<div class="container mx-auto px-4 py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-900">Manajemen Item</h2>
        <button id="addItemBtn" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-2"></i>Tambah Item
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input type="text" id="searchItems" class="input input-sm" placeholder="Cari item...">
            <select id="filterType" class="input input-sm">
                <option value="">Semua Tipe</option>
                <option value="normal">Normal</option>
                <option value="bundle">Bundle</option>
            </select>
            <select id="filterActive" class="input input-sm">
                <option value="">Semua Status</option>
                <option value="1">Aktif</option>
                <option value="0">Tidak Aktif</option>
            </select>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card">
        <div id="itemsTable" class="overflow-x-auto">
            <div class="text-center py-8">
                <div class="spinner mx-auto mb-3"></div>
                <p class="text-gray-500">Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Item Modal -->
<div id="itemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
        <h3 id="modalTitle" class="text-xl font-bold mb-4">Tambah Item</h3>
        
        <form id="itemForm" class="space-y-4">
            <input type="hidden" id="itemId">
            
            <div>
                <label class="block text-sm font-medium mb-1">Nama Item *</label>
                <input type="text" id="itemName" class="input" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tipe *</label>
                <select id="itemType" class="input" required>
                    <option value="normal">Normal</option>
                    <option value="bundle">Bundle</option>
                </select>
            </div>

            <div id="normalFields">
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">SKU/Barcode</label>
                    <input type="text" id="itemSku" class="input">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Harga Jual *</label>
                    <input type="number" id="itemPrice" class="input" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Low Stock Alert</label>
                    <input type="number" id="itemLowStock" class="input" value="10">
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="itemActive" checked class="rounded">
                    <span class="text-sm">Aktif</span>
                </label>
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="itemQuick" class="rounded">
                    <span class="text-sm">Quick Item</span>
                </label>
            </div>

            <div class="flex gap-2 pt-4">
                <button type="button" id="cancelItemBtn" class="btn btn-secondary flex-1">Batal</button>
                <button type="submit" class="btn btn-primary flex-1">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let items = [];

async function loadItems() {
    try {
        const response = await window.api.get('/items');
        items = response.data || response;
        renderItems();
    } catch (error) {
        console.error('Load items error:', error);
        showToast('Gagal memuat data', 'error');
    }
}

function renderItems() {
    const searchQuery = document.getElementById('searchItems').value.toLowerCase();
    const filterType = document.getElementById('filterType').value;
    const filterActive = document.getElementById('filterActive').value;

    let filtered = items.filter(item => {
        const matchSearch = item.name.toLowerCase().includes(searchQuery);
        const matchType = !filterType || item.type === filterType;
        const matchActive = !filterActive || item.is_active.toString() === filterActive;
        return matchSearch && matchType && matchActive;
    });

    const table = document.getElementById('itemsTable');
    
    if (filtered.length === 0) {
        table.innerHTML = '<p class="text-gray-400 text-center py-8">Tidak ada item</p>';
        return;
    }

    table.innerHTML = `
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                ${filtered.map(item => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-semibold">${item.name}</div>
                            ${item.quick_item ? '<span class="badge badge-warning text-xs">Quick</span>' : ''}
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge ${item.type === 'bundle' ? 'badge-info' : 'badge-success'}">
                                ${item.type}
                            </span>
                        </td>
                        <td class="px-4 py-3">${formatRupiah(item.price_sell)}</td>
                        <td class="px-4 py-3">${item.stock_cached || 0}</td>
                        <td class="px-4 py-3">
                            <span class="badge ${item.is_active ? 'badge-success' : 'badge-danger'}">
                                ${item.is_active ? 'Aktif' : 'Nonaktif'}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button onclick="editItem(${item.id})" class="text-blue-600 hover:text-blue-800 px-2">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

// Event listeners
document.getElementById('searchItems').addEventListener('input', renderItems);
document.getElementById('filterType').addEventListener('change', renderItems);
document.getElementById('filterActive').addEventListener('change', renderItems);

document.getElementById('addItemBtn').addEventListener('click', () => {
    document.getElementById('modalTitle').textContent = 'Tambah Item';
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
    document.getElementById('itemActive').checked = true;
    document.getElementById('itemModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
});

document.getElementById('cancelItemBtn').addEventListener('click', () => {
    document.getElementById('itemModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
});

function editItem(id) {
    const item = items.find(i => i.id === id);
    if (!item) return;

    document.getElementById('modalTitle').textContent = 'Edit Item';
    document.getElementById('itemId').value = item.id;
    document.getElementById('itemName').value = item.name;
    document.getElementById('itemType').value = item.type;
    document.getElementById('itemSku').value = item.sku || '';
    document.getElementById('itemPrice').value = item.price_sell;
    document.getElementById('itemLowStock').value = item.low_stock_threshold || 10;
    document.getElementById('itemActive').checked = item.is_active;
    document.getElementById('itemQuick').checked = item.quick_item;
    
    document.getElementById('itemModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

document.getElementById('itemForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = document.getElementById('itemId').value;
    const data = {
        name: document.getElementById('itemName').value,
        type: document.getElementById('itemType').value,
        sku: document.getElementById('itemSku').value || null,
        price_sell: parseInt(document.getElementById('itemPrice').value),
        low_stock_threshold: parseInt(document.getElementById('itemLowStock').value),
        is_active: document.getElementById('itemActive').checked,
        quick_item: document.getElementById('itemQuick').checked,
        quick_order: document.getElementById('itemQuick').checked ? 100 : 0
    };

    showLoading();
    
    try {
        if (id) {
            await window.api.put(`/items/${id}`, data);
        } else {
            await window.api.post('/items', data);
        }
        
        hideLoading();
        document.getElementById('itemModal').classList.add('hidden');
        document.body.classList.remove('modal-open');
        
        showToast(id ? 'Item berhasil diupdate' : 'Item berhasil ditambahkan', 'success');
        loadItems();
        
    } catch (error) {
        hideLoading();
        console.error('Save item error:', error);
        showToast(error.message || 'Gagal menyimpan item', 'error');
    }
});

// Init
loadItems();
</script>
@endpush

@extends('layouts.main')

@section('title', 'POS - Kopontren Kasir')

@section('content')
<div class="container mx-auto px-4 py-4 max-w-7xl">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Left Panel: Search & Items -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Search Bar -->
            <div class="card">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="input pl-12" 
                        placeholder="Scan barcode atau cari item..."
                        autofocus
                    >
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>

                <!-- Search Results -->
                <div id="searchResults" class="mt-4 space-y-2 max-h-64 overflow-y-auto hidden"></div>
            </div>

            <!-- Quick Items Grid -->
            <div class="card">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="fas fa-bolt text-yellow-500"></i>
                    Quick Items
                </h3>
                <div id="quickItemsGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <!-- Loading skeleton -->
                    <div class="animate-pulse bg-gray-200 h-24 rounded-lg"></div>
                    <div class="animate-pulse bg-gray-200 h-24 rounded-lg"></div>
                    <div class="animate-pulse bg-gray-200 h-24 rounded-lg"></div>
                    <div class="animate-pulse bg-gray-200 h-24 rounded-lg"></div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Cart -->
        <div class="lg:col-span-1">
            <div class="card sticky top-20">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center justify-between">
                    <span><i class="fas fa-shopping-cart mr-2"></i>Keranjang</span>
                    <button id="clearCartBtn" class="text-sm text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </h3>

                <!-- Cart Items -->
                <div id="cartItems" class="space-y-2 max-h-96 overflow-y-auto mb-4">
                    <p class="text-gray-400 text-center py-8 text-sm">Keranjang kosong</p>
                </div>

                <!-- Total -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-4">
                        <span class="font-bold text-lg">Total:</span>
                        <span id="cartTotal" class="font-bold text-2xl text-cyan-600">Rp 0</span>
                    </div>

                    <button id="checkoutBtn" class="btn btn-primary btn-block" disabled>
                        <i class="fas fa-check-circle mr-2"></i>
                        Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkoutModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-xl font-bold mb-4">Checkout</h3>
        
        <div class="mb-4">
            <p class="text-gray-600 mb-2">Total Pembayaran:</p>
            <p id="modalTotal" class="text-3xl font-bold text-cyan-600">Rp 0</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
            <select id="paymentMethod" class="input">
                <option value="cash">Cash</option>
                <option value="qris">QRIS</option>
                <option value="transfer">Transfer</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button id="cancelCheckoutBtn" class="btn btn-secondary flex-1">Batal</button>
            <button id="confirmCheckoutBtn" class="btn btn-primary flex-1">
                <i class="fas fa-check mr-2"></i>Konfirmasi
            </button>
        </div>
    </div>
</div>

<!-- Stock Error Modal -->
<div id="stockErrorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="text-center mb-4">
            <i class="fas fa-exclamation-circle text-red-500 text-5xl mb-3"></i>
            <h3 class="text-xl font-bold text-red-600">Stok Tidak Cukup!</h3>
        </div>
        
        <div id="stockErrorDetails" class="space-y-2 mb-4"></div>

        <button id="closeStockErrorBtn" class="btn btn-secondary btn-block">Tutup</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];
let allItems = [];

// Load Quick Items
async function loadQuickItems() {
    try {
        const response = await window.api.get('/items?quick=1');
        const items = response.data || response;
        
        const grid = document.getElementById('quickItemsGrid');
        grid.innerHTML = items.map(item => `
            <button onclick="addToCart(${JSON.stringify(item).replace(/"/g, '&quot;')})" 
                    class="bg-gradient-to-br from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white p-4 rounded-lg transition transform active:scale-95">
                <div class="font-bold text-sm mb-1">${item.name}</div>
                <div class="text-xs opacity-90">${formatRupiah(item.price_sell)}</div>
            </button>
        `).join('');
    } catch (error) {
        console.error('Failed to load quick items:', error);
    }
}

// Search Items
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');

const performSearch = debounce(async function(query) {
    if (query.length < 2) {
        searchResults.classList.add('hidden');
        return;
    }

    try {
        const response = await window.api.get(`/items?search=${encodeURIComponent(query)}`);
        const items = response.data || response;
        
        if (items.length === 0) {
            searchResults.innerHTML = '<p class="text-gray-400 text-sm text-center py-4">Tidak ada hasil</p>';
        } else {
            searchResults.innerHTML = items.map((item, index) => `
                <button onclick="addToCart(${JSON.stringify(item).replace(/"/g, '&quot;')}); clearSearch();" 
                        class="w-full text-left p-3 hover:bg-gray-50 rounded-lg border border-gray-200 transition"
                        id="searchResult${index}">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-semibold text-gray-900">${item.name}</div>
                            <div class="text-sm text-gray-500">Stock: ${item.stock_cached || 0}</div>
                        </div>
                        <div class="text-cyan-600 font-bold">${formatRupiah(item.price_sell)}</div>
                    </div>
                </button>
            `).join('');
        }
        
        searchResults.classList.remove('hidden');
        allItems = items;
    } catch (error) {
        console.error('Search error:', error);
    }
}, 300);

searchInput.addEventListener('input', (e) => {
    performSearch(e.target.value);
});

// Enter key handler
searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        const firstResult = document.getElementById('searchResult0');
        if (firstResult) {
            firstResult.click();
        }
    }
});

function clearSearch() {
    searchInput.value = '';
    searchResults.classList.add('hidden');
    searchInput.focus();
}

// Add to Cart
function addToCart(item) {
    const existingItem = cart.find(i => i.item_id === item.id);
    
    if (existingItem) {
        existingItem.qty++;
    } else {
        cart.push({
            item_id: item.id,
            name: item.name,
            unit_price: item.price_sell,
            qty: 1,
            max_stock: item.stock_cached || 0
        });
    }
    
    renderCart();
}

// Render Cart
function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="text-gray-400 text-center py-8 text-sm">Keranjang kosong</p>';
        cartTotal.textContent = 'Rp 0';
        checkoutBtn.disabled = true;
        return;
    }
    
    let total = 0;
    cartItems.innerHTML = cart.map((item, index) => {
        const subtotal = item.qty * item.unit_price;
        total += subtotal;
        
        return `
            <div class="flex items-center gap-3 pb-3 border-b">
                <div class="flex-1">
                    <div class="font-semibold text-sm">${item.name}</div>
                    <div class="text-xs text-gray-500">${formatRupiah(item.unit_price)}</div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="updateQty(${index}, -1)" class="w-8 h-8 bg-gray-200 rounded-full hover:bg-gray-300">
                        <i class="fas fa-minus text-xs"></i>
                    </button>
                    <span class="w-8 text-center font-bold">${item.qty}</span>
                    <button onclick="updateQty(${index}, 1)" class="w-8 h-8 bg-cyan-500 text-white rounded-full hover:bg-cyan-600">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                </div>
                <button onclick="removeItem(${index})" class="text-red-500 hover:text-red-700 px-2">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </div>
        `;
    }).join('');
    
    cartTotal.textContent = formatRupiah(total);
    checkoutBtn.disabled = false;
}

function updateQty(index, delta) {
    cart[index].qty += delta;
    if (cart[index].qty <= 0) {
        cart.splice(index, 1);
    }
    renderCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

document.getElementById('clearCartBtn').addEventListener('click', () => {
    if (cart.length > 0 && confirm('Kosongkan keranjang?')) {
        cart = [];
        renderCart();
    }
});

// Checkout
document.getElementById('checkoutBtn').addEventListener('click', () => {
    const total = cart.reduce((sum, item) => sum + (item.qty * item.unit_price), 0);
    document.getElementById('modalTotal').textContent = formatRupiah(total);
    document.getElementById('checkoutModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
});

document.getElementById('cancelCheckoutBtn').addEventListener('click', () => {
    document.getElementById('checkoutModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
});

document.getElementById('confirmCheckoutBtn').addEventListener('click', async () => {
    const paymentMethod = document.getElementById('paymentMethod').value;
    
    showLoading();
    
    try {
        const saleData = {
            payment_method: paymentMethod,
            lines: cart.map(item => ({
                item_id: item.item_id,
                qty: item.qty,
                unit_price: item.unit_price
            }))
        };

        if (navigator.onLine) {
            // Online: kirim ke server
            await window.api.post('/sales', saleData);
        } else {
            // Offline: simpan di IndexedDB
            await window.offlineDB.saveTransaction({
                items: saleData.lines,
                total: cart.reduce((sum, item) => sum + (item.qty * item.unit_price), 0),
                payment_method: paymentMethod
            });
        }
        
        hideLoading();
        document.getElementById('checkoutModal').classList.add('hidden');
        document.body.classList.remove('modal-open');
        
        showToast('Transaksi berhasil!', 'success');
        cart = [];
        renderCart();
        
    } catch (error) {
        hideLoading();
        console.error('Checkout error:', error);
        
        // Handle stock error
        if (error.status === 422 && error.details) {
            showStockError(error.details);
            document.getElementById('checkoutModal').classList.add('hidden');
        } else {
            showToast(error.message || 'Transaksi gagal', 'error');
        }
    }
});

function showStockError(details) {
    const container = document.getElementById('stockErrorDetails');
    container.innerHTML = details.map(item => `
        <div class="bg-red-50 border border-red-200 rounded p-3">
            <div class="font-semibold text-red-900">${item.name}</div>
            <div class="text-sm text-red-700">
                Stok tersedia: ${item.stock} | Dibutuhkan: ${item.need}
            </div>
        </div>
    `).join('');
    
    document.getElementById('stockErrorModal').classList.remove('hidden');
}

document.getElementById('closeStockErrorBtn').addEventListener('click', () => {
    document.getElementById('stockErrorModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
});

// Init
document.addEventListener('DOMContentLoaded', () => {
    loadQuickItems();
});
</script>
@endpush

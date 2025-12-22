<nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-200 z-50 md:hidden shadow-lg">
    <div class="grid grid-cols-5 gap-0.5">
        <a href="/pos" class="nav-item" data-page="pos">
            <i class="fas fa-cash-register text-xl"></i>
            <span class="text-xs mt-1 font-medium">POS</span>
        </a>
        <a href="/restock" class="nav-item" data-page="restock">
            <i class="fas fa-truck-loading text-xl"></i>
            <span class="text-xs mt-1 font-medium">Restock</span>
        </a>
        <a href="/items" class="nav-item" data-page="items">
            <i class="fas fa-box text-xl"></i>
            <span class="text-xs mt-1 font-medium">Items</span>
        </a>
        <a href="/stock" class="nav-item" data-page="stock">
            <i class="fas fa-warehouse text-xl"></i>
            <span class="text-xs mt-1 font-medium">Stock</span>
        </a>
        <a href="/owner/dashboard" class="nav-item" data-page="owner" id="ownerNavItem" style="display: none;">
            <i class="fas fa-chart-line text-xl"></i>
            <span class="text-xs mt-1 font-medium">Owner</span>
        </a>
    </div>
</nav>

<style>
.nav-item {
    @apply flex flex-col items-center justify-center py-3 text-gray-500 transition-all duration-200 relative;
}

.nav-item:active {
    @apply scale-95;
}

.nav-item.active {
    @apply text-cyan-600;
}

.nav-item.active::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 32px;
    height: 3px;
    background: linear-gradient(90deg, #06b6d4, #0284c7);
    border-radius: 0 0 4px 4px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ownerNavItem = document.getElementById('ownerNavItem');
    const navItems = document.querySelectorAll('.nav-item');
    const currentPath = window.location.pathname;

    // Show owner nav for owner role
    const checkOwner = setInterval(() => {
        if (!window.authGuard.isLoading) {
            if (window.authGuard.isOwner()) {
                ownerNavItem.style.display = 'flex';
            }
            clearInterval(checkOwner);
        }
    }, 100);

    // Set active nav item
    navItems.forEach(item => {
        const page = item.getAttribute('data-page');
        if (currentPath.includes(page) || (page === 'pos' && currentPath === '/')) {
            item.classList.add('active');
        }
    });
});
</script>

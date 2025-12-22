<div id="offline-indicator" class="hidden">
    <div class="flex items-center justify-center gap-2">
        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
        <span><i class="fas fa-wifi mr-1.5"></i>Mode Offline - Data akan disinkronkan otomatis saat online</span>
    </div>
</div>

<style>
#offline-indicator {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #f59e0b, #ea580c);
    color: white;
    padding: 0.75rem;
    text-align: center;
    z-index: 9999;
    font-size: 0.875rem;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    transform: translateY(-100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

#offline-indicator.show {
    display: block;
    transform: translateY(0);
}
</style>

<script>
function updateOnlineStatus() {
    const indicator = document.getElementById('offline-indicator');
    if (navigator.onLine) {
        indicator?.classList.remove('show');
    } else {
        indicator?.classList.add('show');
    }
}

window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);
document.addEventListener('DOMContentLoaded', updateOnlineStatus);
</script>

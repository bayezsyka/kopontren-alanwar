<header class="bg-white/95 backdrop-blur-md shadow-sm sticky top-0 z-40 border-b border-gray-100">
    <div class="px-4 py-3.5">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <!-- Logo & Title -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center shadow-md">
                    <i class="fas fa-store text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">Kopontren Kasir</h1>
                    <div id="userInfo" class="text-xs text-gray-500"></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <!-- Mode Switch (Owner only) -->
                <div id="modeSwitcher" style="display: none;" class="hidden sm:block">
                    <select id="modeSelect" class="input-sm text-xs bg-gray-50 border-gray-200 focus:border-cyan-500 focus:ring-cyan-500/50 rounded-lg">
                        <option value="kasir">🧑‍💼 Mode Kasir</option>
                        <option value="owner">👔 Mode Owner</option>
                    </select>
                </div>

                <!-- Logout Button -->
                <button id="logoutBtn" class="w-9 h-9 flex items-center justify-center text-red-500 hover:text-white hover:bg-red-500 rounded-lg transition-all duration-200">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const userInfoEl = document.getElementById('userInfo');
    const modeSwitcher = document.getElementById('modeSwitcher');
    const modeSelect = document.getElementById('modeSelect');
    const logoutBtn = document.getElementById('logoutBtn');

    // Display user info
    function updateHeader() {
        const user = window.authGuard.getUser();
        if (user) {
            const roleBadge = user.role === 'owner' ? 
                '<span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded text-xs">Owner</span>' :
                '<span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs">Kasir</span>';
            
            userInfoEl.innerHTML = `${user.name} ${roleBadge}`;

            // Show mode switcher for owner
            if (user.role === 'owner') {
                modeSwitcher.style.display = 'block';
                modeSelect.value = user.ui_mode;
            }
        }
    }

    // Mode switch handler
    modeSelect?.addEventListener('change', async (e) => {
        const newMode = e.target.value;
        await window.authGuard.switchMode(newMode);
    });

    // Logout handler
    logoutBtn?.addEventListener('click', async () => {
        if (confirm('Yakin ingin logout?')) {
            await window.api.logout();
        }
    });

    // Wait for auth guard to load
    const checkAuth = setInterval(() => {
        if (!window.authGuard.isLoading && window.authGuard.getUser()) {
            updateHeader();
            clearInterval(checkAuth);
        }
    }, 100);
});
</script>

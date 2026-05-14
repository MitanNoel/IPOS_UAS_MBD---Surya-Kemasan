<?php
/**
 * Navigation Sidebar Component
 * Include: <?php require_once 'includes/navbar.php'; ?>
 */

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '');
$currentPath = ltrim($scriptName, '/');

if (!function_exists('nav_is_active')) {
    function nav_is_active($currentPath, array $targets) {
        foreach ($targets as $target) {
            if (substr($target, -1) === '/') {
                if (strpos($currentPath, $target) === 0) {
                    return true;
                }
            } elseif ($currentPath === $target) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('nav_mobile_class')) {
    function nav_mobile_class() {
        return 'fixed left-0 top-0 z-50 w-64 bg-gradient-to-b from-gray-900 to-gray-800 text-white min-h-screen shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out md:translate-x-0';
    }
}
?>
<button id="navToggle" type="button" class="fixed top-4 left-4 z-[60] inline-flex items-center justify-center w-11 h-11 rounded-xl bg-gray-900 text-white shadow-lg md:hidden" aria-label="Buka menu">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>
<div id="navOverlay" class="fixed inset-0 z-40 bg-black/50 hidden md:hidden"></div>
<nav id="sidebarNav" class="<?= nav_mobile_class() ?>">
    <!-- Logo Section -->
    <div class="px-6 py-6 border-b border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold">Sistem Toko</h1>
                <p class="text-xs text-gray-400">Manajemen Inventaris</p>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="px-6 py-4 border-b border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center text-sm font-bold">
                <?= substr($user_name ?? 'U', 0, 1) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold truncate max-w-[7rem]" title="<?= htmlspecialchars($user_name ?? '') ?>"><?= htmlspecialchars($user_name ?? '') ?></p>
                <p class="text-xs text-gray-400 capitalize"><?= htmlspecialchars($role ?? '') ?></p>
            </div>
            <a href="../auth/logout.php" class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg text-red-300 hover:text-white hover:bg-red-600 transition-colors" title="Logout" aria-label="Logout">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="px-3 py-6 pb-10 space-y-2 flex-1 overflow-y-auto">
        <!-- Dashboard -->
        <a href="../dashboard/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors <?= nav_is_active($currentPath, ['dashboard/index.php']) ? 'bg-purple-600 shadow-md' : '' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4l4 2m-8-2l4-2"></path>
            </svg>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Products Section -->
        <div class="pt-2">
            <p class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Produk & Stok</p>
            <?php if (is_admin()): ?>
            <a href="../public/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors <?= nav_is_active($currentPath, ['public/']) ? 'bg-purple-600 shadow-md' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m0 0l8 4m-8-4v10l8 4m0-10l8 4m-8-4v10M8 11l4 2m4-2l4-2"></path>
                </svg>
                <span>Data Produk</span>
            </a>
            <?php endif; ?>
            <a href="../inventory/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors <?= nav_is_active($currentPath, ['inventory/']) ? 'bg-purple-600 shadow-md' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-6 8h6m-8 6h10M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z"></path>
                </svg>
                <span>Inventori Stok</span>
            </a>
        </div>

        <!-- Transactions Section -->
        <div class="pt-2">
            <p class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Transaksi</p>
            <a href="../sales/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors <?= nav_is_active($currentPath, ['sales/index.php']) ? 'bg-purple-600 shadow-md' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v4H3V3zm0 8h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V11z"></path>
                </svg>
                <span>Penjualan</span>
            </a>
            <a href="../sales/pos.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors <?= nav_is_active($currentPath, ['sales/pos.php']) ? 'bg-purple-600 shadow-md' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 6h.01M7 14h.01M7 18h.01M11 6h.01M11 14h.01M11 18h.01M15 6h.01M15 14h.01M15 18h.01"></path>
                </svg>
                <span>POS</span>
            </a>
            <?php if (is_admin()): ?>
            <a href="../purchases/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors <?= nav_is_active($currentPath, ['purchases/']) ? 'bg-purple-600 shadow-md' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M7 3v4M17 3v4M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z"></path>
                </svg>
                <span>Pembelian</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Admin Section -->
        <?php if (is_admin()): ?>
        <div class="pt-2">
            <p class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Manajemen (Admin)</p>
            <a href="../users/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors <?= nav_is_active($currentPath, ['users/']) ? 'bg-purple-600 shadow-md' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 8.048M12 4.354L9.172 7.172m5.656-5.656l2.828 2.828m.176 8.48a4 4 0 110-8.048m0 8.048l2.828 2.828m-2.828-2.828l-2.828 2.828"></path>
                </svg>
                <span>Pengguna</span>
            </a>
            <a href="../suppliers/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors <?= nav_is_active($currentPath, ['suppliers/']) ? 'bg-purple-600 shadow-md' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                </svg>
                <span>Supplier</span>
            </a>
        </div>
        <?php endif; ?>

        
    </div>
</nav>

<style>
    .main-content {
        margin-left: 16rem; /* w-64 = 16rem */
    }

    @media (max-width: 767px) {
        .main-content {
            margin-left: 0 !important;
            padding: 1rem !important;
        }

        #sidebarNav {
            width: min(18rem, 85vw);
        }

        #sidebarNav.nav-open {
            transform: translateX(0);
        }

        body.nav-open {
            overflow: hidden;
        }
    }
</style>

<script>
(function() {
    const sidebar = document.getElementById('sidebarNav');
    const overlay = document.getElementById('navOverlay');
    const toggle = document.getElementById('navToggle');

    if (!sidebar || !overlay || !toggle) {
        return;
    }

    function openNav() {
        sidebar.classList.add('nav-open');
        overlay.classList.remove('hidden');
        document.body.classList.add('nav-open');
    }

    function closeNav() {
        sidebar.classList.remove('nav-open');
        overlay.classList.add('hidden');
        document.body.classList.remove('nav-open');
    }

    toggle.addEventListener('click', function() {
        if (sidebar.classList.contains('nav-open')) {
            closeNav();
        } else {
            openNav();
        }
    });

    overlay.addEventListener('click', closeNav);

    sidebar.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                closeNav();
            }
        });
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            closeNav();
        }
    });
})();
</script>

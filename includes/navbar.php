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
        return 'fixed left-0 top-0 z-50 w-64 bg-slate-900 text-slate-100 min-h-screen shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out md:translate-x-0 flex flex-col border-r border-slate-800';
    }
}
?>
<!-- Mobile Menu Trigger -->
<button id="navToggle" type="button" class="fixed top-4 left-4 z-[60] inline-flex items-center justify-center w-11 h-11 rounded-xl bg-slate-900 text-slate-100 shadow-lg md:hidden hover:bg-slate-800 transition-colors" aria-label="Buka menu">
    <i data-lucide="menu" class="w-5 h-5"></i>
</button>
<div id="navOverlay" class="fixed inset-0 z-40 bg-slate-950/60 hidden md:hidden backdrop-blur-sm"></div>

<nav id="sidebarNav" class="<?= nav_mobile_class() ?>">
    <!-- Logo Section -->
    <div class="px-6 py-5 border-b border-slate-850 bg-slate-950/20">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-brand-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-brand-500/10">
                <i data-lucide="store" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-white tracking-tight">Surya Kemasan</h1>
                <p class="text-xs text-brand-400 font-medium">Sistem Manajemen</p>
            </div>
        </div>
    </div>

    <!-- User Info Section -->
    <div class="px-6 py-4 border-b border-slate-850 bg-slate-950/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-slate-800 border border-slate-700 rounded-xl flex items-center justify-center text-brand-400 font-bold text-sm">
                <?= strtoupper(substr($user_name ?? 'U', 0, 2)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate" title="<?= htmlspecialchars($user_name ?? '') ?>">
                    <?= htmlspecialchars($user_name ?? '') ?>
                </p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    <p class="text-xs text-slate-400 capitalize font-medium"><?= htmlspecialchars($role ?? '') ?></p>
                </div>
            </div>
            <a href="../auth/logout.php" class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-400 hover:text-red-400 hover:bg-slate-800 transition-all" title="Logout" aria-label="Logout">
                <i data-lucide="log-out" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="px-4 py-4 space-y-5 flex-1 overflow-y-auto">
        <!-- Main Navigation Group -->
        <div class="space-y-1">
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Menu Utama</p>
            
            <!-- Dashboard -->
            <a href="../dashboard/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all <?= nav_is_active($currentPath, ['dashboard/index.php']) ? 'bg-brand-600 text-white font-medium shadow-md shadow-brand-600/10' : '' ?>">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span class="text-sm">Dashboard</span>
            </a>
        </div>

        <!-- Transactions Navigation Group -->
        <div class="space-y-1">
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Transaksi Kasir</p>
            
            <!-- POS -->
            <a href="../sales/pos.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all <?= nav_is_active($currentPath, ['sales/pos.php']) ? 'bg-brand-600 text-white font-medium shadow-md shadow-brand-600/10' : '' ?>">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span class="text-sm">Point of Sale (POS)</span>
            </a>
            
            <!-- Penjualan -->
            <a href="../sales/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all <?= nav_is_active($currentPath, ['sales/index.php']) ? 'bg-brand-600 text-white font-medium shadow-md shadow-brand-600/10' : '' ?>">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span class="text-sm">Riwayat Penjualan</span>
            </a>
        </div>

        <!-- Inventory Navigation Group -->
        <div class="space-y-1">
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Produk & Inventori</p>
            
            <!-- Inventori Stok -->
            <a href="../inventory/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all <?= nav_is_active($currentPath, ['inventory/']) ? 'bg-brand-600 text-white font-medium shadow-md shadow-brand-600/10' : '' ?>">
                <i data-lucide="boxes" class="w-4 h-4"></i>
                <span class="text-sm">Inventori Stok</span>
            </a>

            <!-- Data Produk (Admin Only) -->
            <?php if (is_admin()): ?>
            <a href="../public/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all <?= nav_is_active($currentPath, ['public/']) ? 'bg-brand-600 text-white font-medium shadow-md shadow-brand-600/10' : '' ?>">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span class="text-sm">Data Produk</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Master Data Navigation Group (Admin Only) -->
        <?php if (is_admin()): ?>
        <div class="space-y-1">
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Master Data (Admin)</p>
            
            <!-- Pembelian -->
            <a href="../purchases/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all <?= nav_is_active($currentPath, ['purchases/']) ? 'bg-brand-600 text-white font-medium shadow-md shadow-brand-600/10' : '' ?>">
                <i data-lucide="package-plus" class="w-4 h-4"></i>
                <span class="text-sm">Pembelian</span>
            </a>
            
            <!-- Supplier -->
            <a href="../suppliers/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all <?= nav_is_active($currentPath, ['suppliers/']) ? 'bg-brand-600 text-white font-medium shadow-md shadow-brand-600/10' : '' ?>">
                <i data-lucide="truck" class="w-4 h-4"></i>
                <span class="text-sm">Supplier</span>
            </a>
            
            <!-- Kategori -->
            <a href="../categories/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all <?= nav_is_active($currentPath, ['categories/']) ? 'bg-brand-600 text-white font-medium shadow-md shadow-brand-600/10' : '' ?>">
                <i data-lucide="folder" class="w-4 h-4"></i>
                <span class="text-sm">Kategori Produk</span>
            </a>
            
            <!-- Pengguna -->
            <a href="../users/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all <?= nav_is_active($currentPath, ['users/']) ? 'bg-brand-600 text-white font-medium shadow-md shadow-brand-600/10' : '' ?>">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span class="text-sm">Pengguna Toko</span>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer Copyright -->
    <div class="p-4 border-t border-slate-850 bg-slate-950/20 text-center">
        <p class="text-[10px] text-slate-500">&copy; 2026 IPOS Toko Surya Kemasan v1.0</p>
    </div>
</nav>

<style>
    .main-content {
        margin-left: 16rem; /* w-64 = 16rem */
    }

    @media (max-width: 767px) {
        .main-content {
            margin-left: 0 !important;
            padding: 1.25rem !important;
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


<?php
require_once '../auth/check_auth.php';
require_once '../config/database.php';

$search = trim($_GET['q'] ?? '');
$stockFilter = $_GET['stock'] ?? 'all';

try {
    $sql = "SELECT 
                b.id_barang,
                b.nama_barang,
                b.harga_jual,
                b.harga_beli,
                k.nama_kategori,
                COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) as qty_in,
                COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0) as qty_out,
                (COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) - 
                COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0)) as stok_actual,
                GREATEST((COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) - 
                COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0)), 0) as stok,
                (b.harga_jual - b.harga_beli) as margin_item
            FROM barang b
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori";

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = '(b.id_barang LIKE :search OR b.nama_barang LIKE :search OR k.nama_kategori LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY b.nama_barang ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Filter results programmatically to support dynamic stock queries
    $items = array_values(array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), function ($item) use ($stockFilter) {
        $stok_actual = (int) $item['stok_actual'];
        if ($stockFilter === 'low') {
            return $stok_actual < 6 && $stok_actual > 0;
        }
        if ($stockFilter === 'empty') {
            return $stok_actual <= 0;
        }
        if ($stockFilter === 'safe') {
            return $stok_actual >= 5;
        }
        return true;
    }));

    $totalItems = count($items);
    $totalStock = array_sum(array_map(static fn($item) => (int) $item['stok'], $items));
    $emptyStock = 0;
    $lowStock = 0;
    $safeStock = 0;
    $stockValue = 0;

    foreach ($items as $item) {
        $stok_actual = (int) $item['stok_actual'];
        $stock = (int) $item['stok'];
        $stockValue += $stock * (float) $item['harga_beli'];
        if ($stok_actual <= 0) {
            $emptyStock++;
        } elseif ($stok_actual < 6) {
            $lowStock++;
        } else {
            $safeStock++;
        }
    }

    $stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM kategori");
    $totalCategories = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total_categories'];

    $stmt = $pdo->query("SELECT COALESCE(SUM(qty), 0) as total_in FROM detail_pembelian");
    $totalIn = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total_in'];

    $stmt = $pdo->query("SELECT COALESCE(SUM(qty), 0) as total_out FROM detail_penjualan");
    $totalOut = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total_out'];

    $topStockSql = "SELECT 
                        b.nama_barang,
                        k.nama_kategori,
                        (COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) - 
                        COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0)) as stok_actual,
                        GREATEST((COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) - 
                        COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0)), 0) as stok
                    FROM barang b
                    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                    ORDER BY stok DESC, b.nama_barang ASC
                    LIMIT 5";
    $stmt = $pdo->query($topStockSql);
    $topStock = array_values(array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), static fn($item) => (int) $item['stok_actual'] > 0));
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Inventori Stok | IPOS Toko Surya Kemasan</title>
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="main-content px-4 py-8 fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                        <span class="w-2.5 h-7 bg-brand-600 rounded-full inline-block"></span>
                        Inventori Stok
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">Pantau perputaran stok barang masuk dan keluar toko secara real-time.</p>
                </div>
                
                <!-- Search & Filters Form -->
                <form method="GET" class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                    <div class="relative flex-1 sm:flex-initial">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input 
                            type="text" 
                            name="q" 
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Cari nama barang atau kategori..."
                            class="w-full sm:w-64 pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 text-xs transition-all"
                        >
                    </div>
                    <select 
                        name="stock"
                        class="px-3 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 text-xs transition-all"
                    >
                        <option value="all" <?= $stockFilter === 'all' ? 'selected' : '' ?>>Semua Level Stok</option>
                        <option value="safe" <?= $stockFilter === 'safe' ? 'selected' : '' ?>>Stok Aman (>= 5)</option>
                        <option value="low" <?= $stockFilter === 'low' ? 'selected' : '' ?>>Stok Rendah (< 6)</option>
                        <option value="empty" <?= $stockFilter === 'empty' ? 'selected' : '' ?>>Stok Habis (<= 0)</option>
                    </select>
                    <button type="submit" class="ipos-btn-primary text-xs py-2">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter
                    </button>
                </form>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
                    <div class="p-3 bg-brand-50 text-brand-650 rounded-xl">
                        <i data-lucide="package" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Total Jenis Produk</p>
                        <p class="text-xl font-extrabold text-slate-800 mt-0.5"><?= $totalItems ?> <span class="text-xs font-normal text-slate-450">Item</span></p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
                    <div class="p-3 bg-sky-50 text-sky-650 rounded-xl">
                        <i data-lucide="boxes" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Total Kuantitas Stok</p>
                        <p class="text-xl font-extrabold text-slate-800 mt-0.5"><?= $totalStock ?> <span class="text-xs font-normal text-slate-450">Pcs</span></p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Item Stok Rendah</p>
                        <p class="text-xl font-extrabold text-slate-800 mt-0.5"><?= $lowStock ?> <span class="text-xs font-normal text-slate-450">Item</span></p>
                    </div>
                </div>

                <!-- Dynaimc Card: Show Inventory Value to Admin, Safe Stocks Count to Cashier -->
                <?php if (is_admin()): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <i data-lucide="wallet" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Nilai Aset Stok</p>
                        <p class="text-xl font-extrabold text-slate-800 mt-0.5">Rp <?= number_format($stockValue, 0, ',', '.') ?></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <i data-lucide="check-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Item Stok Aman</p>
                        <p class="text-xl font-extrabold text-slate-800 mt-0.5"><?= $safeStock ?> <span class="text-xs font-normal text-slate-450">Item</span></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Movement Summary & Top Stock Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Movement summary card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-1.5"><i data-lucide="activity" class="w-4 h-4 text-brand-600"></i> Ringkasan Alur Barang</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-100/50 rounded-xl">
                            <span class="text-xs text-slate-500 font-medium">Total Barang Masuk</span>
                            <span class="font-bold text-emerald-600 text-xs"><?= $totalIn ?> unit</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-100/50 rounded-xl">
                            <span class="text-xs text-slate-500 font-medium">Total Barang Terjual</span>
                            <span class="font-bold text-red-500 text-xs"><?= $totalOut ?> unit</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-100/50 rounded-xl">
                            <span class="text-xs text-slate-500 font-medium">Item Habis Stok (0 pcs)</span>
                            <span class="font-bold text-slate-800 text-xs"><?= $emptyStock ?></span>
                        </div>
                    </div>
                </div>

                <!-- Top Stock Table -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <i data-lucide="trophy" class="w-4 h-4 text-amber-500"></i>
                            Stok Terbanyak (Top 5)
                        </h2>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase">Real-time</span>
                    </div>
                    <?php if (empty($topStock)): ?>
                    <p class="text-slate-400 text-xs py-10 text-center">Belum ada data barang.</p>
                    <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($topStock as $item): ?>
                        <div class="rounded-xl border border-slate-100 p-4 bg-slate-50/50 flex flex-col justify-between">
                            <div>
                                <p class="font-bold text-slate-800 text-xs truncate" title="<?= htmlspecialchars($item['nama_barang']) ?>"><?= htmlspecialchars($item['nama_barang']) ?></p>
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-bold tracking-tight uppercase inline-block mt-1">
                                    <?= htmlspecialchars($item['nama_kategori'] ?? 'Umum') ?>
                                </span>
                            </div>
                            <div class="mt-4 flex items-center justify-between pt-2 border-t border-slate-100/60">
                                <span class="text-[10px] text-slate-400">Tersedia</span>
                                <span class="font-extrabold text-emerald-600 text-xs"><?= (int) $item['stok'] ?> unit</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Inventory Grid Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-150 bg-slate-50/60 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Daftar Inventori Barang</h2>
                        <p class="text-xs text-slate-450 mt-0.5 font-medium">Stok dinamis berdasarkan transaksi penjualan & pembelian</p>
                    </div>
                    <div class="text-xs text-slate-400 font-bold bg-white border px-2.5 py-1 rounded-lg shadow-sm"><?= count($items) ?> item ditemukan</div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Informasi Produk</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Barang Masuk</th>
                                <th class="px-6 py-4">Barang Keluar</th>
                                <th class="px-6 py-4">Kuantitas Stok</th>
                                <th class="px-6 py-4">Status</th>
                                <?php if (is_admin()): ?>
                                <th class="px-6 py-4 text-right">Nilai Stok (Harga Beli)</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="<?= is_admin() ? 7 : 6 ?>" class="px-6 py-16 text-center text-slate-400">
                                    <i data-lucide="archive-x" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                    Tidak ada barang yang cocok dengan filter pencarian.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($items as $item): ?>
                            <?php
                                $stok = (int) $item['stok'];
                                $stok_actual = (int) $item['stok_actual'];
                                $badgeClass = $stok_actual <= 0 ? 'bg-red-50 text-red-650 border-red-100' : ($stok_actual < 6 ? 'bg-amber-50 text-amber-650 border-amber-100' : 'bg-emerald-50 text-emerald-650 border-emerald-100');
                                $statusLabel = $stok_actual <= 0 ? 'Habis' : ($stok_actual < 6 ? 'Rendah' : 'Aman');
                            ?>
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($item['nama_barang']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">ID: <?= htmlspecialchars($item['id_barang']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-medium">
                                    <?= htmlspecialchars($item['nama_kategori'] ?? 'Tanpa Kategori') ?>
                                </td>
                                <td class="px-6 py-4 text-emerald-600 font-bold"><?= (int) $item['qty_in'] ?></td>
                                <td class="px-6 py-4 text-red-500 font-bold"><?= (int) $item['qty_out'] ?></td>
                                <td class="px-6 py-4 font-extrabold text-slate-900 text-sm"><?= $stok ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 border rounded-lg text-[10px] font-bold uppercase tracking-tight <?= $badgeClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <?php if (is_admin()): ?>
                                <td class="px-6 py-4 text-right font-extrabold text-slate-900 text-sm">
                                    Rp <?= number_format($stok * (float) $item['harga_beli'], 0, ',', '.') ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    lucide.createIcons();
    </script>
</body>

</html>
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
                COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) - 
                COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0) as stok,
                (b.harga_jual - b.harga_beli) as margin_item,
                CASE 
                    WHEN (
                        COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) -
                        COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0)
                    ) <= 0 THEN 'habis'
                    WHEN (
                        COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) -
                        COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0)
                    ) < 5 THEN 'rendah'
                    ELSE 'aman'
                END as status_stok
            FROM barang b
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori";

    $where = [];
    $having = [];
    $params = [];

    if ($search !== '') {
        $where[] = '(b.id_barang LIKE :search OR b.nama_barang LIKE :search OR k.nama_kategori LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    if ($stockFilter === 'low') {
        $having[] = 'stok < 5 AND stok > 0';
    } elseif ($stockFilter === 'empty') {
        $having[] = 'stok <= 0';
    } elseif ($stockFilter === 'safe') {
        $having[] = 'stok >= 5';
    }

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY stok ASC, b.nama_barang ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $items = array_values(array_filter($allItems, function ($item) use ($stockFilter) {
        $stok = (int) $item['stok'];
        if ($stockFilter === 'low') {
            return $stok < 5 && $stok > 0;
        }
        if ($stockFilter === 'empty') {
            return $stok <= 0;
        }
        if ($stockFilter === 'safe') {
            return $stok >= 5;
        }
        return true;
    }));

    $totalItems = count($allItems);
    $totalStock = array_sum(array_map(static fn($item) => (int) $item['stok'], $allItems));
    $emptyStock = 0;
    $lowStock = 0;
    $stockValue = 0;

    foreach ($allItems as $item) {
        $stock = (int) $item['stok'];
        $stockValue += $stock * (float) $item['harga_beli'];
        if ($stock <= 0) {
            $emptyStock++;
        } elseif ($stock < 5) {
            $lowStock++;
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
                        COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) - 
                        COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0) as stok
                    FROM barang b
                    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                    ORDER BY stok DESC, b.nama_barang ASC
                    LIMIT 5";
    $stmt = $pdo->query($topStockSql);
    $topStock = array_values(array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), static fn($item) => (int) $item['stok'] > 0));
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventori Stok | Sistem Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .main-content { margin-left: 16rem; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="main-content px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Inventori Stok</h1>
                    <p class="text-gray-500 mt-1">Pantau stok real-time berdasarkan pembelian dan penjualan tanpa mengubah struktur database.</p>
                </div>
                <form method="GET" class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari produk / kategori" class="w-full sm:w-72 pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-white">
                    </div>
                    <select name="stock" class="px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-white">
                        <option value="all" <?= $stockFilter === 'all' ? 'selected' : '' ?>>Semua Stok</option>
                        <option value="safe" <?= $stockFilter === 'safe' ? 'selected' : '' ?>>Stok Aman</option>
                        <option value="low" <?= $stockFilter === 'low' ? 'selected' : '' ?>>Stok Rendah</option>
                        <option value="empty" <?= $stockFilter === 'empty' ? 'selected' : '' ?>>Stok Habis</option>
                    </select>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition-colors">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Terapkan
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-600">Total Produk</h3>
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i data-lucide="package" class="w-5 h-5"></i></div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?= $totalItems ?></p>
                    <p class="text-xs text-gray-500 mt-2">Produk yang terdeteksi dalam inventori</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-600">Total Stok</h3>
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center"><i data-lucide="boxes" class="w-5 h-5"></i></div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?= $totalStock ?></p>
                    <p class="text-xs text-gray-500 mt-2">Total unit tersedia</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-600">Stok Rendah</h3>
                        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center"><i data-lucide="alert-triangle" class="w-5 h-5"></i></div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?= $lowStock ?></p>
                    <p class="text-xs text-gray-500 mt-2">Perlu segera direstok</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-600">Nilai Stok</h3>
                        <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center"><i data-lucide="wallet" class="w-5 h-5"></i></div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">Rp <?= number_format($stockValue, 0, ',', '.') ?></p>
                    <p class="text-xs text-gray-500 mt-2">Berdasarkan harga beli</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Ringkasan Pergerakan</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total Masuk</span>
                            <span class="font-bold text-emerald-600"><?= $totalIn ?> unit</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total Keluar</span>
                            <span class="font-bold text-red-600"><?= $totalOut ?> unit</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Kategori</span>
                            <span class="font-bold text-gray-900"><?= $totalCategories ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Stok Habis</span>
                            <span class="font-bold text-gray-900"><?= $emptyStock ?></span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Stok Tertinggi</h2>
                        <span class="text-xs text-gray-500">Top 5 produk dengan stok terbesar</span>
                    </div>
                    <?php if (empty($topStock)): ?>
                        <p class="text-gray-500 text-sm">Belum ada data stok positif.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($topStock as $item): ?>
                            <div class="rounded-lg border border-gray-100 p-4 bg-gray-50/60">
                                <p class="font-semibold text-gray-900"><?= htmlspecialchars($item['nama_barang']) ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($item['nama_kategori'] ?? 'Tanpa Kategori') ?></p>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Sisa stok</span>
                                    <span class="font-bold text-emerald-600"><?= (int) $item['stok'] ?> unit</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Daftar Inventori</h2>
                        <p class="text-sm text-gray-500">Semua barang dengan stok dinamis</p>
                    </div>
                    <div class="text-sm text-gray-500"><?= count($items) ?> hasil</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-white border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Produk</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Kategori</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Masuk</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Keluar</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Stok</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Nilai Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">Tidak ada barang yang cocok dengan filter.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($items as $item): ?>
                            <?php
                                $stok = (int) $item['stok'];
                                $badgeClass = $stok <= 0 ? 'bg-red-100 text-red-700' : ($stok < 5 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
                                $statusLabel = $stok <= 0 ? 'Habis' : ($stok < 5 ? 'Rendah' : 'Aman');
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($item['nama_barang']) ?></div>
                                    <div class="text-xs text-gray-400 font-mono">#<?= htmlspecialchars($item['id_barang']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($item['nama_kategori'] ?? 'Tanpa Kategori') ?></td>
                                <td class="px-6 py-4 text-emerald-600 font-semibold"><?= (int) $item['qty_in'] ?></td>
                                <td class="px-6 py-4 text-red-600 font-semibold"><?= (int) $item['qty_out'] ?></td>
                                <td class="px-6 py-4 font-bold text-gray-900"><?= $stok ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                    Rp <?= number_format($stok * (float) $item['harga_beli'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>

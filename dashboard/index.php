<?php
require_once '../auth/check_auth.php';
require_once '../config/database.php';

try {
    // Get KPI Data
    
    // Total Revenue (sum of all sales)
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as total_revenue FROM penjualan");
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];

    // Total Cost (sum of all purchases)
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as total_cost FROM pembelian");
    $total_cost = $stmt->fetch(PDO::FETCH_ASSOC)['total_cost'];

    // Total Profit
    $total_profit = $total_revenue - $total_cost;

    // Current Inventory Value (sum of stok value)
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(
            (
                COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = barang.id_barang), 0) -
                COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = barang.id_barang), 0)
            ) * harga_beli
        ), 0) as inventory_value
        FROM barang
    ");
    $inventory_value = $stmt->fetch(PDO::FETCH_ASSOC)['inventory_value'];

    // Total Products
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM barang");
    $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

    // Total Categories
    $stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM kategori");
    $total_categories = $stmt->fetch(PDO::FETCH_ASSOC)['total_categories'];

    // Recent Sales (last 5)
    $stmt = $pdo->query("
        SELECT p.id_penjualan, p.tanggal, u.nama_user, p.total,
               COUNT(dp.iddetail_penjualan) as jumlah_item
        FROM penjualan p
        LEFT JOIN user u ON p.id_user = u.id_user
        LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
        GROUP BY p.id_penjualan
        ORDER BY p.tanggal DESC
        LIMIT 5
    ");
    $recent_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top 5 Best Selling Products
    $stmt = $pdo->query("
        SELECT b.id_barang, b.nama_barang, k.nama_kategori,
               COALESCE(SUM(dp.qty), 0) as total_qty_sold,
               COALESCE(SUM(dp.subtotal), 0) as total_revenue
        FROM barang b
        LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
        LEFT JOIN detail_penjualan dp ON b.id_barang = dp.id_barang
        GROUP BY b.id_barang
        HAVING total_qty_sold > 0
        ORDER BY total_qty_sold DESC
        LIMIT 5
    ");
    $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Low Stock Alert (calculate stock < 5)
    $stmt = $pdo->query("\n        SELECT\n            b.id_barang,\n            b.nama_barang,\n            COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) as qty_in,\n            COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0) as qty_out,\n            COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) -\n            COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0) as stok\n        FROM barang b\n        HAVING stok < 5\n        ORDER BY stok ASC, b.nama_barang ASC\n    ");
    $low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly Revenue (this month)
    $current_date = date('Y-m-01');
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total), 0) as monthly_revenue
        FROM penjualan
        WHERE DATE_FORMAT(tanggal, '%Y-%m') = DATE_FORMAT(?, '%Y-%m')
    ");
    $stmt->execute([date('Y-m-d')]);
    $monthly_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['monthly_revenue'];

} catch (PDOException $e) {
    $error = "Error database: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistem Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .main-content {
            margin-left: 16rem;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <!-- Navbar -->
    <?php require_once '../includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content p-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-500 mt-1">Selamat datang, <strong><?= htmlspecialchars($user_name) ?></strong>! Berikut ringkasan bisnis Anda.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Tanggal: <strong><?= date('d F Y') ?></strong></p>
                    <p class="text-sm text-gray-500">Jam: <strong><?= date('H:i:s') ?></strong></p>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-600 font-semibold">Total Pendapatan</h3>
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">Rp <?= number_format($total_revenue, 0, ',', '.') ?></p>
                <p class="text-xs text-gray-500 mt-2">Dari semua transaksi penjualan</p>
            </div>

            <!-- Total Cost -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-600 font-semibold">Total Biaya</h3>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">Rp <?= number_format($total_cost, 0, ',', '.') ?></p>
                <p class="text-xs text-gray-500 mt-2">Dari semua pembelian</p>
            </div>

            <!-- Total Profit -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-600 font-semibold">Total Profit</h3>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">Rp <?= number_format($total_profit, 0, ',', '.') ?></p>
                <p class="text-xs text-gray-500 mt-2 <?= $total_profit >= 0 ? 'text-emerald-600' : 'text-red-600' ?>">
                    <?= $total_profit >= 0 ? '✓ Profit positif' : '✗ Rugi' ?>
                </p>
            </div>

            <!-- Inventory Value -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-600 font-semibold">Nilai Stok</h3>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m0 0l8 4m-8-4v10l8 4m0-10l8 4m-8-4v10M8 11l4 2m4-2l4-2"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">Rp <?= number_format($inventory_value, 0, ',', '.') ?></p>
                <p class="text-xs text-gray-500 mt-2">Berdasarkan harga beli</p>
            </div>
        </div>

        <!-- Secondary Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Monthly Revenue -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-600 font-semibold text-sm">Pendapatan Bulan Ini</h3>
                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full">Current</span>
                </div>
                <p class="text-2xl font-bold text-gray-900">Rp <?= number_format($monthly_revenue, 0, ',', '.') ?></p>
            </div>

            <!-- Total Products -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-600 font-semibold text-sm">Total Produk</h3>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full"><?= $total_products ?></span>
                </div>
                <p class="text-2xl font-bold text-gray-900"><?= $total_products ?> Item</p>
            </div>

            <!-- Categories -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-600 font-semibold text-sm">Total Kategori</h3>
                    <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full"><?= $total_categories ?></span>
                </div>
                <p class="text-2xl font-bold text-gray-900"><?= $total_categories ?> Kategori</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Sales -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900">Penjualan Terbaru</h2>
                        <a href="../public/index.php" class="text-sm text-purple-600 hover:text-purple-700 font-medium">Lihat Semua →</a>
                    </div>

                    <?php if (empty($recent_sales)): ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500">Belum ada data penjualan</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-600">ID Penjualan</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-600">Tanggal</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-600">Kasir</th>
                                    <th class="text-right py-3 px-4 font-semibold text-gray-600">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_sales as $sale): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4"><strong>#<?= $sale['id_penjualan'] ?></strong></td>
                                    <td class="py-3 px-4"><?= date('d M Y', strtotime($sale['tanggal'])) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($sale['nama_user'] ?? '-') ?></td>
                                    <td class="text-right py-3 px-4">
                                        <span class="font-semibold text-emerald-600">Rp <?= number_format($sale['total'], 0, ',', '.') ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Products -->
            <div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Produk Terlaris</h2>

                    <?php if (empty($top_products)): ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500 text-sm">Belum ada data penjualan produk</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($top_products as $index => $product): ?>
                        <div class="pb-4 border-b border-gray-100 last:border-0">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">
                                            <?= $index + 1 ?>
                                        </span>
                                        <p class="font-semibold text-gray-900 truncate text-sm"><?= htmlspecialchars($product['nama_barang']) ?></p>
                                    </div>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($product['nama_kategori'] ?? '-') ?></p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="font-bold text-gray-900"><?= $product['total_qty_sold'] ?> pcs</p>
                                    <p class="text-xs text-emerald-600">Rp <?= number_format($product['total_revenue'], 0, ',', '.') ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <?php if (!empty($low_stock)): ?>
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-red-100 p-6">
            <div class="flex items-center gap-2 mb-6">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M7.08 6.47A9.984 9.984 0 0120.92 17.53M7.08 6.47L9.54 9M7.08 6.47l-2.46 2.53m13.84 10.53L14.46 15m2.46 2.53l2.46-2.53"></path>
                </svg>
                <h2 class="text-lg font-bold text-gray-900">⚠️ Peringatan Stok Rendah</h2>
                <span class="ml-auto text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full"><?= count($low_stock) ?> Produk</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach ($low_stock as $item): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($item['nama_barang']) ?></p>
                    <p class="text-red-600 font-bold text-lg mt-2"><?= $item['stok'] ?? 0 ?> stok</p>
                    <p class="text-xs text-gray-500 mt-1">Perlu segera restok</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>

<?php
require_once '../auth/check_auth.php';
require_once '../config/database.php';

try {
    // Determine views & queries by role
    if (is_admin()) {
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
                SELECT COALESCE(SUM(stock_qty * harga_beli), 0) AS inventory_value
                FROM (
                    SELECT
                        b.id_barang,
                        b.harga_beli,
                        COALESCE(pb.qty_in, 0) - COALESCE(ps.qty_out, 0) AS stock_qty
                    FROM barang b
                    LEFT JOIN (
                        SELECT id_barang, SUM(qty) AS qty_in
                        FROM detail_pembelian
                        GROUP BY id_barang
                    ) pb ON pb.id_barang = b.id_barang
                    LEFT JOIN (
                        SELECT id_barang, SUM(qty) AS qty_out
                        FROM detail_penjualan
                        GROUP BY id_barang
                    ) ps ON ps.id_barang = b.id_barang
                ) stock_summary
        ");
        $inventory_value = $stmt->fetch(PDO::FETCH_ASSOC)['inventory_value'];

        // Total Products
        $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM barang");
        $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

        // Total Categories
        $stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM kategori");
        $total_categories = $stmt->fetch(PDO::FETCH_ASSOC)['total_categories'];

        // Monthly Revenue (this month)
        $stmt = $pdo->query("
            SELECT COALESCE(SUM(total), 0) as monthly_revenue
            FROM penjualan
            WHERE DATE_FORMAT(tanggal, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
        ");
        $monthly_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['monthly_revenue'];

        // Recent Sales (last 5)
        $stmt = $pdo->query("
            SELECT p.id_penjualan, p.tanggal, u.nama_user, p.total,
                   COUNT(dp.iddetail_penjualan) as jumlah_item
            FROM penjualan p
            LEFT JOIN user u ON p.id_user = u.id_user
            LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
            GROUP BY p.id_penjualan, p.tanggal, u.nama_user, p.total
            ORDER BY p.tanggal DESC, p.id_penjualan DESC
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
            GROUP BY b.id_barang, b.nama_barang, k.nama_kategori
            HAVING total_qty_sold > 0
            ORDER BY total_qty_sold DESC
            LIMIT 5
        ");
        $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sales trend for chart (last 7 days)
        $stmt = $pdo->query("
            SELECT DATE(tanggal) as date, SUM(total) as daily_revenue
            FROM penjualan
            WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(tanggal)
            ORDER BY date ASC
        ");
        $sales_trend_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $chart_labels = [];
        $chart_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $chart_labels[] = date('d M', strtotime($d));
            $val = 0;
            foreach ($sales_trend_raw as $r) {
                if ($r['date'] === $d) {
                    $val = (float)$r['daily_revenue'];
                    break;
                }
            }
            $chart_data[] = $val;
        }
    } else {
        // Kasir
        // Total cashier sales today
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as total_sales_today FROM penjualan WHERE id_user = :id_user AND DATE(tanggal) = CURDATE()");
        $stmt->execute(['id_user' => $user_id]);
        $total_sales_today = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales_today'];

        // Total cashier transactions today
        $stmt = $pdo->prepare("SELECT COUNT(*) as txn_today FROM penjualan WHERE id_user = :id_user AND DATE(tanggal) = CURDATE()");
        $stmt->execute(['id_user' => $user_id]);
        $txn_today = $stmt->fetch(PDO::FETCH_ASSOC)['txn_today'];

        // Average transaction value for this cashier
        $stmt = $pdo->prepare("SELECT COALESCE(AVG(total), 0) as avg_sales FROM penjualan WHERE id_user = :id_user");
        $stmt->execute(['id_user' => $user_id]);
        $avg_sales = $stmt->fetch(PDO::FETCH_ASSOC)['avg_sales'];

        // Recent cashier sales (last 5)
        $stmt = $pdo->prepare("
            SELECT p.id_penjualan, p.tanggal, p.total,
                   COUNT(dp.iddetail_penjualan) as jumlah_item
            FROM penjualan p
            LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
            WHERE p.id_user = :id_user
            GROUP BY p.id_penjualan, p.tanggal, p.total
            ORDER BY p.tanggal DESC, p.id_penjualan DESC
            LIMIT 5
        ");
        $stmt->execute(['id_user' => $user_id]);
        $recent_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Low stock warning (common for both)
    $stmt = $pdo->query("
        SELECT stock_data.id_barang, stock_data.nama_barang, GREATEST(stock_data.stok_actual, 0) AS stok
        FROM (
            SELECT b.id_barang, b.nama_barang,
                COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) -
                COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0) AS stok_actual
            FROM barang b
        ) stock_data
        WHERE stock_data.stok_actual < 6
        ORDER BY stock_data.stok_actual ASC, stock_data.nama_barang ASC
    ");
    $low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error database: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Dashboard | IPOS Toko Surya Kemasan</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-slate-50 text-slate-800">
    <!-- Navbar -->
    <?php require_once '../includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content p-4 md:p-8 fade-in">
        <!-- Top Header Panel -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span class="w-2.5 h-7 bg-brand-600 rounded-full inline-block"></span>
                    Dashboard
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Selamat datang kembali, <strong class="text-slate-800 font-semibold"><?= htmlspecialchars($user_name) ?></strong>!
                    Anda login sebagai <span class="px-2 py-0.5 bg-brand-50 text-brand-650 font-semibold rounded-md text-xs capitalize"><?= htmlspecialchars($role) ?></span>.
                </p>
            </div>
            <div class="flex items-center gap-3 border-t md:border-t-0 pt-3 md:pt-0 border-slate-100">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-3 text-xs text-slate-500 font-medium">
                    <i data-lucide="calendar" class="w-4 h-4 text-brand-600"></i>
                    <div>
                        <p class="text-slate-400">Tanggal</p>
                        <p class="text-slate-800 font-semibold"><?= date('d F Y') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl text-red-700 text-sm flex items-center gap-2.5">
            <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied'): ?>
        <div class="mb-6 p-4 bg-amber-50 border border-amber-250 rounded-xl text-amber-800 text-sm flex items-center gap-2.5">
            <i data-lucide="shield-alert" class="w-5 h-5 flex-shrink-0 text-amber-600"></i>
            <strong>Akses Ditolak:</strong> Halaman tersebut hanya dapat diakses oleh Admin.
        </div>
        <?php endif; ?>

        <!-- ================= ADMIN VIEW ================= -->
        <?php if (is_admin()): ?>
            <!-- Admin KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Revenue -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-slate-500 font-medium text-sm">Total Pendapatan</h3>
                        <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="trending-up" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">Rp <?= number_format($total_revenue, 0, ',', '.') ?></p>
                    <p class="text-xs text-slate-400 mt-2">Akumulasi seluruh penjualan</p>
                </div>

                <!-- Total Cost -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-slate-500 font-medium text-sm">Total Pengeluaran</h3>
                        <div class="w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">Rp <?= number_format($total_cost, 0, ',', '.') ?></p>
                    <p class="text-xs text-slate-400 mt-2">Total belanja pembelian barang</p>
                </div>

                <!-- Total Profit -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-slate-500 font-medium text-sm">Margin Laba Bersih</h3>
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="badge-dollar-sign" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">Rp <?= number_format($total_profit, 0, ',', '.') ?></p>
                    <p class="text-xs mt-2 font-medium <?= $total_profit >= 0 ? 'text-emerald-600' : 'text-red-600' ?>">
                        <?= $total_profit >= 0 ? '✓ Profit positif' : '✗ Mengalami kerugian' ?>
                    </p>
                </div>

                <!-- Inventory Value -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-slate-500 font-medium text-sm">Nilai Aset Stok</h3>
                        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="coins" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">Rp <?= number_format($inventory_value, 0, ',', '.') ?></p>
                    <p class="text-xs text-slate-400 mt-2">Nilai aset berdasarkan harga beli</p>
                </div>
            </div>

            <!-- Stats and Chart Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Sales Trend Chart -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Tren Penjualan</h2>
                            <p class="text-slate-400 text-xs mt-0.5">Grafik pendapatan 7 hari terakhir</p>
                        </div>
                        <span class="w-2.5 h-2.5 bg-brand-500 rounded-full"></span>
                    </div>
                    <div class="h-72">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Secondary Metrics -->
                <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 mb-6">Ringkasan Data</h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-brand-100 text-brand-700 rounded-lg flex items-center justify-center">
                                        <i data-lucide="wallet" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-sm text-slate-650 font-medium">Bulan Ini</span>
                                </div>
                                <span class="font-bold text-slate-800 text-sm">Rp <?= number_format($monthly_revenue, 0, ',', '.') ?></span>
                            </div>

                            <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-sky-100 text-sky-700 rounded-lg flex items-center justify-center">
                                        <i data-lucide="package" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-sm text-slate-650 font-medium">Total Produk</span>
                                </div>
                                <span class="font-bold text-slate-800 text-sm"><?= $total_products ?> Item</span>
                            </div>

                            <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-emerald-100 text-emerald-700 rounded-lg flex items-center justify-center">
                                        <i data-lucide="tags" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-sm text-slate-650 font-medium">Total Kategori</span>
                                </div>
                                <span class="font-bold text-slate-800 text-sm"><?= $total_categories ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-6 border-t border-slate-100 mt-6 flex justify-end gap-2">
                        <a href="../categories/index.php" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-slate-200 bg-white text-slate-650 hover:bg-slate-50 text-xs font-semibold rounded-xl transition-colors shadow-sm">
                            <i data-lucide="folder" class="w-4 h-4 text-slate-500"></i> Kelola Kategori
                        </a>
                        <a href="../public/index.php" class="ipos-btn-primary text-xs flex items-center gap-1">
                            <i data-lucide="package" class="w-4 h-4"></i> Kelola Produk
                        </a>
                    </div>
                </div>
            </div>

            <!-- Admin Tables Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Recent Sales -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Penjualan Terbaru</h2>
                            <p class="text-slate-400 text-xs mt-0.5">Daftar transaksi toko paling baru</p>
                        </div>
                        <a href="../sales/index.php" class="text-sm text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
                            Semua <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                    <?php if (empty($recent_sales)): ?>
                    <div class="text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i data-lucide="receipt-text" class="w-10 h-10 text-slate-300 mx-auto mb-2.5"></i>
                        <p class="text-slate-500 text-sm">Belum ada transaksi penjualan</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 text-left text-slate-400 font-medium text-xs uppercase tracking-wider">
                                    <th class="py-3 px-4">Invoice</th>
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">Kasir</th>
                                    <th class="py-3 px-4 text-right">Total Belanja</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach ($recent_sales as $sale): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3 px-4 font-mono text-brand-600 font-semibold text-xs">#<?= $sale['id_penjualan'] ?></td>
                                    <td class="py-3 px-4 text-slate-600 text-xs"><?= date('d M Y', strtotime($sale['tanggal'])) ?></td>
                                    <td class="py-3 px-4 text-slate-700 font-medium text-xs"><?= htmlspecialchars($sale['nama_user'] ?? '-') ?></td>
                                    <td class="text-right py-3 px-4">
                                        <span class="font-bold text-slate-950">Rp <?= number_format($sale['total'], 0, ',', '.') ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Best Sellers -->
                <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900 mb-1">Produk Terlaris</h2>
                    <p class="text-slate-400 text-xs mb-6">Top 5 produk dengan qty terbanyak</p>

                    <?php if (empty($top_products)): ?>
                    <div class="text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i data-lucide="package-search" class="w-10 h-10 text-slate-300 mx-auto mb-2.5"></i>
                        <p class="text-slate-500 text-sm">Belum ada data barang terjual</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($top_products as $index => $product): ?>
                        <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-50 last:border-0 last:pb-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center justify-center w-5 h-5 bg-brand-50 text-brand-700 text-xs font-bold rounded-lg flex-shrink-0">
                                        <?= $index + 1 ?>
                                    </span>
                                    <p class="font-semibold text-slate-800 truncate text-xs" title="<?= htmlspecialchars($product['nama_barang']) ?>">
                                        <?= htmlspecialchars($product['nama_barang']) ?>
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] uppercase font-bold tracking-tight">
                                    <?= htmlspecialchars($product['nama_kategori'] ?? 'Umum') ?>
                                </span>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-slate-800 text-xs"><?= $product['total_qty_sold'] ?> pcs</p>
                                <p class="text-[10px] text-emerald-600 font-semibold">Rp <?= number_format($product['total_revenue'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ================= KASIR VIEW ================= -->
        <?php else: ?>
            <!-- Kasir KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Today's Cashier Sales -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-slate-500 font-medium text-sm">Penjualan Saya Hari Ini</h3>
                        <div class="w-12 h-12 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="receipt" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">Rp <?= number_format($total_sales_today, 0, ',', '.') ?></p>
                    <p class="text-xs text-slate-400 mt-2">Total penjualan Anda hari ini</p>
                </div>

                <!-- Today's Transactions Count -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-slate-500 font-medium text-sm">Transaksi Hari Ini</h3>
                        <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900"><?= $txn_today ?> Transaksi</p>
                    <p class="text-xs text-slate-400 mt-2">Jumlah pelanggan di POS Anda hari ini</p>
                </div>

                <!-- Average Sale Value -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-slate-500 font-medium text-sm">Rata-rata Penjualan</h3>
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="calculator" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">Rp <?= number_format($avg_sales, 0, ',', '.') ?></p>
                    <p class="text-xs text-slate-400 mt-2">Nilai transaksi rata-rata yang ditangani</p>
                </div>
            </div>

            <!-- Cashier Action & Transactions Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Cashier Recent Sales -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Transaksi Saya Terakhir</h2>
                            <p class="text-slate-400 text-xs mt-0.5">5 transaksi terakhir yang Anda layani</p>
                        </div>
                        <a href="../sales/index.php" class="text-sm text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
                            Riwayat <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                    <?php if (empty($recent_sales)): ?>
                    <div class="text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i data-lucide="receipt-text" class="w-10 h-10 text-slate-300 mx-auto mb-2.5"></i>
                        <p class="text-slate-500 text-sm">Anda belum menangani transaksi</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 text-left text-slate-400 font-medium text-xs uppercase tracking-wider">
                                    <th class="py-3 px-4">Invoice</th>
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">Jumlah Item</th>
                                    <th class="py-3 px-4 text-right">Total Transaksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach ($recent_sales as $sale): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3 px-4 font-mono text-brand-600 font-semibold text-xs">#<?= $sale['id_penjualan'] ?></td>
                                    <td class="py-3 px-4 text-slate-600 text-xs"><?= date('d M Y, H:i', strtotime($sale['tanggal'])) ?></td>
                                    <td class="py-3 px-4 text-slate-700 text-xs font-medium"><?= (int)$sale['jumlah_item'] ?> item</td>
                                    <td class="text-right py-3 px-4">
                                        <span class="font-bold text-slate-950">Rp <?= number_format($sale['total'], 0, ',', '.') ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- POS Launcher Card -->
                <div class="bg-gradient-to-br from-brand-600 to-brand-850 rounded-2xl p-6 text-white shadow-lg flex flex-col justify-between min-h-[300px]">
                    <div>
                        <div class="w-12 h-12 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center mb-4">
                            <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                        </div>
                        <h2 class="text-xl font-bold">Mulai Penjualan Baru</h2>
                        <p class="text-sky-100/80 text-xs mt-2 leading-relaxed">
                            Buka aplikasi kasir POS sekarang untuk melayani pelanggan, memasukkan item belanjaan, dan mencetak struk transaksi.
                        </p>
                    </div>
                    <div class="pt-6">
                        <a href="../sales/pos.php" class="w-full bg-white text-brand-700 font-bold py-3 px-4 rounded-xl shadow-md hover:bg-slate-50 transition-colors flex items-center justify-center gap-2">
                            Buka Aplikasi POS <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ================= LOW STOCK ALERT (COMMON) ================= -->
        <?php if (!empty($low_stock)): ?>
        <div class="bg-white rounded-2xl border border-red-100 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 border-b border-slate-50 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-red-50 text-red-650 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">⚠️ Peringatan Stok Kritis</h2>
                        <p class="text-slate-400 text-[11px] mt-0.5">Produk yang memiliki stok kurang dari 6 pcs</p>
                    </div>
                </div>
                <span class="text-xs bg-red-100 text-red-750 font-bold px-2.5 py-1 rounded-full"><?= count($low_stock) ?> Produk</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach ($low_stock as $item): ?>
                <div class="bg-red-50/50 border border-red-100/80 rounded-xl p-4 flex items-center justify-between hover:bg-red-50 transition-colors">
                    <div>
                        <p class="font-semibold text-slate-800 text-xs truncate max-w-[130px]" title="<?= htmlspecialchars($item['nama_barang']) ?>">
                            <?= htmlspecialchars($item['nama_barang']) ?>
                        </p>
                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">ID: <?= htmlspecialchars($item['id_barang']) ?></p>
                    </div>
                    <div class="text-right">
                        <span class="text-red-650 font-extrabold text-sm"><?= $item['stok'] ?> pcs</span>
                        <p class="text-[9px] font-bold text-red-500 uppercase mt-0.5 tracking-tighter">Beli Stok</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        lucide.createIcons();

        // Render Chart.js Tren Penjualan if admin
        <?php if (is_admin()): ?>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Pendapatan Harian',
                    data: <?= json_encode($chart_data) ?>,
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#0284c7',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            },
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>

</html>
<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

try {
    // Query dynamic stock calculations
    $sql = "SELECT 
                b.*,
                k.nama_kategori,
                COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) as qty_in,
                COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0) as qty_out,
                (COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) - 
                 COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0)) as stok,
                COALESCE((b.harga_jual - b.harga_beli), 0) as margin
            FROM barang b
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
            ORDER BY b.id_barang DESC";
    $stmt = $pdo->query($sql);
    $barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Dynamic stats
    $totalBarang = count($barang);
    $totalNilai = array_sum(array_column($barang, 'harga_jual'));
    $totalStok = array_sum(array_column($barang, 'stok'));
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Data Master Produk | IPOS Sistem Toko</title>
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen">
    
    <!-- Navbar -->
    <?php require_once '../includes/navbar.php'; ?>

    <div class="main-content px-4 py-8 fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                        <span class="w-2.5 h-7 bg-brand-600 rounded-full inline-block"></span>
                        Data Master Produk
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">Kelola data barang dagangan, harga jual/beli, dan kategori produk toko.</p>
                </div>
                <a href="tambah.php" class="ipos-btn-primary text-xs">
                    <i data-lucide="plus" class="w-4 h-4"></i> Tambah Produk Baru
                </a>
            </div>

            <!-- Stats Panel -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="p-3.5 bg-brand-50 text-brand-650 rounded-xl">
                        <i data-lucide="package" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Total Jenis Barang</p>
                        <p class="text-xl font-extrabold text-slate-850 mt-0.5"><?= $totalBarang ?> <span class="text-xs font-normal text-slate-400">Item</span></p>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="p-3.5 bg-emerald-50 text-emerald-650 rounded-xl">
                        <i data-lucide="calculator" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Akumulasi Nilai Jual</p>
                        <p class="text-xl font-extrabold text-emerald-700 mt-0.5">Rp <?= number_format($totalNilai, 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Kode SKU</th>
                                <th class="px-6 py-4">Nama Produk</th>
                                <th class="px-6 py-4">Harga Beli</th>
                                <th class="px-6 py-4">Harga Jual</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($barang)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                                    <i data-lucide="package-x" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                    Belum ada data produk di master data.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($barang as $row): ?>
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold text-slate-500">
                                    #<?= htmlspecialchars($row['id_barang']) ?>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    <?= htmlspecialchars($row['nama_barang']) ?>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-600">
                                    Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 font-extrabold text-slate-900 text-sm">
                                    Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase bg-brand-50 text-brand-700 border border-brand-100/50">
                                        <?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="edit.php?id=<?= urlencode($row['id_barang']) ?>"
                                            class="p-2 text-brand-600 hover:bg-brand-50 rounded-lg transition-colors"
                                            title="Ubah Data">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= urlencode($row['id_barang']) ?>"
                                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Hapus Data">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 text-slate-400 font-medium">
                    Total master data: <?= count($barang) ?> produk.
                </div>
            </div>
        </div>
    </div>

    <script>
    lucide.createIcons();
    </script>
</body>

</html>
<?php
require_once '../config/database.php';

try {
    $q = "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori ORDER BY b.id_barang DESC";
    $s = $pdo->query($q);
    $d = $s->fetchAll(PDO::FETCH_ASSOC);
    $c = count($d);
    $t = array_sum(array_column($d, 'harga_jual'));
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen">

    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Manajemen Inventaris</h1>
                <p class="text-gray-500 mt-1">Pantau stok dan kategori produk Anda secara real-time.</p>
            </div>
            <a href="tambah.php"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-all shadow-sm hover:shadow-md gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Tambah Barang Baru
            </a>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    <i data-lucide="package" class="w-8 h-8"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Item</p>
                    <p class="text-2xl font-bold"><?= $c ?> <span
                            class="text-sm font-normal text-gray-400">Unit</span></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                    <i data-lucide="trending-up" class="w-8 h-8"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Estimasi Nilai Jual</p>
                    <p class="text-2xl font-bold text-emerald-600">Rp <?= number_format($t, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">ID</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Produk
                            </th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Harga
                                Jual</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Kategori
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($d)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="archive-x" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                Belum ada data barang.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($d as $r): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-400">
                                #<?= htmlspecialchars($r['id_barang']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">
                                    <?= htmlspecialchars($r['nama_barang']) ?></div>
                                <div class="text-[10px] text-gray-400 uppercase tracking-tighter">Verified</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900">Rp
                                    <?= number_format($r['harga_jual'], 0, ',', '.') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-100">
                                    <?= htmlspecialchars($r['nama_kategori'] ?? 'Umum') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="edit.php?id=<?= urlencode($r['id_barang']) ?>"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Edit">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= urlencode($r['id_barang']) ?>"
                                        onclick="return confirm('Hapus barang ini?')"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Hapus">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">Total: <?= $c ?> entri.</p>
            </div>
        </div>
    </div>

    <script>
    lucide.createIcons();
    </script>
</body>

</html>
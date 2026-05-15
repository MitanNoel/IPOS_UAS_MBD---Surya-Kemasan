<?php
require_once '../config/database.php';

try {
    // Siapkan parameter pencarian dan sortir dari query string
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_barang';
    $order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';

    // Batasi kolom yang boleh di-sort untuk menghindari SQL injection
    $allowedSort = [
        'id_barang' => 'b.id_barang',
        'nama_barang' => 'b.nama_barang',
        'harga_jual' => 'b.harga_jual',
        'nama_kategori' => 'k.nama_kategori',
    ];

    $sortColumn = isset($allowedSort[$sort]) ? $allowedSort[$sort] : $allowedSort['id_barang'];

    // Bangun query dasar dengan JOIN ke kategori
    $sql = "SELECT b.*, k.nama_kategori 
            FROM barang b 
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori";

    $params = [];
    if ($q !== '') {
        $sql .= " WHERE (b.nama_barang LIKE :q OR k.nama_kategori LIKE :q)";
        $params[':q'] = "%{$q}%";
    }

    $sql .= " ORDER BY {$sortColumn} {$order}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung ringkasan data
    $totalBarang = count($barang);
    $totalNilai = array_sum(array_column($barang, 'harga_jual'));
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}
?>
<?php
// Helper sederhana untuk membuat link sortir sambil mempertahankan query pencarian
function sort_link($column, $label)
{
    $q = isset($_GET['q']) ? $_GET['q'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_barang';
    $order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'asc' : 'desc';

    $newOrder = 'desc';
    if ($sort === $column && $order === 'asc') {
        $newOrder = 'desc';
    } elseif ($sort === $column && $order === 'desc') {
        $newOrder = 'asc';
    }

    $qs = http_build_query(['q' => $q, 'sort' => $column, 'order' => $newOrder]);
    return '<a href="?' . $qs . '" class="hover:underline">' . htmlspecialchars($label) . '</a>';
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
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Manajemen Transaksi</h1>

            </div>
            <div class="flex items-center gap-3">
                <!-- Perbaikan: Link relatif tanpa $baseUrl -->
                <a href="tambah.php"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-all shadow-sm hover:shadow-md gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Tambah Barang Baru
                </a>

                <!-- Form pencarian -->
                <form method="get" action="index.php" class="flex items-center gap-2">
                    <input type="search" name="q" value="<?= htmlspecialchars($q) ?>"
                        placeholder="Cari produk atau kategori" class="px-3 py-2 border rounded-lg text-sm" />
                    <button type="submit" class="px-3 py-2 bg-slate-100 rounded-lg text-sm">Cari</button>
                </form>
            </div>
        </div>

        <!-- Statistik Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    <i data-lucide="package" class="w-8 h-8"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Item</p>
                    <p class="text-2xl font-bold"><?= $totalBarang ?> <span
                            class="text-sm font-normal text-gray-400">Unit</span></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                    <i data-lucide="trending-up" class="w-8 h-8"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Estimasi Nilai Jual</p>
                    <p class="text-2xl font-bold text-emerald-600">Rp <?= number_format($totalNilai, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <?= sort_link('id_barang','ID') ?></th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <?= sort_link('nama_barang','Produk') ?></th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <?= sort_link('harga_jual','Harga Jual') ?></th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <?= sort_link('nama_kategori','Kategori') ?></th>
                            <th
                                class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($barang)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="archive-x" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                Belum ada data barang.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($barang as $row): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-400">
                                #<?= htmlspecialchars($row['id_barang']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">
                                    <?= htmlspecialchars($row['nama_barang']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900">Rp
                                    <?= number_format($row['harga_jual'], 0, ',', '.') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-100">
                                    <?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <!-- Perbaikan: Link relatif tanpa $baseUrl -->
                                    <a href="edit.php?id=<?= urlencode($row['id_barang']) ?>"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Ubah Data">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= urlencode($row['id_barang']) ?>"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Hapus Data">
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
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                <p class="text-xs text-gray-400">Menampilkan total <?= count($barang) ?> entri barang.</p>
            </div>
        </div>
    </div>

    <script>
    lucide.createIcons();
    </script>
</body>

</html>
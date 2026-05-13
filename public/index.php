<?php
require_once '../config/app.php';

$user = require_auth('login.php');
$role = current_user_role();

try {
    // Menggunakan JOIN untuk mengambil nama_kategori dari tabel kategori
    $sql = "SELECT b.*, k.nama_kategori 
            FROM barang b 
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
            ORDER BY b.id_barang DESC";
    $stmt = $pdo->query($sql);
    $barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung ringkasan data
    $totalBarang = count($barang);
    $totalNilai = array_sum(array_column($barang, 'harga_jual'));
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Surya Kemasan | Dashboard</title>
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
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Manajemen Inventaris</h1>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200"><?= role_label($role) ?></span>
                </div>
                <p class="text-gray-500 mt-1">Pantau stok dan kategori produk Anda secara real-time.</p>
                <p class="text-sm text-gray-400 mt-1">Masuk sebagai <?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Pengguna') ?>.</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <?php if ($role === 'admin'): ?>
                <a href="tambah.php"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-all shadow-sm hover:shadow-md gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Tambah Barang Baru
                </a>
                <?php endif; ?>
                <a href="logout.php"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-gray-200 text-gray-600 font-medium rounded-lg transition-all shadow-sm hover:shadow-md gap-2">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    Keluar
                </a>
            </div>
        </div>

        <?php if (isset($_GET['status'])): ?>
        <div class="mb-6 rounded-xl border px-4 py-3 text-sm <?php echo in_array($_GET['status'], ['success_insert', 'success_update', 'deleted'], true) ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-amber-50 border-amber-200 text-amber-700'; ?>">
            <?php if ($_GET['status'] === 'success_insert'): ?>Data barang berhasil ditambahkan.
            <?php elseif ($_GET['status'] === 'success_update'): ?>Data barang berhasil diperbarui.
            <?php elseif ($_GET['status'] === 'deleted'): ?>Data barang berhasil dihapus.
            <?php elseif ($_GET['status'] === 'forbidden'): ?>Anda tidak memiliki akses untuk membuka halaman tersebut.
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($role === 'cashier'): ?>
        <div class="mb-8 rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sky-800">
            <h2 class="font-bold text-lg">Mode Kasir</h2>
            <p class="mt-1 text-sm leading-6">Dashboard ini masih membaca data inventaris yang sama, tetapi aksi pengelolaan dibatasi. Modul POS penjualan akan menjadi langkah berikutnya.</p>
        </div>
        <?php endif; ?>

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
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">ID</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Produk
                            </th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Harga
                                Jual</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Kategori
                            </th>
                            <?php if ($role === 'admin'): ?>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">
                                Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $tableColspan = $role === 'admin' ? 5 : 4; ?>
                        <?php if (empty($barang)): ?>
                        <tr>
                            <td colspan="<?= $tableColspan ?>" class="px-6 py-12 text-center text-gray-400">
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
                                <div class="text-[10px] text-gray-400 uppercase tracking-tighter">Terverifikasi</div>
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
                            <?php if ($role === 'admin'): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
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
                            <?php endif; ?>
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
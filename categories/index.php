<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

try {
    // Ambil data kategori beserta jumlah produk terkait
    $stmt = $pdo->query('SELECT k.*, COUNT(b.id_barang) AS total_produk FROM kategori k LEFT JOIN barang b ON k.id_kategori = b.id_kategori GROUP BY k.id_kategori ORDER BY k.id_kategori ASC');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}

$status = $_GET['status'] ?? '';
$statusMessages = [
    'created' => 'Kategori berhasil ditambahkan.',
    'updated' => 'Kategori berhasil diperbarui.',
    'deleted' => 'Kategori berhasil dihapus.',
    'error' => 'Terjadi kesalahan saat memproses data kategori.',
    'has_products' => 'Kategori gagal dihapus karena masih memiliki produk aktif terkait.'
];
$message = $statusMessages[$status] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Manajemen Kategori | IPOS Toko Surya Kemasan</title>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    
    <div class="main-content px-4 py-8 fade-in">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                        <span class="w-2.5 h-7 bg-brand-600 rounded-full inline-block"></span>
                        Manajemen Kategori
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">Kelola kategori pengelompokkan produk dagangan toko.</p>
                </div>
                <a href="tambah.php" class="ipos-btn-primary text-xs">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Kategori
                </a>
            </div>

            <!-- Status Alert -->
            <?php if ($message): ?>
            <div class="mb-6 p-4 <?= strpos($status, 'error') !== false || $status === 'has_products' ? 'bg-red-50 border-red-100 text-red-800' : 'bg-emerald-50 border-emerald-100 text-emerald-800' ?> border rounded-xl text-xs flex items-center gap-2 font-medium">
                <i data-lucide="<?= strpos($status, 'error') !== false || $status === 'has_products' ? 'alert-circle' : 'check-circle' ?>" class="w-4.5 h-4.5"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">ID Kategori</th>
                                <th class="px-6 py-4">Nama Kategori</th>
                                <th class="px-6 py-4 text-center">Jumlah Produk Terkait</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center text-slate-400">
                                    <i data-lucide="folder-x" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                    Belum ada data kategori.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold text-slate-500">#<?= htmlspecialchars($cat['id_kategori']) ?></td>
                                <td class="px-6 py-4 font-bold text-slate-900"><?= htmlspecialchars($cat['nama_kategori']) ?></td>
                                <td class="px-6 py-4 text-center font-semibold text-slate-650">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] <?= $cat['total_produk'] > 0 ? 'bg-blue-55 text-blue-700 font-bold' : 'bg-slate-100 text-slate-500' ?>">
                                        <?= (int)$cat['total_produk'] ?> Produk
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="edit.php?id=<?= urlencode($cat['id_kategori']) ?>" class="p-2 rounded-lg text-brand-600 hover:bg-brand-50 transition-colors" title="Edit">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <a href="process_delete.php?id=<?= urlencode($cat['id_kategori']) ?>" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Kategori yang memiliki produk aktif tidak dapat dihapus.');"
                                           class="p-2 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-755 transition-colors" title="Hapus">
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
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>

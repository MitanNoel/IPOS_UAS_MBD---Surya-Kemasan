<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

try {
    $stmt = $pdo->query('SELECT * FROM supplier ORDER BY id_supplier ASC');
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}

$status = $_GET['status'] ?? '';
$statusMessages = [
    'created' => 'Supplier berhasil ditambahkan.',
    'updated' => 'Supplier berhasil diperbarui.',
    'deleted' => 'Supplier berhasil dihapus.',
    'error' => 'Terjadi kesalahan saat memproses data supplier.'
];
$message = $statusMessages[$status] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Manajemen Supplier | IPOS Toko Surya Kemasan</title>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    
    <div class="main-content px-4 py-8 fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                        <span class="w-2.5 h-7 bg-brand-600 rounded-full inline-block"></span>
                        Manajemen Supplier
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">Kelola daftar kontak supplier penyedia barang dagangan.</p>
                </div>
                <a href="tambah.php" class="ipos-btn-primary text-xs">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Supplier
                </a>
            </div>

            <!-- Status Alert -->
            <?php if ($message): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-800 text-xs flex items-center gap-2 font-medium">
                <i data-lucide="check-circle" class="w-4.5 h-4.5 text-emerald-600"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">ID Supplier</th>
                                <th class="px-6 py-4">Nama Supplier</th>
                                <th class="px-6 py-4">Nomor Telepon</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center text-slate-400">
                                    <i data-lucide="users-x" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                    Belum ada data supplier.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($suppliers as $supplier): ?>
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold text-slate-500">#<?= htmlspecialchars($supplier['id_supplier']) ?></td>
                                <td class="px-6 py-4 font-bold text-slate-900"><?= htmlspecialchars($supplier['nama_supplier']) ?></td>
                                <td class="px-6 py-4 text-slate-650 font-medium"><?= htmlspecialchars($supplier['no_telp']) ?></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="edit.php?id=<?= urlencode($supplier['id_supplier']) ?>" class="p-2 rounded-lg text-brand-600 hover:bg-brand-55 hover:text-brand-700 transition-colors" title="Edit">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= urlencode($supplier['id_supplier']) ?>" class="p-2 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-750 transition-colors" title="Hapus">
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

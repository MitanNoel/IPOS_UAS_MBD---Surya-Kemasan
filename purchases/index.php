<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

try {
    $stmt = $pdo->query("SELECT p.*, s.nama_supplier, u.nama_user FROM pembelian p LEFT JOIN supplier s ON p.id_supplier = s.id_supplier LEFT JOIN user u ON p.id_user = u.id_user ORDER BY p.tanggal DESC, p.id_pembelian DESC");
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}

$status = $_GET['status'] ?? '';
$statusMessages = [
    'created' => 'Pembelian berhasil ditambahkan.',
    'updated' => 'Pembelian berhasil diperbarui.',
    'deleted' => 'Pembelian berhasil dihapus.',
    'error' => 'Terjadi kesalahan saat memproses pembelian.'
];
$message = $statusMessages[$status] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar Pembelian | Sistem Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'); body{font-family:Inter, sans-serif} .main-content{margin-left:16rem}</style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    <div class="main-content px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Daftar Pembelian</h1>
                    <p class="text-sm text-gray-500">Riwayat pembelian barang dari supplier.</p>
                </div>
                <a href="tambah.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Pembelian
                </a>
            </div>

            <?php if ($message): ?><div class="mb-4 p-3 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded"><?= htmlspecialchars($message) ?></div><?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500">ID</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500">Tanggal</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500">Supplier</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500">Total</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500">Oleh</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($purchases)): ?>
                                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Belum ada data pembelian.</td></tr>
                            <?php else: foreach ($purchases as $p): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-sm">#<?= htmlspecialchars($p['id_pembelian']) ?></td>
                                    <td class="px-6 py-3 text-sm"><?= htmlspecialchars($p['tanggal']) ?></td>
                                    <td class="px-6 py-3 text-sm"><?= htmlspecialchars($p['nama_supplier'] ?? 'Tidak Diketahui') ?></td>
                                    <td class="px-6 py-3 text-sm font-semibold">Rp <?= number_format($p['total'] ?? 0, 0, ',', '.') ?></td>
                                    <td class="px-6 py-3 text-sm"><?= htmlspecialchars($p['nama_user'] ?? 'System') ?></td>
                                    <td class="px-6 py-3 text-right text-sm">
                                        <div class="flex justify-end gap-2">
                                            <a href="view.php?id=<?= urlencode($p['id_pembelian']) ?>" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                            <a href="edit.php?id=<?= urlencode($p['id_pembelian']) ?>" class="p-2 rounded-lg text-amber-600 hover:bg-amber-50"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                            <form action="process_delete.php" method="POST" onsubmit="return confirm('Hapus pembelian ini?');">
                                                <input type="hidden" name="id_pembelian" value="<?= htmlspecialchars($p['id_pembelian']) ?>">
                                                <button type="submit" class="p-2 rounded-lg text-red-600 hover:bg-red-50"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
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
    <?php require_once '../includes/header.php'; ?>
    <title>Pembelian Stok | IPOS Toko Surya Kemasan</title>
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
                        Pembelian (Restok Barang)
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">Riwayat pengadaan stok barang dari pihak supplier.</p>
                </div>
                <a href="tambah.php" class="ipos-btn-primary text-xs">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Catat Pembelian Baru
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
                                <th class="px-6 py-4">ID Pembelian</th>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Supplier</th>
                                <th class="px-6 py-4">Total Biaya Belanja</th>
                                <th class="px-6 py-4">Dicatat Oleh</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($purchases)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                                        <i data-lucide="receipt-text" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                        Belum ada riwayat transaksi pembelian.
                                    </td>
                                </tr>
                            <?php else: foreach ($purchases as $p): ?>
                                <tr class="hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-brand-600">#<?= htmlspecialchars($p['id_pembelian']) ?></td>
                                    <td class="px-6 py-4 text-slate-650 font-medium"><?= date('d M Y', strtotime($p['tanggal'])) ?></td>
                                    <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($p['nama_supplier'] ?? 'Tidak Diketahui') ?></td>
                                    <td class="px-6 py-4 text-slate-900 font-extrabold text-sm">Rp <?= number_format($p['total'] ?? 0, 0, ',', '.') ?></td>
                                    <td class="px-6 py-4"><span class="px-2.5 py-0.5 bg-slate-100 rounded-md text-slate-700 font-bold"><?= htmlspecialchars($p['nama_user'] ?? 'System') ?></span></td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-1">
                                            <a href="view.php?id=<?= urlencode($p['id_pembelian']) ?>" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors" title="Lihat Detail">
                                                <i data-lucide="eye" class="w-4.5 h-4.5"></i>
                                            </a>
                                            <a href="edit.php?id=<?= urlencode($p['id_pembelian']) ?>" class="p-2 rounded-lg text-brand-600 hover:bg-brand-50 transition-colors" title="Edit Transaksi">
                                                <i data-lucide="pencil" class="w-4.5 h-4.5"></i>
                                            </a>
                                            <form action="process_delete.php" method="POST" onsubmit="return confirm('Hapus pembelian ini? Stok produk akan kembali disesuaikan.');" class="inline">
                                                <input type="hidden" name="id_pembelian" value="<?= htmlspecialchars($p['id_pembelian']) ?>">
                                                <button type="submit" class="p-2 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors" title="Hapus Transaksi">
                                                    <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                                </button>
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
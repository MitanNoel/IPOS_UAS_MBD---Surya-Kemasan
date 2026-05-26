<?php
require_once '../auth/check_auth.php';
// Allow admin and kasir
if (!is_admin() && !is_kasir()) {
    header('Location: ../dashboard/index.php?error=access_denied');
    exit();
}
require_once '../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT p.*, u.nama_user, COUNT(dp.iddetail_penjualan) as items_count 
        FROM penjualan p 
        LEFT JOIN user u ON p.id_user = u.id_user 
        LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan 
        GROUP BY p.id_penjualan 
        ORDER BY p.tanggal DESC, p.id_penjualan DESC
    ");
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}

$status = $_GET['status'] ?? '';
$statusMessages = [
    'created' => 'Penjualan berhasil disimpan.',
    'updated' => 'Penjualan berhasil diperbarui.',
    'deleted' => 'Penjualan berhasil dihapus.',
    'error' => 'Terjadi kesalahan saat memproses penjualan.'
];
$message = $statusMessages[$status] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Riwayat Penjualan | IPOS Sistem Toko</title>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    
    <div class="main-content px-4 py-8 fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="receipt" class="w-6 h-6 text-brand-600"></i>
                        Riwayat Penjualan
                    </h1>
                    <p class="text-xs text-slate-500">Daftar transaksi penjualan kasir (POS).</p>
                </div>
                <a href="pos.php" class="ipos-btn-primary text-xs">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i> Buka Aplikasi POS
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
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">ID Transaksi</th>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Jumlah Barang</th>
                                <th class="px-6 py-4">Total Penjualan</th>
                                <th class="px-6 py-4">Kasir</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-150">
                            <?php if (empty($sales)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                                        <i data-lucide="receipt-text" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                        Belum ada transaksi penjualan yang tercatat.
                                    </td>
                                </tr>
                            <?php else: foreach ($sales as $s): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-brand-600">#<?= htmlspecialchars($s['id_penjualan']) ?></td>
                                    <td class="px-6 py-4 text-slate-650 font-medium"><?= date('d M Y, H:i', strtotime($s['tanggal'])) ?></td>
                                    <td class="px-6 py-4 text-slate-600 font-semibold"><?= (int)$s['items_count'] ?> item</td>
                                    <td class="px-6 py-4 text-slate-900 font-extrabold text-sm">Rp <?= number_format($s['total'] ?? 0, 0, ',', '.') ?></td>
                                    <td class="px-6 py-4"><span class="px-2.5 py-0.5 bg-slate-100 rounded-md text-slate-700 font-bold"><?= htmlspecialchars($s['nama_user'] ?? '-') ?></span></td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-1">
                                            <a href="view.php?id=<?= urlencode($s['id_penjualan']) ?>" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors" title="Lihat Struk">
                                                <i data-lucide="eye" class="w-4.5 h-4.5"></i>
                                            </a>
                                            <?php if (is_admin()): ?>
                                            <a href="edit.php?id=<?= urlencode($s['id_penjualan']) ?>" class="p-2 rounded-lg text-brand-600 hover:bg-brand-50 transition-colors" title="Edit Transaksi">
                                                <i data-lucide="pencil" class="w-4.5 h-4.5"></i>
                                            </a>
                                            <form action="process_delete.php" method="POST" onsubmit="return confirm('Hapus transaksi ini? Stok produk akan kembali disesuaikan.');" class="inline">
                                                <input type="hidden" name="id_penjualan" value="<?= htmlspecialchars($s['id_penjualan']) ?>">
                                                <button type="submit" class="p-2 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors" title="Hapus Transaksi">
                                                    <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
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
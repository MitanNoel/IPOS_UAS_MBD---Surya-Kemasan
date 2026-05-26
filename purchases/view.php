<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare('SELECT p.*, s.nama_supplier, u.nama_user FROM pembelian p LEFT JOIN supplier s ON p.id_supplier = s.id_supplier LEFT JOIN user u ON p.id_user = u.id_user WHERE p.id_pembelian = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$purchase) {
        header('Location: index.php');
        exit();
    }

    $stmt = $pdo->prepare('SELECT dp.*, b.nama_barang FROM detail_pembelian dp LEFT JOIN barang b ON dp.id_barang = b.id_barang WHERE dp.id_pembelian = :id');
    $stmt->execute(['id' => $id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Detail Pembelian #<?= htmlspecialchars($purchase['id_pembelian']) ?> | IPOS Toko Surya Kemasan</title>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    <div class="main-content px-4 py-8 fade-in">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="package-2" class="w-5.5 h-5.5 text-brand-600"></i>
                        Detail Nota Pembelian
                    </h1>
                    <p class="text-xs text-slate-500">Nota masuk produk dari supplier #<?= htmlspecialchars($purchase['id_pembelian']) ?>.</p>
                </div>
                <a href="index.php" class="px-4 py-2 border rounded-xl bg-white text-slate-650 hover:bg-slate-50 text-xs font-semibold shadow-sm transition-colors">
                    Kembali
                </a>
            </div>

            <!-- Details Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
                <div class="grid grid-cols-2 gap-6 text-xs">
                    <div class="p-3.5 bg-slate-50 border border-slate-100/50 rounded-xl">
                        <p class="text-slate-400 font-medium mb-1">Nama Supplier:</p>
                        <p class="text-slate-800 font-extrabold text-sm"><?= htmlspecialchars($purchase['nama_supplier'] ?? '-') ?></p>
                    </div>
                    <div class="p-3.5 bg-slate-50 border border-slate-100/50 rounded-xl">
                        <p class="text-slate-400 font-medium mb-1">Dicatat Oleh (Admin):</p>
                        <p class="text-slate-800 font-extrabold text-sm uppercase"><?= htmlspecialchars($purchase['nama_user'] ?? '-') ?></p>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Nama Produk</th>
                            <th class="px-6 py-4 text-center">Kuantitas</th>
                            <th class="px-6 py-4 text-right">Harga Beli Satuan</th>
                            <th class="px-6 py-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($items as $it): ?>
                        <tr class="hover:bg-slate-50/30 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800">
                                <?= htmlspecialchars($it['nama_barang'] ?? $it['id_barang']) ?>
                                <p class="text-[9px] text-slate-400 font-mono mt-0.5">#<?= htmlspecialchars($it['id_barang']) ?></p>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700"><?= (int) $it['qty'] ?> unit</td>
                            <td class="px-6 py-4 text-right text-slate-650">Rp <?= number_format($it['harga_beli'],0,',','.') ?></td>
                            <td class="px-6 py-4 text-right font-extrabold text-slate-900 text-sm">Rp <?= number_format($it['subtotal'],0,',','.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="p-6 text-right border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-10">
                    <span class="text-xs text-slate-500 font-medium">Total Anggaran Pembelian:</span>
                    <span class="text-lg font-extrabold text-slate-900">Rp <?= number_format($purchase['total'],0,',','.') ?></span>
                </div>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
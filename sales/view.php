<?php
require_once '../auth/check_auth.php';
// Allow admin and kasir
if (!is_admin() && !is_kasir()) {
    header('Location: ../dashboard/index.php?error=access_denied');
    exit();
}
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare('SELECT p.*, u.nama_user FROM penjualan p LEFT JOIN user u ON p.id_user = u.id_user WHERE p.id_penjualan = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        header('Location: index.php');
        exit();
    }

    $stmt = $pdo->prepare('SELECT dp.*, b.nama_barang FROM detail_penjualan dp LEFT JOIN barang b ON dp.id_barang = b.id_barang WHERE dp.id_penjualan = :id');
    $stmt->execute(['id' => $id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Detail Penjualan #<?= htmlspecialchars($sale['id_penjualan']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'); body{font-family:Inter, sans-serif} .main-content{margin-left:16rem}</style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    <div class="main-content px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Detail Penjualan</h1>
                    <p class="text-sm text-gray-500">#<?= htmlspecialchars($sale['id_penjualan']) ?> — <?= htmlspecialchars($sale['tanggal']) ?></p>
                </div>
                <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200">Kembali</a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-semibold">Kasir</h3>
                        <p class="text-sm"><?= htmlspecialchars($sale['nama_user'] ?? '-') ?></p>
                    </div>
                    <div>
                        <h3 class="font-semibold">Tanggal</h3>
                        <p class="text-sm"><?= htmlspecialchars($sale['tanggal']) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-xs text-gray-500">Produk</th>
                            <th class="px-6 py-3 text-xs text-gray-500">Qty</th>
                            <th class="px-6 py-3 text-xs text-gray-500">Harga Jual</th>
                            <th class="px-6 py-3 text-xs text-gray-500 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($items as $it): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3"><?= htmlspecialchars($it['nama_barang'] ?? $it['id_barang']) ?></td>
                            <td class="px-6 py-3"><?= (int) $it['qty'] ?></td>
                            <td class="px-6 py-3">Rp <?= number_format($it['harga_jual'],0,',','.') ?></td>
                            <td class="px-6 py-3 text-right font-semibold">Rp <?= number_format($it['subtotal'],0,',','.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="p-6 text-right border-t border-gray-100">
                    <span class="text-sm text-gray-500 mr-4">Total</span>
                    <span class="text-lg font-bold">Rp <?= number_format($sale['total'],0,',','.') ?></span>
                </div>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
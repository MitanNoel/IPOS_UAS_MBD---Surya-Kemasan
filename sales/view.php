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
    <?php require_once '../includes/header.php'; ?>
    <title>Detail Penjualan #<?= htmlspecialchars($sale['id_penjualan']) ?> | IPOS Toko</title>
    <style>
        @media print {
            #sidebarNav, #navToggle, #navOverlay, .no-print {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    
    <div class="main-content px-4 py-8 fade-in">
        <div class="max-w-3xl mx-auto print-container">
            <!-- Header (No Print) -->
            <div class="flex items-center justify-between mb-6 no-print">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="receipt" class="w-5.5 h-5.5 text-brand-600"></i>
                        Detail Transaksi Penjualan
                    </h1>
                    <p class="text-xs text-slate-500">Invoice pembayaran pelanggan di POS.</p>
                </div>
                <div class="flex gap-2">
                    <a href="index.php" class="px-4 py-2 border rounded-xl bg-white text-slate-650 hover:bg-slate-50 text-xs font-semibold shadow-sm transition-colors">
                        Kembali
                    </a>
                    <button onclick="window.print()" class="ipos-btn-primary text-xs">
                        <i data-lucide="printer" class="w-4 h-4"></i> Cetak Struk
                    </button>
                </div>
            </div>

            <!-- Invoice Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden p-6 md:p-8">
                <!-- Receipt Header Details -->
                <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-slate-100 pb-6 mb-6">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center text-white shadow-inner">
                                <i data-lucide="store" class="w-4 h-4"></i>
                            </span>
                            <span class="text-base font-extrabold text-slate-900 tracking-tight">IPOS TOKO</span>
                        </div>
                        <p class="text-xs text-slate-450">Integrated Point of Sale System</p>
                    </div>
                    <div class="sm:text-right">
                        <span class="px-2.5 py-1 bg-brand-50 text-brand-700 font-extrabold rounded-lg text-[10px] uppercase tracking-wider">
                            Lunas / Paid
                        </span>
                        <h2 class="text-sm font-bold text-slate-800 mt-2 font-mono">Invoice: #<?= htmlspecialchars($sale['id_penjualan']) ?></h2>
                        <p class="text-[11px] text-slate-500 mt-1"><?= date('d F Y, H:i', strtotime($sale['tanggal'])) ?></p>
                    </div>
                </div>

                <!-- Metadata info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 border border-slate-100 rounded-xl p-4 mb-6 text-xs">
                    <div>
                        <p class="text-slate-400 font-medium">Dilayani Oleh (Kasir):</p>
                        <p class="text-slate-800 font-bold mt-1 uppercase"><?= htmlspecialchars($sale['nama_user'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-slate-400 font-medium">Metode Pembayaran:</p>
                        <p class="text-slate-800 font-bold mt-1 uppercase">Tunai (Cash)</p>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="border border-slate-100 rounded-xl overflow-hidden mb-6">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">Nama Produk</th>
                                <th class="px-5 py-3.5 text-center">Qty</th>
                                <th class="px-5 py-3.5 text-right">Harga Satuan</th>
                                <th class="px-5 py-3.5 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($items as $it): ?>
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-slate-800">
                                    <?= htmlspecialchars($it['nama_barang'] ?? $it['id_barang']) ?>
                                    <p class="text-[9px] text-slate-400 font-mono mt-0.5">#<?= htmlspecialchars($it['id_barang']) ?></p>
                                </td>
                                <td class="px-5 py-3.5 text-center font-bold text-slate-700"><?= (int) $it['qty'] ?></td>
                                <td class="px-5 py-3.5 text-right text-slate-650">Rp <?= number_format($it['harga_jual'],0,',','.') ?></td>
                                <td class="px-5 py-3.5 text-right font-extrabold text-slate-900">Rp <?= number_format($it['subtotal'],0,',','.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Grand Total Panel -->
                <div class="flex flex-col items-end gap-1.5 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-10 text-xs">
                        <span class="text-slate-500 font-medium">Total Tagihan:</span>
                        <span class="font-extrabold text-slate-900 text-base">Rp <?= number_format($sale['total'],0,',','.') ?></span>
                    </div>
                </div>

                <!-- Footer Receipt Notes -->
                <div class="text-center border-t border-slate-100/70 pt-8 mt-8 text-[10px] text-slate-400">
                    <p class="font-bold uppercase tracking-wider text-slate-500">Terima Kasih Atas Kunjungan Anda</p>
                    <p class="mt-1">Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan.</p>
                </div>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
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
    $stmt = $pdo->prepare('SELECT p.*, u.nama_user FROM penjualan p LEFT JOIN user u ON p.id_user = u.id_user WHERE p.id_penjualan = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        header('Location: index.php');
        exit();
    }

    $stmt = $pdo->prepare('SELECT dp.*, b.nama_barang FROM detail_penjualan dp LEFT JOIN barang b ON dp.id_barang = b.id_barang WHERE dp.id_penjualan = :id ORDER BY dp.iddetail_penjualan ASC');
    $stmt->execute(['id' => $id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $brgStmt = $pdo->query('SELECT id_barang, nama_barang, harga_jual FROM barang ORDER BY nama_barang ASC');
    $barangs = $brgStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}

$status = $_GET['status'] ?? '';
$message = $status === 'error' ? 'Perubahan gagal disimpan. Periksa item, tanggal, dan stok tersedia.' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Penjualan | Sistem Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'); body{font-family:Inter, sans-serif} .main-content{margin-left:16rem}</style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    <div class="main-content px-4 py-8">
        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Edit Penjualan</h1>
                    <p class="text-sm text-gray-500">#<?= htmlspecialchars($sale['id_penjualan']) ?> · kasir <?= htmlspecialchars($sale['nama_user'] ?? '-') ?></p>
                </div>
                <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200">Kembali</a>
            </div>

            <?php if ($message): ?><div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-700 rounded"><?= htmlspecialchars($message) ?></div><?php endif; ?>

            <form id="saleForm" action="process_update.php" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <input type="hidden" name="id_penjualan" value="<?= htmlspecialchars($sale['id_penjualan']) ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="text-sm font-medium">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= htmlspecialchars($sale['tanggal']) ?>" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="flex items-end">
                        <div class="w-full rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-3 flex items-center justify-between">
                            <span class="text-sm text-indigo-700 font-medium">Total</span>
                            <span id="grandTotal" class="text-xl font-bold text-indigo-900">Rp 0</span>
                        </div>
                    </div>
                </div>

                <div class="mb-4 hidden md:grid md:grid-cols-12 gap-2 px-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <div class="md:col-span-5">Produk</div>
                    <div class="md:col-span-2">Qty</div>
                    <div class="md:col-span-2">Harga Jual</div>
                    <div class="md:col-span-2">Subtotal</div>
                    <div class="md:col-span-1 text-right">Aksi</div>
                </div>

                <div id="items" class="space-y-3">
                    <?php if (empty($items)): ?>
                        <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-gray-100 p-3 item-row">
                            <div class="col-span-12 md:col-span-5">
                                <select name="id_barang[]" class="w-full px-3 py-2 border rounded-lg select-barang" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($barangs as $b): ?>
                                        <option value="<?= htmlspecialchars($b['id_barang']) ?>" data-price="<?= htmlspecialchars($b['harga_jual']) ?>"><?= htmlspecialchars($b['nama_barang']) ?> (<?= htmlspecialchars($b['id_barang']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-span-6 md:col-span-2"><input name="qty[]" type="number" min="1" value="1" class="w-full px-3 py-2 border rounded-lg qty-input" required></div>
                            <div class="col-span-6 md:col-span-2"><input name="harga_jual[]" type="number" min="0" step="0.01" class="w-full px-3 py-2 border rounded-lg price-input" placeholder="Harga Jual" required></div>
                            <div class="col-span-8 md:col-span-2"><div class="subtotal-box px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 font-semibold text-right">Rp 0</div></div>
                            <div class="col-span-4 md:col-span-1 text-right"><button type="button" class="remove-row inline-flex items-center justify-center px-3 py-2 rounded-lg text-red-600 hover:bg-red-50">Hapus</button></div>
                        </div>
                    <?php else: foreach ($items as $it): ?>
                        <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-gray-100 p-3 item-row">
                            <div class="col-span-12 md:col-span-5">
                                <select name="id_barang[]" class="w-full px-3 py-2 border rounded-lg select-barang" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($barangs as $b): ?>
                                        <option value="<?= htmlspecialchars($b['id_barang']) ?>" data-price="<?= htmlspecialchars($b['harga_jual']) ?>" <?= $b['id_barang'] == $it['id_barang'] ? 'selected' : '' ?>><?= htmlspecialchars($b['nama_barang']) ?> (<?= htmlspecialchars($b['id_barang']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-span-6 md:col-span-2"><input name="qty[]" type="number" min="1" value="<?= (int) $it['qty'] ?>" class="w-full px-3 py-2 border rounded-lg qty-input" required></div>
                            <div class="col-span-6 md:col-span-2"><input name="harga_jual[]" type="number" min="0" step="0.01" value="<?= htmlspecialchars($it['harga_jual']) ?>" class="w-full px-3 py-2 border rounded-lg price-input" placeholder="Harga Jual" required></div>
                            <div class="col-span-8 md:col-span-2"><div class="subtotal-box px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 font-semibold text-right">Rp 0</div></div>
                            <div class="col-span-4 md:col-span-1 text-right"><button type="button" class="remove-row inline-flex items-center justify-center px-3 py-2 rounded-lg text-red-600 hover:bg-red-50">Hapus</button></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <button id="addRow" type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white">Tambah Item</button>
                    <p class="text-sm text-gray-500">Edit hanya untuk admin dan akan divalidasi ulang sebelum disimpan.</p>
                </div>

                <div class="mt-6 text-right">
                    <button type="submit" class="px-6 py-2 rounded-lg bg-emerald-600 text-white font-semibold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const itemsContainer = document.getElementById('items');
    const grandTotalEl = document.getElementById('grandTotal');

    function formatCurrency(value) {
        return 'Rp ' + Math.round(value || 0).toLocaleString('id-ID');
    }

    function updateRowSubtotal(row) {
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const subtotalBox = row.querySelector('.subtotal-box');
        const qty = parseFloat(qtyInput?.value || 0);
        const price = parseFloat(priceInput?.value || 0);
        const subtotal = Math.max(0, qty * price);
        if (subtotalBox) subtotalBox.textContent = formatCurrency(subtotal);
        return subtotal;
    }

    function updateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(function(row) {
            total += updateRowSubtotal(row);
        });
        grandTotalEl.textContent = formatCurrency(total);
    }

    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList && e.target.classList.contains('remove-row')) {
            const row = e.target.closest('.item-row');
            if (row && document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                updateGrandTotal();
            }
        }
    });

    document.getElementById('addRow').addEventListener('click', function() {
        const template = document.querySelector('#items .item-row').outerHTML;
        itemsContainer.insertAdjacentHTML('beforeend', template);
        const newRow = document.querySelector('#items .item-row:last-child');
        if (newRow) {
            newRow.querySelector('.select-barang').value = '';
            newRow.querySelector('.qty-input').value = 1;
            newRow.querySelector('.price-input').value = '';
            const subtotalBox = newRow.querySelector('.subtotal-box');
            if (subtotalBox) subtotalBox.textContent = formatCurrency(0);
        }
        updateGrandTotal();
    });

    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList && e.target.classList.contains('select-barang')) {
            const price = e.target.selectedOptions[0]?.dataset.price || '';
            const row = e.target.closest('.item-row');
            if (row) {
                const priceInput = row.querySelector('.price-input');
                if (priceInput) priceInput.value = price;
                updateGrandTotal();
            }
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target && (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input'))) {
            updateGrandTotal();
        }
    });

    updateGrandTotal();
    lucide.createIcons();
    </script>
</body>
</html>

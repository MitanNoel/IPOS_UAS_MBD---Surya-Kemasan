<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

try {
    $supStmt = $pdo->query('SELECT * FROM supplier ORDER BY nama_supplier ASC');
    $suppliers = $supStmt->fetchAll(PDO::FETCH_ASSOC);

    $brgStmt = $pdo->query('SELECT id_barang, nama_barang, harga_beli FROM barang ORDER BY nama_barang ASC');
    $barangs = $brgStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tambah Pembelian | Sistem Toko</title>
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
                    <h1 class="text-2xl font-bold">Tambah Pembelian</h1>
                    <p class="text-sm text-gray-500">Tambahkan pembelian baru dan detail item.</p>
                </div>
                <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200">Kembali</a>
            </div>

            <form id="purchaseForm" action="process_insert.php" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-sm font-medium">Supplier</label>
                        <select name="id_supplier" required class="w-full px-3 py-2 border rounded">
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= htmlspecialchars($s['id_supplier']) ?>"><?= htmlspecialchars($s['nama_supplier']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 border rounded">
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold mb-2">Daftar Item</h3>
                    <div id="items">
                        <div class="grid grid-cols-12 gap-2 items-center mb-2">
                            <div class="col-span-6">
                                <select name="id_barang[]" class="w-full px-3 py-2 border rounded select-barang" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($barangs as $b): ?>
                                        <option value="<?= htmlspecialchars($b['id_barang']) ?>" data-price="<?= htmlspecialchars($b['harga_beli']) ?>"><?= htmlspecialchars($b['nama_barang']) ?> (<?= htmlspecialchars($b['id_barang']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-span-2"><input name="qty[]" type="number" min="1" value="1" required class="w-full px-3 py-2 border rounded qty-input"></div>
                            <div class="col-span-2"><input name="harga_beli[]" type="number" min="0" step="0.01" class="w-full px-3 py-2 border rounded price-input" placeholder="Harga Beli"></div>
                            <div class="col-span-2 text-right"><button type="button" class="remove-row text-red-600">Hapus</button></div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button id="addRow" type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-blue-600 text-white">Tambah Item</button>
                    </div>
                </div>

                <div class="mt-4 text-right">
                    <button type="submit" class="px-6 py-2 rounded bg-emerald-600 text-white">Simpan Pembelian</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('click', function(e){
        if (e.target && e.target.classList && e.target.classList.contains('remove-row')) {
            e.target.closest('.grid').remove();
        }
    });
    document.getElementById('addRow').addEventListener('click', function(){
        const template = document.querySelector('#items .grid').outerHTML;
        document.getElementById('items').insertAdjacentHTML('beforeend', template);
    });

    // Auto-fill harga_beli when selecting product
    document.addEventListener('change', function(e){
        if (e.target && e.target.classList && e.target.classList.contains('select-barang')){
            const price = e.target.selectedOptions[0].dataset.price || '';
            const row = e.target.closest('.grid');
            if (row) {
                const priceInput = row.querySelector('.price-input');
                if (priceInput) priceInput.value = price;
            }
        }
    });

    lucide.createIcons();
    </script>
</body>
</html>
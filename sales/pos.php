<?php
require_once '../auth/check_auth.php';
// Allow admin and kasir
if (!is_admin() && !is_kasir()) {
    header('Location: ../dashboard/index.php?error=access_denied');
    exit();
}
require_once '../config/database.php';

try {
    $stmt = $pdo->query('SELECT id_barang, nama_barang, harga_jual FROM barang ORDER BY nama_barang ASC');
    $barangs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>POS | Sistem Toko</title>
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
                    <h1 class="text-2xl font-bold">Point of Sale (POS)</h1>
                    <p class="text-sm text-gray-500">Kasir: buat penjualan cepat di kasir.</p>
                </div>
                <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200">Riwayat</a>
            </div>

            <form action="process_insert.php" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div id="items" class="space-y-3">
                    <div class="grid grid-cols-12 gap-2 items-center">
                        <div class="col-span-6">
                            <select name="id_barang[]" class="w-full px-3 py-2 border rounded select-barang" required>
                                <option value="">-- Pilih Produk --</option>
                                <?php foreach ($barangs as $b): ?>
                                    <option value="<?= htmlspecialchars($b['id_barang']) ?>" data-price="<?= htmlspecialchars($b['harga_jual']) ?>"><?= htmlspecialchars($b['nama_barang']) ?> (<?= htmlspecialchars($b['id_barang']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-span-2"><input name="qty[]" type="number" min="1" value="1" class="w-full px-3 py-2 border rounded qty-input" required></div>
                        <div class="col-span-2"><input name="harga_jual[]" type="number" min="0" step="0.01" class="w-full px-3 py-2 border rounded price-input" placeholder="Harga Jual" required></div>
                        <div class="col-span-2 text-right"><button type="button" class="remove-row text-red-600">Hapus</button></div>
                    </div>
                </div>
                <div class="mt-3">
                    <button id="addRow" type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-blue-600 text-white">Tambah Item</button>
                </div>
                <div class="mt-6 text-right">
                    <button type="submit" class="px-6 py-2 rounded bg-indigo-600 text-white">Bayar & Simpan</button>
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
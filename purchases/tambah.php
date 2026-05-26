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
    <?php require_once '../includes/header.php'; ?>
    <title>Catat Pembelian Baru | IPOS Sistem Toko</title>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    <div class="main-content px-4 py-8 fade-in">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="package-plus" class="w-6 h-6 text-brand-600"></i>
                        Catat Transaksi Pembelian
                    </h1>
                    <p class="text-xs text-slate-500">Menambah stok barang dengan mencatat nota pembelian dari supplier.</p>
                </div>
                <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-650 hover:bg-slate-50 text-xs font-semibold shadow-sm transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
                </a>
            </div>

            <!-- Form Card -->
            <form id="purchaseForm" action="process_insert.php" method="POST" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-6">
                <!-- Meta data (Supplier & Tanggal) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-slate-700">Pilih Supplier</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="truck" class="w-4 h-4"></i></span>
                            <select name="id_supplier" required class="ipos-input pl-10 appearance-none bg-white pr-10">
                                <option value="">-- Pilih Supplier --</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= htmlspecialchars($s['id_supplier']) ?>"><?= htmlspecialchars($s['nama_supplier']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-slate-700">Tanggal Transaksi</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="calendar" class="w-4 h-4"></i></span>
                            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required class="ipos-input pl-10">
                        </div>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="border-t border-slate-100 pt-5">
                    <h3 class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-1.5">
                        <i data-lucide="list-checks" class="w-4.5 h-4.5 text-brand-600"></i> Daftar Item Restok
                    </h3>
                    
                    <!-- Table header for grid (desktop) -->
                    <div class="hidden sm:grid grid-cols-12 gap-3 px-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                        <div class="col-span-5">Nama Produk</div>
                        <div class="col-span-2">Qty</div>
                        <div class="col-span-2">Harga Beli Satuan</div>
                        <div class="col-span-2 text-right">Subtotal</div>
                        <div class="col-span-1 text-right">Aksi</div>
                    </div>

                    <div id="items" class="space-y-3 mb-4">
                        <!-- Dynamic Row Template -->
                        <div class="grid grid-cols-12 gap-3 items-center p-3 border border-slate-100 rounded-xl bg-slate-50/50 item-row">
                            <div class="col-span-12 sm:col-span-5">
                                <select name="id_barang[]" class="w-full px-3 py-2 border border-slate-200 rounded-lg select-barang text-xs outline-none focus:border-brand-500 bg-white" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($barangs as $b): ?>
                                        <option value="<?= htmlspecialchars($b['id_barang']) ?>" data-price="<?= htmlspecialchars($b['harga_beli']) ?>"><?= htmlspecialchars($b['nama_barang']) ?> (<?= htmlspecialchars($b['id_barang']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-span-6 sm:col-span-2">
                                <input name="qty[]" type="number" min="1" value="1" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs outline-none focus:border-brand-500 font-bold qty-input">
                            </div>
                            <div class="col-span-6 sm:col-span-2 relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] font-bold">Rp</span>
                                <input name="harga_beli[]" type="number" min="0" step="0.01" class="w-full pl-8 pr-3 py-2 border border-slate-200 rounded-lg text-xs outline-none focus:border-brand-500 price-input" placeholder="0">
                            </div>
                            <div class="col-span-8 sm:col-span-2">
                                <div class="subtotal-box px-3 py-2 rounded-lg bg-brand-50 text-brand-700 font-semibold text-right text-xs">Rp 0</div>
                            </div>
                            <div class="col-span-4 sm:col-span-1 text-right">
                                <button type="button" class="remove-row text-xs text-red-500 font-bold hover:underline select-none">Hapus</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100">
                        <button id="addRow" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-200 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Item
                        </button>
                        
                        <div class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100 text-xs">
                            <span class="text-slate-500 font-medium">Estimasi Total Pembelian:</span>
                            <span id="grandTotal" class="text-base font-extrabold text-brand-700">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6 border-t border-slate-100 text-right">
                    <button type="submit" class="ipos-btn-primary text-xs font-bold shadow-md shadow-brand-500/10">
                        <i data-lucide="save" class="w-4.5 h-4.5"></i> Simpan Transaksi Pembelian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function calculateTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#items .item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value || 0);
            const price = parseFloat(row.querySelector('.price-input').value || 0);
            const subtotal = qty * price;
            grandTotal += subtotal;
            
            const subtotalBox = row.querySelector('.subtotal-box');
            if (subtotalBox) {
                subtotalBox.textContent = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
            }
        });
        document.getElementById('grandTotal').textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    }

    document.addEventListener('click', function(e){
        if (e.target && e.target.classList && e.target.classList.contains('remove-row')) {
            const rowCount = document.querySelectorAll('#items .item-row').length;
            if (rowCount > 1) {
                e.target.closest('.item-row').remove();
                calculateTotals();
            } else {
                alert('Transaksi pembelian minimal harus mencantumkan 1 item barang.');
            }
        }
    });

    document.getElementById('addRow').addEventListener('click', function(){
        const template = document.querySelector('#items .item-row').outerHTML;
        document.getElementById('items').insertAdjacentHTML('beforeend', template);
        const newRow = document.querySelector('#items .item-row:last-child');
        if (newRow) {
            newRow.querySelector('select').value = '';
            newRow.querySelector('.qty-input').value = '1';
            newRow.querySelector('.price-input').value = '';
            newRow.querySelector('.subtotal-box').textContent = 'Rp 0';
        }
        calculateTotals();
    });

    // Auto-fill harga_beli when selecting product
    document.addEventListener('change', function(e){
        if (e.target && e.target.classList && e.target.classList.contains('select-barang')){
            const price = e.target.selectedOptions[0].dataset.price || '';
            const row = e.target.closest('.item-row');
            if (row) {
                const priceInput = row.querySelector('.price-input');
                if (priceInput) {
                    priceInput.value = price;
                    calculateTotals();
                }
            }
        }
    });

    // Recalculate totals on quantity or price typing
    document.addEventListener('input', function(e){
        if (e.target && (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input'))) {
            calculateTotals();
        }
    });

    calculateTotals();
    lucide.createIcons();
    </script>
</body>
</html>
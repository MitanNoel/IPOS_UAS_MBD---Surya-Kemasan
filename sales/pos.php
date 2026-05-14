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

            <form id="posForm" action="process_insert.php" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="mb-4 hidden md:grid md:grid-cols-12 gap-2 px-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <div class="md:col-span-5">Produk</div>
                    <div class="md:col-span-2">Qty</div>
                    <div class="md:col-span-2">Harga Jual</div>
                    <div class="md:col-span-2">Subtotal</div>
                    <div class="md:col-span-1 text-right">Aksi</div>
                </div>
                <div id="items" class="space-y-3">
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
                        <div class="col-span-8 md:col-span-2">
                            <div class="subtotal-box px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 font-semibold text-right">Rp 0</div>
                        </div>
                        <div class="col-span-4 md:col-span-1 text-right">
                            <button type="button" class="remove-row inline-flex items-center justify-center px-3 py-2 rounded-lg text-red-600 hover:bg-red-50">Hapus</button>
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <button id="addRow" type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white">Tambah Item</button>
                    <div class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3 border border-gray-100">
                        <span class="text-sm text-gray-500">Total</span>
                        <span id="grandTotal" class="text-xl font-bold text-gray-900">Rp 0</span>
                    </div>
                </div>
                <div class="mt-6 text-right">
                    <button id="payBtn" type="button" class="px-6 py-2 rounded-lg bg-indigo-600 text-white font-semibold">Bayar & Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="paymentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Konfirmasi Pembayaran</h2>
                    <p class="text-sm text-gray-500">Masukkan uang dari customer untuk menghitung kembalian.</p>
                </div>
                <button type="button" id="closePaymentModal" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-3 flex items-center justify-between">
                    <span class="text-sm text-indigo-700 font-medium">Total Belanja</span>
                    <span id="modalTotal" class="text-lg font-bold text-indigo-900">Rp 0</span>
                </div>
                <div>
                    <label for="paymentAmount" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Uang</label>
                    <input id="paymentAmount" type="number" min="0" step="0.01" class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Masukkan jumlah uang">
                    <p id="paymentError" class="mt-2 text-sm text-red-600 hidden">Jumlah uang harus sama atau lebih besar dari total.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Kembalian</p>
                        <p id="changeAmount" class="text-lg font-bold text-gray-900">Rp 0</p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-emerald-600 mb-1">Status</p>
                        <p id="paymentStatus" class="text-lg font-bold text-emerald-700">Siap disimpan</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
                <button type="button" id="cancelPayment" class="px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-700">Batal</button>
                <button type="button" id="confirmPayment" class="px-5 py-2 rounded-lg bg-indigo-600 text-white font-semibold disabled:opacity-50 disabled:cursor-not-allowed">Simpan Transaksi</button>
            </div>
        </div>
    </div>

    <script>
    const posForm = document.getElementById('posForm');
    const itemsContainer = document.getElementById('items');
    const grandTotalEl = document.getElementById('grandTotal');
    const modal = document.getElementById('paymentModal');
    const payBtn = document.getElementById('payBtn');
    const closePaymentModal = document.getElementById('closePaymentModal');
    const cancelPayment = document.getElementById('cancelPayment');
    const confirmPayment = document.getElementById('confirmPayment');
    const paymentAmount = document.getElementById('paymentAmount');
    const modalTotal = document.getElementById('modalTotal');
    const changeAmount = document.getElementById('changeAmount');
    const paymentError = document.getElementById('paymentError');
    const paymentStatus = document.getElementById('paymentStatus');

    function formatCurrency(value) {
        return 'Rp ' + Math.round(value || 0).toLocaleString('id-ID');
    }

    function getRowValue(row, selector) {
        const input = row.querySelector(selector);
        return input ? parseFloat(input.value || 0) : 0;
    }

    function updateRowSubtotal(row) {
        const qty = getRowValue(row, '.qty-input');
        const price = getRowValue(row, '.price-input');
        const subtotal = Math.max(0, qty * price);
        const subtotalBox = row.querySelector('.subtotal-box');
        if (subtotalBox) {
            subtotalBox.textContent = formatCurrency(subtotal);
        }
        return subtotal;
    }

    function updateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(function(row) {
            total += updateRowSubtotal(row);
        });
        grandTotalEl.textContent = formatCurrency(total);
        modalTotal.textContent = formatCurrency(total);

        const entered = parseFloat(paymentAmount.value || 0);
        updateChange(total, entered);
        return total;
    }

    function updateChange(total, entered) {
        const safeEntered = Number.isFinite(entered) ? entered : 0;
        const change = safeEntered - total;
        if (safeEntered < total) {
            changeAmount.textContent = formatCurrency(0);
            paymentStatus.textContent = 'Uang kurang';
            paymentStatus.className = 'text-lg font-bold text-red-600';
            paymentError.classList.remove('hidden');
            confirmPayment.disabled = true;
            return;
        }

        changeAmount.textContent = formatCurrency(change);
        paymentStatus.textContent = 'Siap disimpan';
        paymentStatus.className = 'text-lg font-bold text-emerald-700';
        paymentError.classList.add('hidden');
        confirmPayment.disabled = false;
    }

    function openModal() {
        const total = updateGrandTotal();
        if (total <= 0) {
            alert('Tambahkan minimal satu item terlebih dahulu.');
            return;
        }
        paymentAmount.value = '';
        changeAmount.textContent = formatCurrency(0);
        paymentStatus.textContent = 'Siap disimpan';
        paymentStatus.className = 'text-lg font-bold text-emerald-700';
        paymentError.classList.add('hidden');
        confirmPayment.disabled = true;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => paymentAmount.focus(), 0);
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('click', function(e){
        if (e.target && e.target.classList && e.target.classList.contains('remove-row')) {
            const row = e.target.closest('.item-row');
            if (row && document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                updateGrandTotal();
            }
        }
    });
    document.getElementById('addRow').addEventListener('click', function(){
        const template = document.querySelector('#items .item-row').outerHTML;
        document.getElementById('items').insertAdjacentHTML('beforeend', template);
        const newRow = document.querySelector('#items .item-row:last-child');
        if (newRow) {
            const subtotalBox = newRow.querySelector('.subtotal-box');
            if (subtotalBox) subtotalBox.textContent = formatCurrency(0);
        }
        updateGrandTotal();
    });

    document.addEventListener('change', function(e){
        if (e.target && e.target.classList && e.target.classList.contains('select-barang')){
            const price = e.target.selectedOptions[0].dataset.price || '';
            const row = e.target.closest('.grid');
            if (row) {
                const priceInput = row.querySelector('.price-input');
                if (priceInput) priceInput.value = price;
                updateGrandTotal();
            }
        }
    });

    document.addEventListener('input', function(e){
        if (e.target && (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input'))) {
            updateGrandTotal();
        }
        if (e.target === paymentAmount) {
            const total = updateGrandTotal();
            updateChange(total, parseFloat(paymentAmount.value || 0));
        }
    });

    payBtn.addEventListener('click', openModal);
    closePaymentModal.addEventListener('click', closeModal);
    cancelPayment.addEventListener('click', closeModal);

    paymentAmount.addEventListener('input', function(){
        const total = updateGrandTotal();
        updateChange(total, parseFloat(paymentAmount.value || 0));
    });

    confirmPayment.addEventListener('click', function(){
        const total = updateGrandTotal();
        const entered = parseFloat(paymentAmount.value || 0);
        if (!Number.isFinite(entered) || entered < total) {
            paymentError.classList.remove('hidden');
            confirmPayment.disabled = true;
            return;
        }
        closeModal();
        posForm.submit();
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    updateGrandTotal();

    lucide.createIcons();
    </script>
</body>
</html>
<?php
require_once '../auth/check_auth.php';
// Allow admin and kasir
if (!is_admin() && !is_kasir()) {
    header('Location: ../dashboard/index.php?error=access_denied');
    exit();
}
require_once '../config/database.php';

try {
    // Fetch products with their dynamically computed stock levels
    $stmt = $pdo->query("
        SELECT 
            b.id_barang, 
            b.nama_barang, 
            b.harga_jual,
            COALESCE(k.nama_kategori, 'Umum') as nama_kategori,
            (COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = b.id_barang), 0) - 
             COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = b.id_barang), 0)) as stok
        FROM barang b
        LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
        ORDER BY b.nama_barang ASC
    ");
    $barangs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/header.php'; ?>
    <title>POS | IPOS Sistem Toko</title>
    <style>
        .pos-grid-container {
            height: calc(100vh - 120px);
        }
        @media (max-width: 1023px) {
            .pos-grid-container {
                height: auto;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    
    <div class="main-content px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="w-6 h-6 text-brand-600"></i>
                    Point of Sale (POS)
                </h1>
                <p class="text-xs text-slate-500">Kasir: <strong class="text-slate-800"><?= htmlspecialchars($user_name) ?></strong> | Catat penjualan kasir dengan cepat.</p>
            </div>
            <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-650 hover:bg-slate-50 hover:text-slate-900 transition-colors shadow-sm text-xs font-semibold">
                <i data-lucide="history" class="w-4 h-4"></i> Riwayat Transaksi
            </a>
        </div>

        <!-- POS Workspace -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pos-grid-container">
            <!-- Left Panel: Product Catalog -->
            <div class="lg:col-span-7 flex flex-col bg-white rounded-2xl border border-slate-100 shadow-sm p-4 overflow-hidden h-full">
                <!-- Search & Filters -->
                <div class="mb-4 relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input 
                        type="text" 
                        id="searchProduct" 
                        placeholder="Cari nama barang atau kode produk..." 
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl outline-none focus:border-brand-500 focus:bg-white transition-all text-xs"
                    >
                </div>

                <!-- Catalog Grid -->
                <div class="flex-1 overflow-y-auto pr-1">
                    <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php foreach ($barangs as $b): ?>
                            <?php 
                                $stok = (int)$b['stok'];
                                $isOutOfStock = $stok <= 0;
                            ?>
                            <div 
                                class="product-card p-3 bg-white border rounded-xl hover:border-brand-500 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between group relative overflow-hidden select-none <?= $isOutOfStock ? 'border-slate-100 opacity-60 bg-slate-50/50 cursor-not-allowed' : 'border-slate-100' ?>"
                                data-id="<?= htmlspecialchars($b['id_barang']) ?>"
                                data-name="<?= htmlspecialchars($b['nama_barang']) ?>"
                                data-price="<?= htmlspecialchars($b['harga_jual']) ?>"
                                data-stock="<?= $stok ?>"
                                onclick="addProductToCart(this)"
                            >
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[9px] font-mono text-slate-400">#<?= htmlspecialchars($b['id_barang']) ?></span>
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-tight <?= $stok <= 0 ? 'bg-red-100 text-red-700' : ($stok < 6 ? 'bg-amber-100 text-amber-700' : 'bg-brand-50 text-brand-700') ?>">
                                            Stok: <?= $stok ?>
                                        </span>
                                    </div>
                                    <h3 class="text-xs font-bold text-slate-800 leading-snug group-hover:text-brand-650 transition-colors line-clamp-2">
                                        <?= htmlspecialchars($b['nama_barang']) ?>
                                    </h3>
                                    <p class="text-[9px] text-slate-450 mt-0.5 uppercase tracking-wider font-semibold">
                                        <?= htmlspecialchars($b['nama_kategori']) ?>
                                    </p>
                                </div>
                                <div class="mt-4 flex items-center justify-between pt-2 border-t border-slate-50">
                                    <span class="text-xs font-extrabold text-slate-900">
                                        Rp <?= number_format($b['harga_jual'], 0, ',', '.') ?>
                                    </span>
                                    <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-500 group-hover:bg-brand-600 group-hover:text-white flex items-center justify-center transition-colors">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Transaction Cart / Checkout -->
            <form id="posForm" action="process_insert.php" method="POST" class="lg:col-span-5 flex flex-col bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden h-full">
                <!-- Cart Header -->
                <div class="p-4 border-b border-slate-100 bg-slate-50/55 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-brand-600"></i>
                        Keranjang Belanja
                    </h2>
                    <button 
                        type="button" 
                        onclick="clearCart()" 
                        class="text-[10px] text-red-650 font-bold hover:underline flex items-center gap-1"
                    >
                        <i data-lucide="trash" class="w-3 h-3"></i> Kosongkan
                    </button>
                </div>

                <!-- Cart Items List -->
                <div id="cartList" class="flex-1 overflow-y-auto p-4 space-y-3">
                    <!-- Dynamic Cart Rows Injected Here -->
                    <div id="emptyCartMessage" class="h-full flex flex-col items-center justify-center text-center py-20 text-slate-400">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-center mb-3">
                            <i data-lucide="shopping-cart" class="w-6 h-6 text-slate-350"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-500">Keranjang Belanja Kosong</p>
                        <p class="text-[10px] text-slate-400 max-w-[200px] mt-1 leading-normal">Pilih barang dari katalog produk di sebelah kiri untuk ditambahkan.</p>
                    </div>
                </div>

                <!-- Checkout Summary Panel -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium">Subtotal</span>
                        <span id="cartSubtotal" class="text-sm font-semibold text-slate-800">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100/50 pt-2.5">
                        <span class="text-sm font-bold text-slate-900">Grand Total</span>
                        <span id="grandTotal" class="text-lg font-extrabold text-brand-700">Rp 0</span>
                    </div>

                    <button 
                        id="checkoutBtn"
                        type="button" 
                        onclick="openPaymentModal()"
                        class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-all shadow-md shadow-brand-500/10 hover:shadow-lg hover:shadow-brand-500/20 active:scale-[0.99] flex items-center justify-center gap-2 text-xs"
                        disabled
                    >
                        <i data-lucide="credit-card" class="w-4 h-4"></i> Proses Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden fade-in">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4.5 h-4.5 text-brand-600"></i>
                        Selesaikan Transaksi
                    </h2>
                    <p class="text-[10px] text-slate-400 mt-0.5">Input jumlah uang pembayaran untuk menghitung kembalian.</p>
                </div>
                <button type="button" onclick="closePaymentModal()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="px-6 py-5 space-y-4">
                <div class="rounded-xl bg-brand-50/50 border border-brand-100/50 px-4 py-3.5 flex items-center justify-between">
                    <span class="text-xs text-slate-600 font-medium">Tagihan Penjualan</span>
                    <span id="modalTotal" class="text-lg font-extrabold text-brand-700">Rp 0</span>
                </div>
                
                <div>
                    <label for="paymentAmount" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Uang Diterima (Cash)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-sm">Rp</span>
                        <input 
                            id="paymentAmount" 
                            type="number" 
                            min="0" 
                            step="0.01" 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white text-base font-bold transition-all"
                            placeholder="0"
                            oninput="calculateChange()"
                        >
                    </div>
                    <p id="paymentError" class="mt-2 text-xs text-red-650 font-semibold hidden flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Jumlah uang kurang dari total tagihan!
                    </p>
                </div>

                <!-- Shortcuts for Quick Cash -->
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="quickCashInput(5000)" class="py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-[10px] font-bold hover:bg-slate-100">Rp 5.000</button>
                    <button type="button" onclick="quickCashInput(10000)" class="py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-[10px] font-bold hover:bg-slate-100">Rp 10.000</button>
                    <button type="button" onclick="quickCashInput(20000)" class="py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-[10px] font-bold hover:bg-slate-100">Rp 20.000</button>
                    <button type="button" onclick="quickCashInput(50000)" class="py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-[10px] font-bold hover:bg-slate-100">Rp 50.000</button>
                    <button type="button" onclick="quickCashInput(100000)" class="py-1.5 bg-slate-50 border border-slate-100 rounded-lg text-[10px] font-bold hover:bg-slate-100">Rp 100.000</button>
                    <button type="button" id="exactCashBtn" class="py-1.5 bg-brand-50 border border-brand-100 rounded-lg text-[10px] font-bold hover:bg-brand-100 text-brand-700">Uang Pas</button>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3">
                        <p class="text-[9px] uppercase tracking-wide text-slate-400 font-bold mb-1">Kembalian</p>
                        <p id="changeAmount" class="text-base font-extrabold text-slate-900">Rp 0</p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 border border-emerald-100/70 px-4 py-3">
                        <p class="text-[9px] uppercase tracking-wide text-emerald-600 font-bold mb-1">Status Pembayaran</p>
                        <p id="paymentStatus" class="text-sm font-extrabold text-emerald-700">Siap</p>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-650 text-xs font-semibold hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="button" id="confirmPaymentBtn" onclick="submitPOSForm()" class="px-5 py-2.5 rounded-xl bg-brand-600 text-white text-xs font-bold hover:bg-brand-700 transition-colors flex items-center gap-1.5 shadow-md shadow-brand-500/10" disabled>
                    <i data-lucide="check" class="w-4 h-4"></i> Simpan Transaksi
                </button>
            </div>
        </div>
    </div>

    <script>
    // POS Cart State
    let cart = [];

    function formatCurrency(val) {
        return 'Rp ' + Math.round(val).toLocaleString('id-ID');
    }

    // Add Product to Cart
    function addProductToCart(element) {
        const id = element.dataset.id;
        const name = element.dataset.name;
        const price = parseFloat(element.dataset.price);
        const maxStock = parseInt(element.dataset.stock);

        if (maxStock <= 0) {
            alert('Produk ini habis stoknya!');
            return;
        }

        // Check if already in cart
        const existingItemIndex = cart.findIndex(item => item.id === id);
        if (existingItemIndex > -1) {
            if (cart[existingItemIndex].qty + 1 > maxStock) {
                alert(`Maksimal stok tercapai. Hanya tersedia ${maxStock} pcs.`);
                return;
            }
            cart[existingItemIndex].qty += 1;
        } else {
            cart.push({ id, name, price, qty: 1, maxStock });
        }

        renderCart();
    }

    // Render Cart view
    function renderCart() {
        const cartList = document.getElementById('cartList');
        const emptyMsg = document.getElementById('emptyCartMessage');
        const checkoutBtn = document.getElementById('checkoutBtn');

        // Clear display first
        const items = cartList.querySelectorAll('.cart-row');
        items.forEach(el => el.remove());

        if (cart.length === 0) {
            emptyMsg.classList.remove('hidden');
            emptyMsg.classList.add('flex');
            checkoutBtn.disabled = true;
            document.getElementById('cartSubtotal').textContent = formatCurrency(0);
            document.getElementById('grandTotal').textContent = formatCurrency(0);
            return;
        }

        emptyMsg.classList.add('hidden');
        emptyMsg.classList.remove('flex');
        checkoutBtn.disabled = false;

        let total = 0;
        cart.forEach((item, index) => {
            const subtotal = item.qty * item.price;
            total += subtotal;

            const rowHtml = `
                <div class="cart-row grid grid-cols-12 gap-2 items-center p-3 border border-slate-100 rounded-xl bg-slate-50/50 hover:bg-slate-50 transition-colors">
                    <div class="col-span-12 sm:col-span-6">
                        <p class="text-xs font-bold text-slate-800 line-clamp-1">${escapeHtml(item.name)}</p>
                        <span class="text-[9px] font-mono text-slate-400">#${item.id}</span>
                        <input type="hidden" name="id_barang[]" value="${item.id}">
                    </div>
                    <div class="col-span-6 sm:col-span-3 flex items-center gap-1.5">
                        <button type="button" onclick="updateQty(${index}, -1)" class="w-6 h-6 border rounded-lg bg-white hover:bg-slate-50 flex items-center justify-center text-xs font-bold select-none">-</button>
                        <input 
                            name="qty[]" 
                            type="number" 
                            min="1" 
                            max="${item.maxStock}" 
                            value="${item.qty}" 
                            class="w-10 text-center py-0.5 border rounded-lg text-xs font-bold"
                            onchange="onQtyInputChange(this, ${index})"
                        >
                        <button type="button" onclick="updateQty(${index}, 1)" class="w-6 h-6 border rounded-lg bg-white hover:bg-slate-50 flex items-center justify-center text-xs font-bold select-none">+</button>
                    </div>
                    <div class="col-span-6 sm:col-span-3 text-right flex flex-col justify-end items-end">
                        <input type="hidden" name="harga_jual[]" value="${item.price}">
                        <p class="text-xs font-extrabold text-slate-900">${formatCurrency(subtotal)}</p>
                        <button type="button" onclick="removeCartItem(${index})" class="text-[10px] text-red-500 font-semibold hover:underline mt-0.5 flex items-center gap-0.5">
                            Hapus
                        </button>
                    </div>
                </div>
            `;
            cartList.insertAdjacentHTML('beforeend', rowHtml);
        });

        document.getElementById('cartSubtotal').textContent = formatCurrency(total);
        document.getElementById('grandTotal').textContent = formatCurrency(total);
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Increments or decrements quantity
    function updateQty(index, offset) {
        const item = cart[index];
        const newQty = item.qty + offset;
        if (newQty <= 0) {
            removeCartItem(index);
        } else if (newQty <= item.maxStock) {
            item.qty = newQty;
            renderCart();
        } else {
            alert(`Stok produk terbatas. Hanya tersedia ${item.maxStock} pcs.`);
        }
    }

    // Handles manual quantity typing
    function onQtyInputChange(input, index) {
        let val = parseInt(input.value || 0);
        const item = cart[index];
        if (val <= 0 || isNaN(val)) {
            removeCartItem(index);
        } else if (val <= item.maxStock) {
            item.qty = val;
            renderCart();
        } else {
            alert(`Stok produk terbatas. Hanya tersedia ${item.maxStock} pcs.`);
            input.value = item.qty;
        }
    }

    // Remove single item
    function removeCartItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    // Empty entire cart
    function clearCart() {
        if (cart.length > 0 && confirm('Kosongkan semua barang belanjaan?')) {
            cart = [];
            renderCart();
        }
    }

    // Payment Logic
    function getCartTotal() {
        return cart.reduce((sum, item) => sum + (item.qty * item.price), 0);
    }

    function openPaymentModal() {
        const total = getCartTotal();
        if (total <= 0) return;

        document.getElementById('modalTotal').textContent = formatCurrency(total);
        document.getElementById('paymentAmount').value = '';
        document.getElementById('changeAmount').textContent = formatCurrency(0);
        document.getElementById('paymentStatus').textContent = 'Siap';
        document.getElementById('paymentStatus').className = 'text-sm font-extrabold text-slate-500';
        document.getElementById('paymentError').classList.add('hidden');
        document.getElementById('confirmPaymentBtn').disabled = true;

        // Exact cash button
        document.getElementById('exactCashBtn').onclick = function() {
            document.getElementById('paymentAmount').value = total;
            calculateChange();
        };

        const modal = document.getElementById('paymentModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => document.getElementById('paymentAmount').focus(), 150);
    }

    function closePaymentModal() {
        const modal = document.getElementById('paymentModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function quickCashInput(amount) {
        document.getElementById('paymentAmount').value = amount;
        calculateChange();
    }

    function calculateChange() {
        const total = getCartTotal();
        const payment = parseFloat(document.getElementById('paymentAmount').value || 0);
        const changeAmount = document.getElementById('changeAmount');
        const paymentStatus = document.getElementById('paymentStatus');
        const paymentError = document.getElementById('paymentError');
        const confirmBtn = document.getElementById('confirmPaymentBtn');

        if (isNaN(payment) || payment < total) {
            changeAmount.textContent = formatCurrency(0);
            paymentStatus.textContent = 'Uang Kurang';
            paymentStatus.className = 'text-sm font-extrabold text-red-600';
            paymentError.classList.remove('hidden');
            confirmBtn.disabled = true;
            return;
        }

        const change = payment - total;
        changeAmount.textContent = formatCurrency(change);
        paymentStatus.textContent = 'Pembayaran Lunas';
        paymentStatus.className = 'text-sm font-extrabold text-emerald-700';
        paymentError.classList.add('hidden');
        confirmBtn.disabled = false;
    }

    function submitPOSForm() {
        document.getElementById('posForm').submit();
    }

    // Client-side instant catalog searching
    document.getElementById('searchProduct').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            const name = card.dataset.name.toLowerCase();
            const id = card.dataset.id.toLowerCase();
            if (name.includes(query) || id.includes(query)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    });

    // Initialize
    lucide.createIcons();
    </script>
</body>
</html>
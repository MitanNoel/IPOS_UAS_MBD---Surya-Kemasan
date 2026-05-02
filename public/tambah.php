<?php
require_once '../config/database.php';

try {
    $q = 'SELECT * FROM kategori ORDER BY nama_kategori ASC';
    $s = $pdo->query($q);
    $k = $s->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Gagal mengambil data kategori: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang | Inventory System</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; }
    @keyframes slideIn { from { transform: translate(-50%, -60%); opacity: 0; } to { transform: translate(-50%, -50%); opacity: 1; } }
    .popup-animation { animation: slideIn 0.3s ease-out forwards; }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen relative">

    <!-- Popup Card -->
    <div id="errorPopup" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8 popup-animation border border-red-100">
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="alert-circle" class="w-10 h-10"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Harga Tidak Valid</h3>
                <p id="errorMessage" class="text-gray-500 text-sm mb-6 leading-relaxed">
                    Harga barang tidak boleh kurang dari atau sama dengan nol. Silakan periksa kembali input Anda.
                </p>
                <button onclick="closePopup()"
                    class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-red-100">
                    Mengerti, Saya Perbaiki
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 border-b border-gray-200 pb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Tambah Barang Baru</h1>
                <p class="text-gray-500 mt-1">Masukkan detail produk baru ke dalam sistem inventaris.</p>
            </div>
            <a href="index.php"
                class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-200 text-gray-600 font-medium rounded-lg hover:bg-gray-50 transition-all shadow-sm gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Information Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-emerald-50 p-6 rounded-xl border border-emerald-100">
                    <div class="flex items-center gap-3 mb-4 text-emerald-700 font-bold">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                        Validasi Data
                    </div>
                    <p class="text-sm text-emerald-600 leading-relaxed mb-4">
                        Pastikan <strong>ID Barang</strong> bersifat unik. Pilih <strong>Kategori</strong> yang sesuai
                        agar laporan stok akurat.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-2 text-sm uppercase tracking-wider text-emerald-600">Daftar
                        Kategori</h3>
                    <p class="text-xs text-gray-400 mb-3 italic">Nama kategori saat ini:</p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($k as $r): ?>
                        <span class="px-2 py-1 bg-gray-100 rounded text-[10px] text-gray-600 border border-gray-200">
                            <?= htmlspecialchars($r['nama_kategori']) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-8 py-4 border-b border-gray-100 flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i>
                        <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Formulir Entry
                            Barang</span>
                    </div>

                    <form id="insertForm" action="../process/insert.php" method="POST" class="p-8 space-y-6">

                        <!-- ID Barang -->
                        <div class="space-y-2">
                            <label for="id_barang" class="text-sm font-semibold text-gray-700">ID Barang / SKU</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="fingerprint"
                                        class="w-5 h-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors"></i>
                                </div>
                                <input type="text" id="id_barang" name="id_barang" required
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none text-gray-900 font-mono"
                                    placeholder="Misal: BRG-001">
                            </div>
                        </div>

                        <!-- Nama Barang -->
                        <div class="space-y-2">
                            <label for="nama_barang" class="text-sm font-semibold text-gray-700">Nama Lengkap
                                Barang</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="box"
                                        class="w-5 h-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors"></i>
                                </div>
                                <input type="text" id="nama_barang" name="nama_barang" required
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none text-gray-900"
                                    placeholder="Contoh: Plastik HD 15x30">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Harga Jual -->
                            <div class="space-y-2">
                                <label for="harga_jual" class="text-sm font-semibold text-gray-700 font-bold">Harga Jual
                                    (IDR)</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 font-bold text-sm italic">Rp</span>
                                    </div>
                                    <input type="number" id="harga_jual" name="harga_jual" required
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none text-gray-900 font-bold"
                                        placeholder="0">
                                </div>
                            </div>

                            <!-- Harga Beli -->
                            <div class="space-y-2">
                                <label for="harga_beli" class="text-sm font-semibold text-gray-700">Harga Beli
                                    (IDR)</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 font-bold text-sm italic">Rp</span>
                                    </div>
                                    <input type="number" id="harga_beli" name="harga_beli" required
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none text-gray-900"
                                        placeholder="0">
                                </div>
                            </div>
                        </div>

                        <!-- Kategori (Sekarang menggunakan SELECT) -->
                        <div class="space-y-2">
                            <label for="id_kategori"
                                class="text-sm font-semibold text-gray-700 uppercase tracking-tight">Kategori
                                Produk</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="tag"
                                        class="w-5 h-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors"></i>
                                </div>
                                <select id="id_kategori" name="id_kategori" required
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none text-gray-900 appearance-none bg-white">
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <?php foreach ($list_kategori as $kat): ?>
                                    <option value="<?= $kat['id_kategori'] ?>">
                                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-6 border-t border-gray-100 flex items-center gap-3">
                            <button type="submit"
                                class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition-all shadow-md shadow-emerald-100 hover:shadow-lg hover:-translate-y-0.5 gap-2">
                                <i data-lucide="save" class="w-5 h-5"></i>
                                Simpan Data Barang
                            </button>
                            <a href="index.php"
                                class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold rounded-lg transition-all">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    const f=document.getElementById('insertForm'),p=document.getElementById('errorPopup');
    f.addEventListener('submit',e=>{const j=parseFloat(document.getElementById('harga_jual').value),b=parseFloat(document.getElementById('harga_beli').value);if(j<=0||b<=0){e.preventDefault();p.classList.remove('hidden');}});
    function closePopup(){p.classList.add('hidden');}
    lucide.createIcons();
    </script>
</body>

</html>
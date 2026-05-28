<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

try {
    // Ambil data barang yang akan diedit
    $sql = "SELECT * FROM barang WHERE id_barang = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("Data barang tidak ditemukan");
    }

    // Ambil semua kategori untuk dropdown
    $sql_kat = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
    $list_kategori = $pdo->query($sql_kat)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Ubah Produk | IPOS Toko Surya Kemasan</title>
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen">
    
    <!-- Navbar -->
    <?php require_once '../includes/navbar.php'; ?>

    <div class="main-content px-4 py-8 fade-in">
        <!-- Error Popup (Validasi Harga) -->
        <div id="errorPopup" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 backdrop-blur-sm">
            <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl p-8 border border-slate-100 text-center fade-in">
                <div class="w-16 h-16 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-red-100">
                    <i data-lucide="alert-circle" class="w-8 h-8"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-950 mb-2">Input Tidak Valid</h3>
                <p class="text-slate-500 text-xs mb-6">Harga jual dan harga beli harus bernilai positif (lebih besar dari nol).</p>
                <button onclick="document.getElementById('errorPopup').classList.add('hidden')"
                    class="w-full py-2.5 bg-red-650 hover:bg-red-750 text-white font-bold rounded-xl transition-all shadow-md shadow-red-500/10">
                    Perbaiki Data
                </button>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4 border-b border-slate-200 pb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-6 h-6 text-brand-600"></i>
                        Perbarui Data Produk
                    </h1>
                    <p class="text-slate-500 text-xs mt-1">Ubah detail barang untuk SKU #<strong class="font-mono text-slate-800"><?= htmlspecialchars($data['kode_barang']) ?></strong>.</p>
                </div>
                <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-650 hover:bg-slate-50 text-xs font-semibold shadow-sm transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Sidebar Info -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100">
                        <h3 class="text-blue-800 font-bold flex items-center gap-2 mb-2 text-xs uppercase tracking-wider">
                            <i data-lucide="info" class="w-4.5 h-4.5 text-blue-650"></i> Log Identitas
                        </h3>
                        <p class="text-xs text-blue-700 leading-relaxed">SKU ID tidak dapat diubah karena merupakan kunci unik primer di database.</p>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="bg-slate-50/50 px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-brand-600"></i>
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Formulir Update Barang</span>
                        </div>

                        <form id="editForm" action="../process/update.php" method="POST" class="p-6 space-y-5">
                            <input type="hidden" name="id_barang" value="<?= htmlspecialchars($data['id_barang']) ?>">

                            <!-- Nama Barang -->
                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-700">Nama Lengkap Barang</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="package" class="w-4 h-4"></i></span>
                                    <input 
                                        type="text" 
                                        name="nama_barang" 
                                        value="<?= htmlspecialchars($data['nama_barang']) ?>" 
                                        required
                                        class="ipos-input pl-10"
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Harga Beli -->
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold text-slate-700">Harga Beli Dasar (Rp)</label>
                                    <input 
                                        type="number" 
                                        id="harga_beli" 
                                        name="harga_beli" 
                                        value="<?= htmlspecialchars($data['harga_beli']) ?>" 
                                        required
                                        class="ipos-input"
                                    >
                                </div>

                                <!-- Harga Jual -->
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold text-slate-700 font-bold text-brand-650">Harga Jual Kasir (Rp)</label>
                                    <input 
                                        type="number" 
                                        id="harga_jual" 
                                        name="harga_jual" 
                                        value="<?= htmlspecialchars($data['harga_jual']) ?>" 
                                        required
                                        class="ipos-input font-bold text-brand-700 focus:border-brand-600 focus:ring-brand-200"
                                    >
                                </div>
                            </div>

                            <!-- Kategori -->
                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-700">Kategori Produk</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="tag" class="w-4 h-4"></i></span>
                                    <select 
                                        name="id_kategori" 
                                        required
                                        class="ipos-input pl-10 appearance-none bg-white pr-10"
                                    >
                                        <?php foreach ($list_kategori as $kat): ?>
                                        <option value="<?= $kat['id_kategori'] ?>" <?= ($kat['id_kategori'] == $data['id_kategori']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="pt-6 border-t border-slate-100 flex items-center gap-3">
                                <button 
                                    type="submit"
                                    class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-all shadow-md shadow-brand-500/10 hover:shadow-lg flex items-center justify-center gap-2 text-xs"
                                >
                                    <i data-lucide="save" class="w-4.5 h-4.5"></i> Simpan Perubahan
                                </button>
                                <a 
                                    href="index.php"
                                    class="px-5 py-3 bg-slate-100 text-slate-650 font-bold rounded-xl hover:bg-slate-200 transition-colors text-xs text-center"
                                >
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const form = document.getElementById('editForm');
    const popup = document.getElementById('errorPopup');
    
    form.addEventListener('submit', function(e) {
        const jual = parseFloat(document.getElementById('harga_jual').value);
        const beli = parseFloat(document.getElementById('harga_beli').value);
        if (jual <= 0 || beli <= 0) {
            e.preventDefault();
            popup.classList.remove('hidden');
            popup.classList.add('flex');
        }
    });
    lucide.createIcons();
    </script>
</body>

</html>
<?php
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang | Inventory System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }

    @keyframes slideIn {
        from {
            transform: translate(-50%, -60%);
            opacity: 0;
        }

        to {
            transform: translate(-50%, -50%);
            opacity: 1;
        }
    }

    .popup-animation {
        animation: slideIn 0.3s ease-out forwards;
    }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen">

    <!-- Popup Card -->
    <div id="errorPopup" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8 popup-animation border border-red-100 text-center">
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="alert-circle" class="w-10 h-10"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Input Tidak Valid</h3>
            <p class="text-gray-500 text-sm mb-6">Harga barang tidak boleh kurang dari atau sama dengan nol.</p>
            <button onclick="document.getElementById('errorPopup').classList.add('hidden')"
                class="w-full py-3 bg-red-600 text-white font-bold rounded-xl transition-all shadow-lg shadow-red-100">
                Perbaiki Data
            </button>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 border-b border-gray-200 pb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Perbarui Produk</h1>
                <p class="text-gray-500 mt-1">Ubah detail barang untuk SKU #<?= htmlspecialchars($id) ?>.</p>
            </div>
            <a href="index.php"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-600 font-medium rounded-lg hover:bg-gray-50 shadow-sm gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-blue-50 p-6 rounded-xl border border-blue-100">
                    <h3 class="text-blue-700 font-bold flex items-center gap-2 mb-2">
                        <i data-lucide="info" class="w-5 h-5"></i> Log Identitas
                    </h3>
                    <p class="text-xs text-blue-600 leading-relaxed">SKU ID tidak dapat diubah karena merupakan kunci
                        unik di database.</p>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <form id="editForm" action="../process/update.php" method="POST" class="p-8 space-y-6">
                        <input type="hidden" name="id_barang" value="<?= htmlspecialchars($data['id_barang']) ?>">

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Nama Barang</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="package"
                                        class="w-5 h-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                </div>
                                <input type="text" name="nama_barang"
                                    value="<?= htmlspecialchars($data['nama_barang']) ?>" required
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-700 font-bold text-blue-600">Harga Jual
                                    (Rp)</label>
                                <input type="number" id="harga_jual" name="harga_jual"
                                    value="<?= htmlspecialchars($data['harga_jual']) ?>" required
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none font-bold">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-700">Harga Beli (Rp)</label>
                                <input type="number" id="harga_beli" name="harga_beli"
                                    value="<?= htmlspecialchars($data['harga_beli']) ?>" required
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Pilih Kategori</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="tag"
                                        class="w-5 h-5 text-gray-400 group-focus-within:text-blue-500"></i>
                                </div>
                                <select name="id_kategori" required
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none appearance-none bg-white">
                                    <?php foreach ($list_kategori as $kat): ?>
                                    <option value="<?= $kat['id_kategori'] ?>"
                                        <?= ($kat['id_kategori'] == $data['id_kategori']) ? 'selected' : '' ?>>
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

                        <div class="pt-6 border-t border-gray-100 flex items-center gap-3">
                            <button type="submit"
                                class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-all shadow-md gap-2 flex items-center justify-center">
                                <i data-lucide="save" class="w-5 h-5"></i> Simpan Perubahan
                            </button>
                            <a href="index.php"
                                class="px-6 py-3 bg-gray-100 text-gray-600 font-semibold rounded-lg hover:bg-gray-200 transition-all text-center">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    const form = document.getElementById('editForm');
    form.addEventListener('submit', function(e) {
        const jual = parseFloat(document.getElementById('harga_jual').value);
        const beli = parseFloat(document.getElementById('harga_beli').value);
        if (jual <= 0 || beli <= 0) {
            e.preventDefault();
            document.getElementById('errorPopup').classList.remove('hidden');
        }
    });
    lucide.createIcons();
    </script>
</body>

</html>
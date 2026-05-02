<?php
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$i = $_GET['id'];

try {
    $q = 'SELECT b.*, k.nama_kategori
          FROM barang b
          LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
          WHERE b.id_barang = :id';
    $s = $pdo->prepare($q);
    $s->bindParam(':id', $i);
    $s->execute();
    $d = $s->fetch(PDO::FETCH_ASSOC);

    if (!$d) {
        die('Data barang tidak ditemukan');
    }
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Hapus | Inventory System</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <!-- Kartu Konfirmasi Modern -->
        <div class="bg-white rounded-2xl shadow-2xl border border-red-100 overflow-hidden transform transition-all">
            <!-- Bagian Header Peringatan -->
            <div class="bg-red-50 px-8 py-10 text-center">
                <div
                    class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-red-50">
                    <i data-lucide="trash-2" class="w-10 h-10"></i>
                </div>
                <h2 class="text-2xl font-extrabold text-gray-900 leading-tight">Hapus Barang?</h2>
                <p class="text-red-600/70 text-sm mt-2 font-medium uppercase tracking-widest text-[10px]">Tindakan ini
                    permanen dan tidak bisa dibatalkan</p>
            </div>

            <!-- Detail Barang -->
            <div class="px-8 py-6 border-y border-gray-50">
                <div class="bg-gray-50 p-5 rounded-xl space-y-4">
                    <div class="flex justify-between items-start text-sm">
                        <span class="text-gray-400 font-medium text-xs uppercase tracking-tighter">Nama Barang</span>
                        <span
                            class="font-bold text-gray-900 text-right"><?= htmlspecialchars($d['nama_barang']) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-400 font-medium text-xs uppercase tracking-tighter">SKU / ID</span>
                        <span
                            class="font-mono bg-white px-2 py-1 rounded border border-gray-200 text-[10px] font-bold text-gray-600">
                            #<?= htmlspecialchars($d['id_barang']) ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-400 font-medium text-xs uppercase tracking-tighter">Kategori</span>
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-[11px] font-bold uppercase">
                            <?= htmlspecialchars($d['nama_kategori'] ?? 'Tanpa Kategori') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="p-8 space-y-3">
                <!-- Action kembali ke path relatif -->
                <a href="../process/delete.php?id=<?= urlencode($d['id_barang']) ?>"
                    class="w-full py-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-red-100 flex items-center justify-center gap-2 group">
                    <i data-lucide="check-circle-2" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                    Ya, Hapus Sekarang
                </a>

                <!-- Link kembali ke index relatif -->
                <a href="index.php"
                    class="w-full py-4 bg-gray-50 hover:bg-gray-100 text-gray-500 font-bold rounded-xl transition-all flex items-center justify-center border border-gray-200">
                    Batalkan
                </a>
            </div>
        </div>

        <p class="text-center text-gray-400 text-[10px] mt-6 font-medium uppercase tracking-widest">
            Sistem Inventaris • Manajemen Data Kelompok
        </p>
    </div>

    <script>
    lucide.createIcons();
    </script>
</body>

</html>
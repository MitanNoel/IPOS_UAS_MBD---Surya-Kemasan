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
    // Mengambil detail barang lengkap dengan nama kategori
    $sql = "SELECT b.*, k.nama_kategori 
            FROM barang b 
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
            WHERE id_barang = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Konfirmasi Hapus Produk | IPOS Sistem Toko</title>
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full fade-in">
        <!-- Confirmation Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-red-100 overflow-hidden">
            <!-- Header Warning Section -->
            <div class="bg-red-50/70 border-b border-red-100/50 px-6 py-8 text-center">
                <div class="w-16 h-16 bg-red-100 text-red-650 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-red-200 shadow-inner">
                    <i data-lucide="trash-2" class="w-8 h-8"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-900 leading-tight">Hapus Produk?</h2>
                <p class="text-red-700 font-semibold text-[9px] uppercase tracking-wider mt-1.5">Tindakan ini tidak bisa dibatalkan</p>
            </div>

            <!-- Product Details Summary -->
            <div class="px-6 py-5 border-b border-slate-100">
                <div class="bg-slate-50 rounded-xl p-4 space-y-3.5 text-xs">
                    <div class="flex justify-between items-start">
                        <span class="text-slate-400 font-medium">Nama Produk</span>
                        <span class="font-bold text-slate-800 text-right max-w-[180px] truncate" title="<?= htmlspecialchars($data['nama_barang']) ?>"><?= htmlspecialchars($data['nama_barang']) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">SKU Kode</span>
                        <span class="font-mono bg-white px-2 py-0.5 rounded border border-slate-200 text-[10px] font-bold text-slate-650">
                            #<?= htmlspecialchars($data['id_barang']) ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Kategori</span>
                        <span class="px-2 py-0.5 bg-brand-50 text-brand-700 border border-brand-100/50 rounded-lg text-[10px] font-bold uppercase">
                            <?= htmlspecialchars($data['nama_kategori'] ?? 'Umum') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="p-6 space-y-2">
                <form action="../process/delete.php" method="POST">
                    <input type="hidden" name="id_barang" value="<?= htmlspecialchars($data['id_barang']) ?>">
                    <button 
                        type="submit"
                        class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-md shadow-red-500/10 hover:shadow-lg flex items-center justify-center gap-1.5 text-xs"
                    >
                        <i data-lucide="check" class="w-4 h-4"></i> Hapus Sekarang
                    </button>
                </form>

                <a 
                    href="index.php"
                    class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-650 font-bold rounded-xl transition-colors flex items-center justify-center border border-slate-200 text-xs"
                >
                    Batalkan
                </a>
            </div>
        </div>

        <p class="text-center text-slate-400 text-[9px] mt-6 font-bold uppercase tracking-widest">
            IPOS Sistem Toko &bull; Master Data
        </p>
    </div>

    <script>
    lucide.createIcons();
    </script>
</body>

</html>
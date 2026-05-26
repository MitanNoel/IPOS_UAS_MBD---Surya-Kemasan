<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare('SELECT * FROM supplier WHERE id_supplier = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$supplier) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Hapus Supplier | IPOS Toko Surya Kemasan</title>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full fade-in">
        <!-- Confirmation Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-red-100 overflow-hidden">
            <!-- Header warning section -->
            <div class="bg-red-50/70 border-b border-red-100/50 px-6 py-8 text-center">
                <div class="w-16 h-16 bg-red-100 text-red-650 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-red-205 shadow-inner">
                    <i data-lucide="trash-2" class="w-8 h-8"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-900 leading-tight">Hapus Supplier?</h2>
                <p class="text-red-700 font-semibold text-[9px] uppercase tracking-wider mt-1.5">Tindakan ini tidak bisa dibatalkan</p>
            </div>

            <!-- Details -->
            <div class="px-6 py-5 border-b border-slate-100">
                <div class="bg-slate-50 rounded-xl p-4 space-y-3.5 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Nama Supplier</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($supplier['nama_supplier']) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Nomor Telepon</span>
                        <span class="font-mono bg-white px-2 py-0.5 rounded border border-slate-200 text-[10px] font-bold text-slate-650">
                            <?= htmlspecialchars($supplier['no_telp']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="p-6 space-y-2">
                <form action="process_delete.php" method="POST">
                    <input type="hidden" name="id_supplier" value="<?= htmlspecialchars($supplier['id_supplier']) ?>">
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
    
    <script>lucide.createIcons();</script>
</body>
</html>

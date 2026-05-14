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
        header('Location: index.php?status=error');
        exit();
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
    <title>Edit Supplier | Sistem Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .main-content { margin-left: 16rem; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    <div class="main-content px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Supplier</h1>
                    <p class="text-gray-500 mt-1">Perbarui informasi supplier yang tersimpan.</p>
                </div>
                <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Data Supplier</h2>
                </div>
                <form action="process_update.php" method="POST" class="p-6 grid grid-cols-1 gap-5">
                    <input type="hidden" name="id_supplier" value="<?= htmlspecialchars($supplier['id_supplier']) ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="nama_supplier">Nama Supplier</label>
                        <input id="nama_supplier" name="nama_supplier" type="text" required value="<?= htmlspecialchars($supplier['nama_supplier']) ?>" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="no_telp">No. Telepon</label>
                        <input id="no_telp" name="no_telp" type="text" required value="<?= htmlspecialchars($supplier['no_telp']) ?>" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors">
                            <i data-lucide="save" class="w-5 h-5"></i>
                            Simpan Perubahan
                        </button>
                        <a href="index.php" class="px-5 py-3 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>

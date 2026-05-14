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
    $stmt = $pdo->prepare('SELECT * FROM user WHERE id_user = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus User | Sistem Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl border border-red-100 overflow-hidden">
        <div class="bg-red-50 px-8 py-10 text-center">
            <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="user-x" class="w-10 h-10"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-gray-900">Hapus User?</h2>
            <p class="text-red-600/70 text-sm mt-2 uppercase tracking-widest text-[10px]">Tindakan ini permanen</p>
        </div>
        <div class="px-8 py-6 border-y border-gray-50 space-y-4">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-400 font-medium text-xs uppercase tracking-tighter">Nama</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($user['nama_user']) ?></span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-400 font-medium text-xs uppercase tracking-tighter">Username</span>
                <span class="font-mono bg-gray-50 px-2 py-1 rounded border border-gray-200 text-xs text-gray-700"><?= htmlspecialchars($user['username']) ?></span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-400 font-medium text-xs uppercase tracking-tighter">Role</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                    <?= htmlspecialchars($user['role']) ?>
                </span>
            </div>
        </div>
        <div class="px-8 py-6 flex items-center gap-3">
            <form action="process_delete.php" method="POST" class="flex-1">
                <input type="hidden" name="id_user" value="<?= htmlspecialchars($user['id_user']) ?>">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                    Ya, Hapus
                </button>
            </form>
            <a href="index.php" class="px-5 py-3 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">Batal</a>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>

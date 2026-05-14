<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

$currentUserId = (int) ($_SESSION['user_id'] ?? 0);

try {
    $stmt = $pdo->query("SELECT * FROM user ORDER BY id_user ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}

$status = $_GET['status'] ?? '';
$statusMessages = [
    'created' => 'User berhasil ditambahkan.',
    'updated' => 'User berhasil diperbarui.',
    'deleted' => 'User berhasil dihapus.',
    'error' => 'Terjadi kesalahan saat memproses data user.'
];
$message = $statusMessages[$status] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User | Sistem Toko</title>
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
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Manajemen User</h1>
                    <p class="text-gray-500 mt-1">Kelola akun admin dan kasir dari tabel user.</p>
                </div>
                <a href="tambah.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition-colors">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    Tambah User
                </a>
            </div>

            <?php if ($message): ?>
            <div class="mb-6 px-4 py-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700">
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">ID</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Nama</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Username</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Role</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">Belum ada user.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-sm text-gray-500">#<?= htmlspecialchars($user['id_user']) ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-900"><?= htmlspecialchars($user['nama_user']) ?></td>
                                <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($user['username']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="edit.php?id=<?= urlencode($user['id_user']) ?>" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50" title="Edit">
                                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                                        </a>
                                        <?php if ((int) $user['id_user'] !== $currentUserId): ?>
                                        <a href="hapus.php?id=<?= urlencode($user['id_user']) ?>" class="p-2 rounded-lg text-red-600 hover:bg-red-50" title="Hapus">
                                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>

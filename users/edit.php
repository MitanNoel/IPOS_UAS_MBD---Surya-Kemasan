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
    <?php require_once '../includes/header.php'; ?>
    <title>Edit Pengguna | IPOS Sistem Toko</title>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    
    <div class="main-content px-4 py-8 fade-in">
        <div class="max-w-xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="edit" class="w-5.5 h-5.5 text-brand-600"></i>
                        Edit Data Pengguna
                    </h1>
                    <p class="text-xs text-slate-500">Perbarui rincian login untuk user #<?= htmlspecialchars($id) ?>.</p>
                </div>
                <a href="index.php" class="px-4 py-2 border rounded-xl bg-white text-slate-650 hover:bg-slate-50 text-xs font-semibold shadow-sm transition-colors">
                    Kembali
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="bg-slate-50/50 px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="file-edit" class="w-4 h-4 text-brand-600"></i>
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Formulir Edit Pengguna</span>
                </div>

                <form action="process_update.php" method="POST" class="p-6 space-y-5">
                    <input type="hidden" name="id_user" value="<?= htmlspecialchars($user['id_user']) ?>">
                    
                    <!-- Nama Lengkap -->
                    <div class="space-y-2">
                        <label for="nama_user" class="text-xs font-semibold text-slate-700">Nama Lengkap</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="user" class="w-4 h-4"></i></span>
                            <input 
                                type="text" 
                                id="nama_user" 
                                name="nama_user" 
                                required 
                                value="<?= htmlspecialchars($user['nama_user']) ?>" 
                                class="ipos-input pl-10"
                            >
                        </div>
                    </div>

                    <!-- Username -->
                    <div class="space-y-2">
                        <label for="username" class="text-xs font-semibold text-slate-700">Username Login</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="user-check" class="w-4 h-4"></i></span>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required 
                                value="<?= htmlspecialchars($user['username']) ?>" 
                                class="ipos-input pl-10 lowercase font-semibold text-slate-700"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <label for="password" class="text-xs font-semibold text-slate-700">Password Baru</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="key" class="w-4 h-4"></i></span>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="ipos-input pl-10"
                                placeholder="Kosongkan jika password tidak diubah"
                            >
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="space-y-2">
                        <label for="role" class="text-xs font-semibold text-slate-700">Hak Akses Role</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="shield" class="w-4 h-4"></i></span>
                            <select 
                                id="role" 
                                name="role" 
                                required 
                                class="ipos-input pl-10 appearance-none bg-white pr-10"
                            >
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="kasir" <?= $user['role'] === 'kasir' ? 'selected' : '' ?>>Kasir</option>
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
                            class="px-5 py-3 bg-slate-100 text-slate-650 font-bold rounded-xl hover:bg-slate-200 transition-colors text-xs text-center border border-slate-200"
                        >
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    lucide.createIcons();
    </script>
</body>
</html>

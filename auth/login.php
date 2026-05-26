<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard/index.php');
    exit();
}

// Get error message from URL if exists
$error_msg = $_GET['error'] ?? '';
$error_messages = [
    'invalid_credentials' => 'Username atau password salah',
    'required_fields' => 'Username dan password harus diisi',
    'session_expired' => 'Sesi Anda telah berakhir, silakan login kembali'
];
$error_display = isset($error_messages[$error_msg]) ? $error_messages[$error_msg] : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Login | IPOS Sistem Toko</title>
    <style>
        .login-gradient {
            background: linear-gradient(135deg, #0284c7 0%, #0c4a6e 100%);
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md fade-in">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
            <!-- Header -->
            <div class="login-gradient p-8 text-white text-center relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
                <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
                
                <div class="w-16 h-16 bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 backdrop-blur-sm shadow-inner">
                    <i data-lucide="store" class="w-8 h-8 text-white"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight">Sistem IPOS Toko</h1>
                <p class="text-sky-100/80 text-xs mt-1">Manajemen Inventaris & Penjualan</p>
            </div>

            <!-- Login Form -->
            <div class="p-8">
                <?php if ($error_display): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-650 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <h3 class="font-semibold text-red-950 text-sm">Login Gagal</h3>
                        <p class="text-red-700 text-xs mt-0.5"><?= htmlspecialchars($error_display) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="process_login.php" class="space-y-5">
                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-xs font-semibold text-slate-700 mb-2 uppercase tracking-wider">
                            <i data-lucide="user" class="inline w-3.5 h-3.5 mr-1 text-slate-400"></i>Username
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Masukkan username Anda"
                            class="ipos-input"
                            required
                        >
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-2 uppercase tracking-wider">
                            <i data-lucide="lock" class="inline w-3.5 h-3.5 mr-1 text-slate-400"></i>Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan password Anda"
                            class="ipos-input"
                            required
                        >
                    </div>

                    <!-- Login Button -->
                    <button 
                        type="submit"
                        class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition-all shadow-md shadow-brand-500/10 hover:shadow-lg hover:shadow-brand-500/20 active:scale-[0.99] mt-6 flex items-center justify-center gap-2"
                    >
                        <i data-lucide="log-in" class="w-5 h-5"></i>Masuk ke Sistem
                    </button>
                </form>

                <!-- Demo Credentials Info -->
                <div class="mt-8 p-4 bg-slate-50 border border-slate-100 rounded-xl">
                    <p class="text-xs text-brand-700 font-semibold mb-2 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i> Kredensial Demo:
                    </p>
                    <div class="text-[11px] text-slate-650 space-y-2">
                        <div class="flex items-center justify-between bg-white px-3 py-1.5 rounded-lg border border-slate-100">
                            <span><strong>Admin:</strong> <code class="text-brand-600">admin</code></span>
                            <span class="text-slate-400">pass: <code class="text-slate-700 font-medium">12345</code></span>
                        </div>
                        <div class="flex items-center justify-between bg-white px-3 py-1.5 rounded-lg border border-slate-100">
                            <span><strong>Kasir:</strong> <code class="text-brand-600">kasir</code></span>
                            <span class="text-slate-400">pass: <code class="text-slate-700 font-medium">12345</code></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 text-center text-[10px] text-slate-400">
                <p>&copy; 2026 IPOS Sistem Toko. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>

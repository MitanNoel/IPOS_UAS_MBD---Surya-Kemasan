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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Manajemen Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .login-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="login-gradient p-8 text-white text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="store" class="w-8 h-8"></i>
                </div>
                <h1 class="text-3xl font-bold">Sistem Toko</h1>
                <p class="text-purple-100 mt-2">Manajemen Inventaris & Penjualan</p>
            </div>

            <!-- Login Form -->
            <div class="p-8">
                <?php if ($error_display): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <h3 class="font-semibold text-red-900">Login Gagal</h3>
                        <p class="text-red-700 text-sm mt-1"><?= htmlspecialchars($error_display) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="process_login.php" class="space-y-5">
                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            <i data-lucide="user" class="inline w-4 h-4 mr-1"></i>Username
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Masukkan username Anda"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all outline-none"
                            required
                        >
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i data-lucide="lock" class="inline w-4 h-4 mr-1"></i>Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan password Anda"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all outline-none"
                            required
                        >
                    </div>

                    <!-- Login Button -->
                    <button 
                        type="submit"
                        class="w-full py-3 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl mt-6 flex items-center justify-center gap-2"
                    >
                        <i data-lucide="log-in" class="w-5 h-5"></i>Login
                    </button>
                </form>

                <!-- Demo Credentials Info -->
                <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-xs text-blue-600 font-semibold mb-2">Demo Credentials:</p>
                    <div class="text-xs text-blue-700 space-y-1">
                        <p><strong>Admin:</strong> username: <code class="bg-white px-2 py-1 rounded">admin</code> | password: <code class="bg-white px-2 py-1 rounded">12345</code></p>
                        <p><strong>Kasir:</strong> username: <code class="bg-white px-2 py-1 rounded">kasir</code> | password: <code class="bg-white px-2 py-1 rounded">12345</code></p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-4 bg-gray-50 border-t border-gray-200 text-center text-xs text-gray-500">
                <p>© 2026 Sistem Toko. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>

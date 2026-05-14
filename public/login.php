<?php
require_once '../config/app.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

$status = $_GET['status'] ?? '';
$message = $_GET['msg'] ?? 'Masuk dengan akun admin atau kasir untuk melanjutkan.';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Toko Surya Kemasan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    :root {
        --ipos-blue: #337ab7;
        --ipos-blue-dark: #214f7d;
    }

    body {
        font-family: 'Inter', sans-serif;
    }

    .login-panel {
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.2), transparent 28%),
            radial-gradient(circle at bottom right, rgba(51, 122, 183, 0.25), transparent 35%),
            linear-gradient(135deg, #10233d 0%, #17314f 48%, #0f172a 100%);
    }
    </style>
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen grid lg:grid-cols-[1.08fr_0.92fr]">
        <div class="login-panel hidden lg:flex flex-col justify-between border-r border-white/10 p-12 text-white">
            <div>
                <p class="text-sm uppercase tracking-[0.4em] text-sky-200/80">Toko Surya Kemasan</p>
                <h1 class="mt-6 max-w-xl text-5xl font-black leading-tight">Tampilan admin yang akrab seperti iPos 5, lalu dipoles lebih rapi.</h1>
                <p class="mt-6 max-w-lg text-slate-300 leading-7">Masuk untuk mengelola master data, menyiapkan transaksi penjualan, dan membuka ruang untuk pembelian, persediaan, dan laporan.</p>
            </div>

            <div class="grid grid-cols-3 gap-4 max-w-xl text-sm">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <div class="text-sky-300 font-bold text-2xl">Sidebar</div>
                    <div class="text-slate-300 mt-1">Gaya iPos</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <div class="text-emerald-300 font-bold text-2xl">Table</div>
                    <div class="text-slate-300 mt-1">Data padat</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <div class="text-amber-300 font-bold text-2xl">POS</div>
                    <div class="text-slate-300 mt-1">Tahap berikutnya</div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_30px_80px_rgba(15,23,42,0.12)]">
                <div class="h-2 bg-gradient-to-r from-blue-500 via-sky-500 to-emerald-500"></div>
                <div class="p-8 sm:p-10">
                    <div class="mb-8">
                        <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-blue-700">
                            <i data-lucide="shield-check" class="h-4 w-4"></i>
                            Masuk Sistem
                        </div>
                        <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">Selamat datang kembali</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-500"><?= htmlspecialchars($message) ?></p>
                    </div>

                    <?php if ($status === 'error'): ?>
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        Username atau kata sandi tidak valid.
                    </div>
                    <?php endif; ?>

                    <?php if ($status === 'pending-schema'): ?>
                    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        Tabel pengguna belum tersedia. Jalankan schema auth terlebih dahulu.
                    </div>
                    <?php endif; ?>

                    <form action="../process/login.php" method="POST" class="space-y-5">
                        <div>
                            <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">Username</label>
                            <input type="text" id="username" name="username" required autofocus
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:shadow-[0_0_0_4px_rgba(51,122,183,0.12)]"
                                placeholder="Masukkan username">
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Kata Sandi</label>
                            <input type="password" id="password" name="password" required
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:shadow-[0_0_0_4px_rgba(51,122,183,0.12)]"
                                placeholder="Masukkan kata sandi">
                        </div>

                        <button type="submit"
                            class="w-full rounded-2xl bg-blue-600 px-4 py-3.5 font-semibold text-white transition hover:bg-blue-700 shadow-sm shadow-blue-100">
                            Masuk ke Dashboard
                        </button>
                    </form>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                        Tampilan ini mengikuti referensi iPos 5: panel klasik, warna biru sebagai aksi utama, dan ruang login yang fokus.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
    lucide.createIcons();
    </script>
</body>

</html>

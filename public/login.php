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

    body {
        font-family: 'Inter', sans-serif;
    }
    </style>
</head>

<body class="min-h-screen bg-slate-950 text-white">
    <div class="min-h-screen grid lg:grid-cols-[1.1fr_0.9fr]">
        <div class="hidden lg:flex flex-col justify-between p-12 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.35),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(59,130,246,0.25),_transparent_30%),linear-gradient(135deg,_#020617,_#0f172a_65%,_#111827)] border-r border-white/10">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-emerald-300/80">Toko Surya Kemasan</p>
                <h1 class="mt-6 max-w-xl text-5xl font-black leading-tight">POS dan manajemen transaksi yang terarah untuk operasional toko.</h1>
                <p class="mt-6 max-w-lg text-slate-300 leading-7">Masuk untuk mengelola data master, memproses penjualan, dan menyiapkan alur pembelian yang akan diperluas pada tahap berikutnya.</p>
            </div>

            <div class="grid grid-cols-3 gap-4 max-w-xl text-sm">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="text-emerald-300 font-bold text-2xl">2</div>
                    <div class="text-slate-300 mt-1">Role utama</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="text-sky-300 font-bold text-2xl">Atomic</div>
                    <div class="text-slate-300 mt-1">Transaksi stok</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="text-amber-300 font-bold text-2xl">CRUD</div>
                    <div class="text-slate-300 mt-1">Master data</div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-center p-6 sm:p-10 bg-slate-100 text-slate-900">
            <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 sm:p-10 shadow-[0_30px_80px_rgba(15,23,42,0.12)]">
                <div class="mb-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-600">Masuk Sistem</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight">Selamat datang kembali</h2>
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
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none ring-0 transition focus:border-emerald-500 focus:bg-white focus:shadow-[0_0_0_4px_rgba(16,185,129,0.12)]"
                            placeholder="Masukkan username">
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Kata Sandi</label>
                        <input type="password" id="password" name="password" required
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none ring-0 transition focus:border-emerald-500 focus:bg-white focus:shadow-[0_0_0_4px_rgba(16,185,129,0.12)]"
                            placeholder="Masukkan kata sandi">
                    </div>

                    <button type="submit"
                        class="w-full rounded-2xl bg-emerald-600 px-4 py-3.5 font-semibold text-white transition hover:bg-emerald-700">
                        Masuk ke Dashboard
                    </button>
                </form>

                <div class="mt-6 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                    Sementara ini aplikasi mengandalkan tabel <span class="font-semibold text-slate-700">users</span> dengan kolom <span class="font-semibold text-slate-700">username</span>, <span class="font-semibold text-slate-700">password_hash</span>, dan <span class="font-semibold text-slate-700">role</span>.
                </div>
            </div>
        </div>
    </div>
</body>

</html>

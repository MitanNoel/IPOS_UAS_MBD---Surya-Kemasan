<?php
require_once '../config/app.php';

$user = require_auth('login.php');
$role = current_user_role();

try {
    // Menggunakan JOIN untuk mengambil nama_kategori dari tabel kategori
    $sql = "SELECT b.*, k.nama_kategori 
            FROM barang b 
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
            ORDER BY b.id_barang DESC";
    $stmt = $pdo->query($sql);
    $barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung ringkasan data
    $totalBarang = count($barang);
    $totalNilai = array_sum(array_column($barang, 'harga_jual'));
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Surya Kemasan | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    :root {
        --ipos-blue: #337ab7;
        --ipos-blue-dark: #214f7d;
        --ipos-surface: #f5f7fb;
        --ipos-border: #d9e3f0;
    }

    body {
        font-family: 'Inter', sans-serif;
        background:
            radial-gradient(circle at top left, rgba(51, 122, 183, 0.08), transparent 30%),
            linear-gradient(180deg, #f8fbff 0%, #f5f7fb 100%);
    }

    .ipos-sidebar {
        background: linear-gradient(180deg, #1f3f61 0%, #19334f 100%);
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
    }

    .ipos-card {
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(217, 227, 240, 0.9);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
    }

    .ipos-table thead th {
        background: linear-gradient(180deg, #337ab7 0%, #2c6ca5 100%);
        color: #fff;
    }

    .ipos-table tbody tr:hover {
        background: rgba(51, 122, 183, 0.04);
    }

    .menu-link {
        transition: all 0.2s ease;
    }

    .menu-link:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateX(2px);
    }
    </style>
</head>

<body class="min-h-screen text-slate-800">
    <div class="min-h-screen lg:flex">
        <aside class="ipos-sidebar lg:fixed lg:inset-y-0 lg:left-0 lg:w-64 text-white">
            <div class="p-6 border-b border-white/10">
                <div class="inline-flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-blue-700 shadow-sm">
                        <i data-lucide="store" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.35em] text-white/60">Toko Surya Kemasan</p>
                        <h1 class="text-lg font-bold">iPos-inspired Panel</h1>
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-2 text-sm">
                <a href="index.php" class="menu-link flex items-center gap-3 rounded-xl bg-white/12 px-4 py-3 font-semibold">
                    <i data-lucide="layout-grid" class="h-4 w-4"></i>
                    Dashboard
                </a>
                <a href="#barang" class="menu-link flex items-center gap-3 rounded-xl px-4 py-3 text-white/85 hover:text-white">
                    <i data-lucide="package" class="h-4 w-4"></i>
                    Data Barang
                </a>
                <a href="#penjualan" class="menu-link flex items-center gap-3 rounded-xl px-4 py-3 text-white/85 hover:text-white">
                    <i data-lucide="shopping-cart" class="h-4 w-4"></i>
                    Penjualan
                    <span class="ml-auto rounded-full bg-white/10 px-2 py-0.5 text-[10px] uppercase tracking-wider text-white/60">soon</span>
                </a>
                <a href="#pembelian" class="menu-link flex items-center gap-3 rounded-xl px-4 py-3 text-white/85 hover:text-white">
                    <i data-lucide="truck" class="h-4 w-4"></i>
                    Pembelian
                    <span class="ml-auto rounded-full bg-white/10 px-2 py-0.5 text-[10px] uppercase tracking-wider text-white/60">soon</span>
                </a>
                <a href="#persediaan" class="menu-link flex items-center gap-3 rounded-xl px-4 py-3 text-white/85 hover:text-white">
                    <i data-lucide="boxes" class="h-4 w-4"></i>
                    Persediaan
                    <span class="ml-auto rounded-full bg-white/10 px-2 py-0.5 text-[10px] uppercase tracking-wider text-white/60">soon</span>
                </a>
                <a href="#pengaturan" class="menu-link flex items-center gap-3 rounded-xl px-4 py-3 text-white/85 hover:text-white">
                    <i data-lucide="settings-2" class="h-4 w-4"></i>
                    Pengaturan
                    <span class="ml-auto rounded-full bg-white/10 px-2 py-0.5 text-[10px] uppercase tracking-wider text-white/60">soon</span>
                </a>
            </nav>

            <div class="mt-auto hidden lg:block p-4">
                <div class="rounded-2xl border border-white/10 bg-white/8 p-4 text-sm text-white/80">
                    <p class="text-xs uppercase tracking-[0.3em] text-white/45">Login Aktif</p>
                    <p class="mt-2 font-semibold text-white"><?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Pengguna') ?></p>
                    <p class="text-xs text-white/60"><?= role_label($role) ?></p>
                </div>
            </div>
        </aside>

        <main class="flex-1 lg:ml-64">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <div class="ipos-card mb-6 rounded-3xl px-6 py-5 sm:px-8">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-blue-700">Dashboard</span>
                                <span class="inline-flex items-center rounded-full border border-blue-100 bg-white px-3 py-1 text-xs font-semibold text-blue-700"><?= role_label($role) ?></span>
                            </div>
                            <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Manajemen Inventaris</h2>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Pantau stok, kategori, dan nilai jual produk dalam satu panel yang mengikuti arah visual iPos 5, lalu berkembang ke modul transaksi berikutnya.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <?php if ($role === 'admin'): ?>
                            <a href="tambah.php" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                <i data-lucide="plus-circle" class="h-5 w-5"></i>
                                Tambah Barang
                            </a>
                            <?php endif; ?>
                            <a href="logout.php" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                                <i data-lucide="log-out" class="h-5 w-5"></i>
                                Keluar
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (isset($_GET['status'])): ?>
                <div class="mb-6 rounded-2xl border px-4 py-3 text-sm shadow-sm <?php echo in_array($_GET['status'], ['success_insert', 'success_update', 'deleted'], true) ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'; ?>">
                    <?php if ($_GET['status'] === 'success_insert'): ?>Data barang berhasil ditambahkan.
                    <?php elseif ($_GET['status'] === 'success_update'): ?>Data barang berhasil diperbarui.
                    <?php elseif ($_GET['status'] === 'deleted'): ?>Data barang berhasil dihapus.
                    <?php elseif ($_GET['status'] === 'forbidden'): ?>Anda tidak memiliki akses untuk membuka halaman tersebut.
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($role === 'cashier'): ?>
                <div class="mb-8 rounded-3xl border border-sky-200 bg-sky-50 p-6 text-sky-800 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="rounded-2xl bg-white/70 p-3 text-sky-700 shadow-sm">
                            <i data-lucide="badge-info" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Mode Kasir</h3>
                            <p class="mt-1 text-sm leading-6">Akses saat ini fokus ke data inventaris. Tampilan ini disiapkan agar modul POS penjualan dapat masuk tanpa mengubah bahasa visualnya.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-8">
                    <div class="ipos-card rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500">Total Item</p>
                                <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= $totalBarang ?></p>
                            </div>
                            <div class="rounded-2xl bg-blue-50 p-3 text-blue-700">
                                <i data-lucide="package" class="h-7 w-7"></i>
                            </div>
                        </div>
                    </div>
                    <div class="ipos-card rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500">Kategori Aktif</p>
                                <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= count(array_unique(array_filter(array_column($barang, 'nama_kategori')))) ?></p>
                            </div>
                            <div class="rounded-2xl bg-amber-50 p-3 text-amber-600">
                                <i data-lucide="tags" class="h-7 w-7"></i>
                            </div>
                        </div>
                    </div>
                    <div class="ipos-card rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500">Estimasi Nilai Jual</p>
                                <p class="mt-2 text-2xl font-extrabold text-emerald-600">Rp <?= number_format($totalNilai, 0, ',', '.') ?></p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-600">
                                <i data-lucide="trending-up" class="h-7 w-7"></i>
                            </div>
                        </div>
                    </div>
                    <div class="ipos-card rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500">Status</p>
                                <p class="mt-2 text-2xl font-extrabold text-blue-700">Online</p>
                            </div>
                            <div class="rounded-2xl bg-sky-50 p-3 text-sky-700">
                                <i data-lucide="shield-check" class="h-7 w-7"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <section id="barang" class="ipos-card overflow-hidden rounded-3xl">
                    <div class="flex items-center justify-between border-b border-slate-200/80 px-6 py-4 sm:px-8">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-600">Master Data</p>
                            <h3 class="mt-1 text-xl font-bold text-slate-900">Daftar Barang</h3>
                        </div>
                        <div class="text-right text-xs text-slate-400">
                            Menampilkan total <?= count($barang) ?> entri barang
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="ipos-table min-w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Harga Jual</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Kategori</th>
                                    <?php if ($role === 'admin'): ?>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-right">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <?php $tableColspan = $role === 'admin' ? 5 : 4; ?>
                                <?php if (empty($barang)): ?>
                                <tr>
                                    <td colspan="<?= $tableColspan ?>" class="px-6 py-14 text-center text-slate-400">
                                        <i data-lucide="archive-x" class="mx-auto mb-3 h-12 w-12 opacity-20"></i>
                                        Belum ada data barang.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($barang as $row): ?>
                                <tr class="transition-colors hover:bg-blue-50/40">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-400">
                                        #<?= htmlspecialchars($row['id_barang']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($row['nama_barang']) ?></div>
                                        <div class="text-[10px] uppercase tracking-[0.25em] text-slate-400">Terverifikasi</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold text-slate-900">Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                            <?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori') ?>
                                        </span>
                                    </td>
                                    <?php if ($role === 'admin'): ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="edit.php?id=<?= urlencode($row['id_barang']) ?>" class="inline-flex items-center justify-center rounded-xl bg-blue-50 p-2.5 text-blue-700 transition hover:bg-blue-100" title="Ubah Data">
                                                <i data-lucide="edit-3" class="h-5 w-5"></i>
                                            </a>
                                            <a href="hapus.php?id=<?= urlencode($row['id_barang']) ?>" class="inline-flex items-center justify-center rounded-xl bg-red-50 p-2.5 text-red-700 transition hover:bg-red-100" title="Hapus Data">
                                                <i data-lucide="trash-2" class="h-5 w-5"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
    lucide.createIcons();
    </script>
</body>

</html>
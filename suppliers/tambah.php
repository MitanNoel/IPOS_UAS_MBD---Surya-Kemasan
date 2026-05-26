<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/header.php'; ?>
    <title>Tambah Supplier | IPOS Sistem Toko</title>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <?php require_once '../includes/navbar.php'; ?>
    
    <div class="main-content px-4 py-8 fade-in">
        <div class="max-w-xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="truck" class="w-5.5 h-5.5 text-brand-600"></i>
                        Tambah Supplier Baru
                    </h1>
                    <p class="text-xs text-slate-500">Mendaftarkan kontak supplier penyedia baru.</p>
                </div>
                <a href="index.php" class="px-4 py-2 border rounded-xl bg-white text-slate-650 hover:bg-slate-50 text-xs font-semibold shadow-sm transition-colors">
                    Kembali
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="bg-slate-50/50 px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-brand-600"></i>
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Formulir Data Supplier</span>
                </div>

                <form action="process_insert.php" method="POST" class="p-6 space-y-5">
                    <!-- Nama Supplier -->
                    <div class="space-y-2">
                        <label for="nama_supplier" class="text-xs font-semibold text-slate-700">Nama Supplier</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="user" class="w-4 h-4"></i></span>
                            <input 
                                type="text" 
                                id="nama_supplier" 
                                name="nama_supplier" 
                                required
                                class="ipos-input pl-10"
                                placeholder="Contoh: PT. Sumber Jaya"
                            >
                        </div>
                    </div>

                    <!-- Nomor Telepon -->
                    <div class="space-y-2">
                        <label for="no_telp" class="text-xs font-semibold text-slate-700">Nomor Telepon</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="phone" class="w-4 h-4"></i></span>
                            <input 
                                type="text" 
                                id="no_telp" 
                                name="no_telp" 
                                required
                                class="ipos-input pl-10"
                                placeholder="Contoh: 0812XXXXXXXX"
                            >
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="pt-6 border-t border-slate-100 flex items-center gap-3">
                        <button 
                            type="submit"
                            class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-all shadow-md shadow-brand-500/10 hover:shadow-lg flex items-center justify-center gap-2 text-xs"
                        >
                            <i data-lucide="save" class="w-4.5 h-4.5"></i> Simpan Supplier
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

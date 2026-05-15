# Proyek MBD - Sistem Toko

Sistem toko berbasis PHP dan MySQL untuk manajemen inventori, pembelian, penjualan, supplier, dan user. Aplikasi ini memakai session login, role-based access control, dan perhitungan stok dinamis dari tabel detail pembelian serta detail penjualan.

## Gambaran Umum

Alur utama aplikasi:

1. Root project membuka [index.html](index.html), lalu mengarahkan pengguna ke halaman login.
2. [auth/login.php](auth/login.php) menampilkan form login.
3. [auth/process_login.php](auth/process_login.php) memverifikasi kredensial dan membuat session.
4. [auth/check_auth.php](auth/check_auth.php) dipakai oleh halaman yang dilindungi untuk memastikan user sudah login dan punya role yang sesuai.
5. Setelah login, pengguna masuk ke [dashboard/index.php](dashboard/index.php) untuk melihat ringkasan bisnis.

## Persyaratan

- PHP 8+.
- MySQL atau MariaDB.
- Ekstensi PDO MySQL aktif.
- Tailwind CSS dan Lucide dimuat dari CDN.

## Setup Singkat

1. Buat file `.env` dari [.env.example](.env.example).
2. Isi kredensial database yang benar.
3. Import [template_clean.sql](template_clean.sql) ke database.
4. Jalankan project melalui web server PHP.
5. Buka aplikasi dari root project agar redirect masuk ke halaman login.

Kredensial demo yang tersedia di halaman login:

- Admin: `admin` / `12345`
- Kasir: `kasir` / `12345`

## Struktur File

### File Root

| File | Fungsi |
| --- | --- |
| [.env.example](.env.example) | Contoh variabel environment untuk koneksi database. Nilainya masih placeholder dan harus diganti dengan kredensial lokal. |
| [.gitignore](.gitignore) | Daftar file atau folder yang tidak perlu ikut version control. |
| [index.html](index.html) | Halaman redirect root yang langsung mengarahkan browser ke [auth/login.php](auth/login.php). |
| [template_clean.sql](template_clean.sql) | Dump database berisi struktur tabel dan data awal untuk seluruh modul aplikasi. |

### Folder auth

| File | Fungsi |
| --- | --- |
| [auth/check_auth.php](auth/check_auth.php) | Middleware autentikasi. Memulai session, memeriksa apakah user sudah login, dan menyediakan helper role seperti `is_admin()` dan `is_kasir()`. |
| [auth/login.php](auth/login.php) | Tampilan form login dengan pesan error dan contoh kredensial demo. |
| [auth/process_login.php](auth/process_login.php) | Memproses login, mencari user di tabel `user`, memverifikasi password, lalu mengisi session. |
| [auth/logout.php](auth/logout.php) | Menghancurkan session dan mengarahkan user kembali ke halaman login. |

### Folder config

| File | Fungsi |
| --- | --- |
| [config/database.php](config/database.php) | Memuat environment, membaca variabel koneksi database, lalu membuat objek PDO yang dipakai seluruh modul. |

### Folder includes

| File | Fungsi |
| --- | --- |
| [includes/navbar.php](includes/navbar.php) | Sidebar navigasi utama. Menampilkan menu berdasarkan role, menandai menu aktif, dan menyediakan tombol logout. |

### Folder dashboard

| File | Fungsi |
| --- | --- |
| [dashboard/index.php](dashboard/index.php) | Dashboard utama yang menampilkan KPI penjualan, biaya, profit, nilai stok, pendapatan bulanan, penjualan terbaru, top product, dan low stock alert. |

### Folder inventory

| File | Fungsi |
| --- | --- |
| [inventory/index.php](inventory/index.php) | Halaman monitoring stok dinamis. Menyediakan pencarian, filter stok, ringkasan pergerakan barang, nilai stok, stok tertinggi, dan tabel detail inventori. |

### Folder process

Folder ini adalah backend CRUD produk yang dipakai oleh modul [public/](public/).

| File | Fungsi |
| --- | --- |
| [process/insert.php](process/insert.php) | Menyimpan data barang baru ke tabel `barang` setelah validasi input. |
| [process/update.php](process/update.php) | Memperbarui data barang yang sudah ada di tabel `barang`. |
| [process/delete.php](process/delete.php) | Menghapus barang dari tabel `barang` setelah konfirmasi POST. |

### Folder public

Folder ini berisi antarmuka master barang versi admin, dengan stok dihitung dari pembelian dan penjualan.

| File | Fungsi |
| --- | --- |
| [public/index.php](public/index.php) | Daftar barang utama untuk admin. Menampilkan stok dinamis, margin, dan aksi edit/hapus. |
| [public/tambah.php](public/tambah.php) | Form untuk menambahkan barang baru. |
| [public/edit.php](public/edit.php) | Form untuk mengubah data barang yang sudah ada. |
| [public/hapus.php](public/hapus.php) | Halaman konfirmasi sebelum barang dihapus, lalu mengirim POST ke [process/delete.php](process/delete.php). |

### Folder purchases

Modul pembelian hanya untuk admin dan memakai transaksi database agar header dan detail selalu konsisten.

| File | Fungsi |
| --- | --- |
| [purchases/index.php](purchases/index.php) | Daftar riwayat pembelian, lengkap dengan supplier, total, dan aksi detail/edit/hapus. |
| [purchases/tambah.php](purchases/tambah.php) | Form input pembelian baru beserta item-item detailnya. |
| [purchases/edit.php](purchases/edit.php) | Form untuk mengubah pembelian dan detail item pembelian. |
| [purchases/view.php](purchases/view.php) | Halaman detail satu transaksi pembelian. |
| [purchases/process_insert.php](purchases/process_insert.php) | Menyimpan pembelian baru dan detailnya dalam satu transaksi. |
| [purchases/process_update.php](purchases/process_update.php) | Memperbarui pembelian beserta detailnya dengan transaksi database. |
| [purchases/process_delete.php](purchases/process_delete.php) | Menghapus pembelian dan seluruh detail pembelian terkait. |

### Folder sales

Modul penjualan digunakan oleh admin dan kasir. POS dipakai untuk transaksi cepat, sedangkan halaman daftar menampilkan riwayat penjualan.

| File | Fungsi |
| --- | --- |
| [sales/index.php](sales/index.php) | Daftar penjualan dengan ringkasan item, total transaksi, dan aksi detail/edit/hapus. |
| [sales/pos.php](sales/pos.php) | Antarmuka point of sale untuk membuat transaksi penjualan dengan cepat. |
| [sales/view.php](sales/view.php) | Halaman detail transaksi penjualan. |
| [sales/edit.php](sales/edit.php) | Form untuk memperbarui transaksi penjualan. |
| [sales/process_insert.php](sales/process_insert.php) | Menyimpan transaksi penjualan baru dan detailnya dalam satu transaksi database. |
| [sales/process_update.php](sales/process_update.php) | Memperbarui transaksi penjualan dan detail item terkait. |
| [sales/process_delete.php](sales/process_delete.php) | Menghapus transaksi penjualan dan seluruh detail penjualannya. |

### Folder suppliers

Modul supplier hanya untuk admin dan dipakai sebagai referensi pembelian.

| File | Fungsi |
| --- | --- |
| [suppliers/index.php](suppliers/index.php) | Daftar supplier dengan aksi edit dan hapus. |
| [suppliers/tambah.php](suppliers/tambah.php) | Form untuk menambahkan supplier baru. |
| [suppliers/edit.php](suppliers/edit.php) | Form untuk mengubah data supplier. |
| [suppliers/hapus.php](suppliers/hapus.php) | Halaman konfirmasi sebelum supplier dihapus. |
| [suppliers/process_insert.php](suppliers/process_insert.php) | Menyimpan data supplier baru. |
| [suppliers/process_update.php](suppliers/process_update.php) | Memperbarui data supplier yang sudah ada. |
| [suppliers/process_delete.php](suppliers/process_delete.php) | Menghapus supplier dari database. |

### Folder users

Modul user dipakai admin untuk mengelola akun admin dan kasir.

| File | Fungsi |
| --- | --- |
| [users/index.php](users/index.php) | Daftar user yang tersimpan di tabel `user`, termasuk role, edit, dan hapus. |
| [users/tambah.php](users/tambah.php) | Form pembuatan user baru. |
| [users/edit.php](users/edit.php) | Form untuk mengubah data user. |
| [users/hapus.php](users/hapus.php) | Halaman konfirmasi sebelum user dihapus. |
| [users/process_insert.php](users/process_insert.php) | Menyimpan user baru, termasuk validasi username unik dan role yang valid. |
| [users/process_update.php](users/process_update.php) | Memperbarui data user. |
| [users/process_delete.php](users/process_delete.php) | Menghapus user dari database. |

## Database dan Model Data

Database utama berisi tabel-tabel berikut:

- `user` untuk akun login.
- `barang` untuk master produk.
- `kategori` untuk kategori produk.
- `supplier` untuk data pemasok.
- `pembelian` dan `detail_pembelian` untuk transaksi pembelian.
- `penjualan` dan `detail_penjualan` untuk transaksi penjualan.

Stok barang dihitung dinamis dari:

stok = total qty pembelian - total qty penjualan

Nilai stok dihitung dari:

nilai stok = stok per barang x harga beli

## Catatan Implementasi

- File-file di folder `public/`, `process/`, `purchases/`, `sales/`, `suppliers/`, dan `users/` mengikuti pola CRUD yang konsisten: list, form tambah/edit, konfirmasi hapus, lalu handler proses.
- Modul pembelian dan penjualan memakai transaksi database untuk menjaga header dan detail tetap sinkron.
- Sidebar navigasi menyesuaikan menu berdasarkan role, jadi admin dan kasir tidak selalu melihat menu yang sama.
- Seluruh halaman utama memakai `require_once '../auth/check_auth.php';` dan `require_once '../config/database.php';` agar akses dan koneksi database konsisten.

## Ringkasan Navigasi

- Dashboard: [dashboard/index.php](dashboard/index.php)
- Inventori: [inventory/index.php](inventory/index.php)
- Data Produk: [public/index.php](public/index.php)
- Penjualan: [sales/index.php](sales/index.php)
- POS: [sales/pos.php](sales/pos.php)
- Pembelian: [purchases/index.php](purchases/index.php)
- Supplier: [suppliers/index.php](suppliers/index.php)
- User: [users/index.php](users/index.php)

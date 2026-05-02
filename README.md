# Project PHP CRUD

Proyek implementasi operasi database CRUD menggunakan PHP dan PDO.

## Struktur Direktori
Sesuai dengan spesifikasi tugas:
- `config/`: Konfigurasi koneksi database
- `public/`: Antarmuka halaman pengguna
- `process/`: File proses logika server-side

## Fitur
1. **Create**: Tambah data ke dalam tabel `barang` (Validasi terintegrasi).
2. **Read**: Menampilkan seluruh data barang ke dalam tabel HTML.
3. **Update**: Mengubah data `barang` menggunakan Form.
4. **Delete**: Menghapus data `barang` via ID URL.

## Keamanan & Standar
- Semua query menggunakan Prepared Statements dengan named parameter (`:param`)
- Penanganan error menggunakan blok `try-catch` (`PDO::ERRMODE_EXCEPTION`)
- Validasi sederhana untuk input data kosong dan numerik (harga)
- Proteksi terhadap SQL Injection dengan parameter binding

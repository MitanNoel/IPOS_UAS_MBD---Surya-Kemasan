<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_barang = trim($_POST['id_barang'] ?? '');
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $harga_jual = $_POST['harga_jual'] ?? 0;
    $harga_beli = $_POST['harga_beli'] ?? 0;
    $id_kategori = $_POST['id_kategori'] ?? 0;

    // Validasi Input
    if (empty($id_barang)) {
        die("Error: ID barang tidak boleh kosong.");
    }
    if (empty($nama_barang)) {
        die("Error: Nama barang tidak boleh kosong.");
    }
    if (!is_numeric($harga_jual) || $harga_jual <= 0) {
        die("Error: Harga jual harus berupa angka dan lebih besar dari 0.");
    }
    if (!is_numeric($harga_beli) || $harga_beli <= 0) {
        die("Error: Harga beli harus berupa angka dan lebih besar dari 0.");
    }

    try {
        // Prepared statement untuk insert
        $sql = "INSERT INTO barang (kode_barang, nama_barang, harga_jual, harga_beli, id_kategori) VALUES (:kode_barang, :nama_barang, :harga_jual, :harga_beli, :id_kategori)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':kode_barang', $id_barang); // $id_barang from form contains the SKU code
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':harga_jual', $harga_jual);
        $stmt->bindParam(':harga_beli', $harga_beli);
        $stmt->bindParam(':id_kategori', $id_kategori);
        
        $stmt->execute();
        
        header("Location: ../public/index.php?status=success_insert");
        exit();
    } catch (PDOException $e) {
        die("Error database (Insert): " . $e->getMessage());
    }
} else {
    header("Location: ../public/tambah.php");
    exit();
}
?>

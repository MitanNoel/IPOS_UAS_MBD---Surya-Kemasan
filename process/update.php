<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_barang = $_POST['id_barang'] ?? '';
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $harga_jual = $_POST['harga_jual'] ?? 0;
    $harga_beli = $_POST['harga_beli'] ?? 0;
    $id_kategori = $_POST['id_kategori'] ?? 0;

    // Validasi Input
    if (empty($id_barang)) {
         die("Error: ID barang tidak ditemukan.");
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
        // Prepared statement untuk update
        $sql = "UPDATE barang SET nama_barang = :nama_barang, harga_jual = :harga_jual, harga_beli = :harga_beli, id_kategori = :id_kategori WHERE id_barang = :id_barang";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':id_barang', $id_barang);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':harga_jual', $harga_jual);
        $stmt->bindParam(':harga_beli', $harga_beli);
        $stmt->bindParam(':id_kategori', $id_kategori);
        
        $stmt->execute();
        
        header("Location: ../public/index.php?status=success_update");
        exit();
    } catch (PDOException $e) {
        die("Error database (Update): " . $e->getMessage());
    }
} else {
    header("Location: ../public/index.php");
    exit();
}
?>

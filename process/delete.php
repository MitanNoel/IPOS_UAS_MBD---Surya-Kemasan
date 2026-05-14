<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

// Pastikan data dikirim via POST untuk keamanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_barang'])) {
    $id_barang = $_POST['id_barang'];

    try {
        $sql = "DELETE FROM barang WHERE id_barang = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_barang);
        $stmt->execute();
        
        // Redirect dengan status sukses
        header("Location: ../public/index.php?status=deleted");
        exit();
    } catch (PDOException $e) {
        // Jika gagal karena constraint (misal barang sudah diproses transaksi)
        header("Location: ../public/index.php?status=error&msg=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: ../public/index.php");
    exit();
}
<?php
require_once '../config/database.php';

// Menghapus data dari tabel berdasarkan parameter yang dikirim melalui URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_barang = $_GET['id'];

    try {
        // Prepared statement untuk delete
        $sql = "DELETE FROM barang WHERE id_barang = :id_barang";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_barang', $id_barang);
        $stmt->execute();
        
        header("Location: ../public/index.php?status=success_delete");
        exit();
    } catch (PDOException $e) {
        die("Error database (Delete): " . $e->getMessage());
    }
} else {
    header("Location: ../public/index.php");
    exit();
}
?>

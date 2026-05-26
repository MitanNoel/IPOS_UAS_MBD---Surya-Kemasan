<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int)$_GET['id'];

try {
    // Validasi apakah kategori masih memiliki barang/produk terkait
    $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM barang WHERE id_kategori = :id');
    $checkStmt->execute(['id' => $id]);
    $hasProducts = $checkStmt->fetchColumn() > 0;

    if ($hasProducts) {
        header('Location: index.php?status=has_products');
        exit();
    }

    // Lakukan delete jika aman
    $stmt = $pdo->prepare('DELETE FROM kategori WHERE id_kategori = :id');
    $stmt->execute(['id' => $id]);

    header('Location: index.php?status=deleted');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?status=error');
    exit();
}
?>

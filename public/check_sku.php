<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

header('Content-Type: application/json');

$sku = trim($_GET['sku'] ?? '');

if ($sku === '') {
    echo json_encode(['exists' => false]);
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM barang WHERE kode_barang = :sku');
    $stmt->execute(['sku' => $sku]);
    $count = (int)$stmt->fetchColumn();

    echo json_encode(['exists' => $count > 0]);
    exit();
} catch (PDOException $e) {
    echo json_encode(['exists' => false, 'error' => $e->getMessage()]);
    exit();
}
?>

<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$id = (int) ($_POST['id_pembelian'] ?? 0);
if ($id <= 0) {
    header('Location: index.php?status=error');
    exit();
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('DELETE FROM detail_pembelian WHERE id_pembelian = :id');
    $stmt->execute(['id' => $id]);

    $stmt = $pdo->prepare('DELETE FROM pembelian WHERE id_pembelian = :id');
    $stmt->execute(['id' => $id]);

    $pdo->commit();
    header('Location: index.php?status=deleted');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: index.php?status=error');
    exit();
}
?>
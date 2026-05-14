<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$id_supplier = (int) ($_POST['id_supplier'] ?? 0);

if ($id_supplier <= 0) {
    header('Location: index.php?status=error');
    exit();
}

try {
    $stmt = $pdo->prepare('DELETE FROM supplier WHERE id_supplier = :id_supplier');
    $stmt->execute(['id_supplier' => $id_supplier]);

    header('Location: index.php?status=deleted');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?status=error');
    exit();
}

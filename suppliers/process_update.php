<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$id_supplier = (int) ($_POST['id_supplier'] ?? 0);
$nama_supplier = trim($_POST['nama_supplier'] ?? '');
$no_telp = trim($_POST['no_telp'] ?? '');

if ($id_supplier <= 0 || $nama_supplier === '' || $no_telp === '') {
    header('Location: index.php?status=error');
    exit();
}

try {
    $stmt = $pdo->prepare('UPDATE supplier SET nama_supplier = :nama_supplier, no_telp = :no_telp WHERE id_supplier = :id_supplier');
    $stmt->execute([
        'nama_supplier' => $nama_supplier,
        'no_telp' => $no_telp,
        'id_supplier' => $id_supplier
    ]);

    header('Location: index.php?status=updated');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?status=error');
    exit();
}

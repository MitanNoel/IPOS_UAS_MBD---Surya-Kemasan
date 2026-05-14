<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah.php');
    exit();
}

$nama_supplier = trim($_POST['nama_supplier'] ?? '');
$no_telp = trim($_POST['no_telp'] ?? '');

if ($nama_supplier === '' || $no_telp === '') {
    header('Location: tambah.php?status=error');
    exit();
}

try {
    $stmt = $pdo->prepare('INSERT INTO supplier (nama_supplier, no_telp) VALUES (:nama_supplier, :no_telp)');
    $stmt->execute([
        'nama_supplier' => $nama_supplier,
        'no_telp' => $no_telp
    ]);

    header('Location: index.php?status=created');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?status=error');
    exit();
}

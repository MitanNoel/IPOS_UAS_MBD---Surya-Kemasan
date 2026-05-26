<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah.php');
    exit();
}

$nama_kategori = trim($_POST['nama_kategori'] ?? '');

if ($nama_kategori === '') {
    header('Location: tambah.php?status=error');
    exit();
}

try {
    $stmt = $pdo->prepare('INSERT INTO kategori (nama_kategori) VALUES (:nama_kategori)');
    $stmt->execute([
        'nama_kategori' => $nama_kategori
    ]);

    header('Location: index.php?status=created');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?status=error');
    exit();
}
?>

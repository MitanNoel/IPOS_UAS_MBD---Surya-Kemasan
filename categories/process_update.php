<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$id_kategori = (int)($_POST['id_kategori'] ?? 0);
$nama_kategori = trim($_POST['nama_kategori'] ?? '');

if ($id_kategori <= 0 || $nama_kategori === '') {
    header('Location: edit.php?id=' . $id_kategori . '&status=error');
    exit();
}

try {
    $stmt = $pdo->prepare('UPDATE kategori SET nama_kategori = :nama_kategori WHERE id_kategori = :id');
    $stmt->execute([
        'nama_kategori' => $nama_kategori,
        'id' => $id_kategori
    ]);

    header('Location: index.php?status=updated');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?status=error');
    exit();
}
?>

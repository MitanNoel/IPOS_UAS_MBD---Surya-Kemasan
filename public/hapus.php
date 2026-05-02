<?php
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$i = $_GET['id'];

try {
    $q = 'SELECT * FROM barang WHERE id_barang = :id';
    $s = $pdo->prepare($q);
    $s->bindParam(':id', $i);
    $s->execute();
    $d = $s->fetch(PDO::FETCH_ASSOC);

    if (!$d) {
        die('Data barang tidak ditemukan');
    }
} catch (PDOException $e) {
    die('Error database: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus Barang</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .alert { background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 4px; max-width: 500px; }
        .btn { padding: 10px 15px; text-decoration: none; border-radius: 3px; display: inline-block; font-weight: bold; }
        .btn-delete { background-color: #f44336; color: white; }
        .btn-cancel { background-color: #ccc; color: black; margin-left: 10px;}
    </style>
</head>
<body>
    <h2>Konfirmasi Hapus Data</h2>
    
    <div class="alert">
        <p>Apakah Anda yakin ingin menghapus barang <strong><?= htmlspecialchars($d['nama_barang']) ?></strong>?</p>
        <p>Tindakan ini tidak dapat dibatalkan.</p>
    </div>
    
    <a href="../process/delete.php?id=<?= urlencode($d['id_barang']) ?>" class="btn btn-delete">Ya, Hapus Data</a>
    <a href="index.php" class="btn btn-cancel">Batal</a>
</body>
</html>

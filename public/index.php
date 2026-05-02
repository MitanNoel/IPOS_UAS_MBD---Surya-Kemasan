<?php
require_once '../config/database.php';

try {
    $sql = "SELECT * FROM barang ORDER BY id_barang DESC";
    $stmt = $pdo->query($sql);
    $barang = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Barang</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; text-decoration: none; border-radius: 3px; border: 1px solid transparent; cursor: pointer; }
        .btn-add { background-color: #4CAF50; color: white; display: inline-block; }
        .btn-edit { background-color: #2196F3; color: white; }
        .btn-delete { background-color: #f44336; color: white; }
    </style>
</head>
<body>
    <h2>Data Barang</h2>
    <a href="tambah.php" class="btn btn-add">+ Tambah Data</a>
    
    <table>
        <thead>
            <tr>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th>Harga Jual</th>
                <th>Harga Beli</th>
                <th>ID Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($barang as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['id_barang']) ?></td>
                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                <td>Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                <td>Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['id_kategori']) ?></td>
                <td>
                    <a href="edit.php?id=<?= urlencode($row['id_barang']) ?>" class="btn btn-edit">Edit</a>
                    <a href="hapus.php?id=<?= urlencode($row['id_barang']) ?>" class="btn btn-delete">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

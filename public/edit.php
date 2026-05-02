<?php
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

try {
    $sql = "SELECT * FROM barang WHERE id_barang = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("Data barang tidak ditemukan");
    }
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Barang</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        form { max-width: 400px; margin-top: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background-color: #2196F3; color: white; border: none; cursor: pointer; border-radius: 3px; }
        .btn-back { display: inline-block; padding: 10px 15px; background-color: #ccc; color: black; text-decoration: none; margin-left: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <h2>Edit Data Barang</h2>
    <form action="../process/update.php" method="POST">
        <input type="hidden" name="id_barang" value="<?= htmlspecialchars($data['id_barang']) ?>">
        
        <div class="form-group">
            <label for="nama_barang">Nama Barang:</label>
            <input type="text" id="nama_barang" name="nama_barang" value="<?= htmlspecialchars($data['nama_barang']) ?>" required>
        </div>
        <div class="form-group">
            <label for="harga_jual">Harga Jual:</label>
            <input type="number" id="harga_jual" name="harga_jual" value="<?= htmlspecialchars($data['harga_jual']) ?>" required min="1">
        </div>
        <div class="form-group">
            <label for="harga_beli">Harga Beli:</label>
            <input type="number" id="harga_beli" name="harga_beli" value="<?= htmlspecialchars($data['harga_beli']) ?>" required min="1">
        </div>
        <div class="form-group">
            <label for="id_kategori">ID Kategori:</label>
            <input type="number" id="id_kategori" name="id_kategori" value="<?= htmlspecialchars($data['id_kategori']) ?>" required>
        </div>
        
        <button type="submit">Update Data</button>
        <a href="index.php" class="btn-back">Kembali</a>
    </form>
</body>
</html>

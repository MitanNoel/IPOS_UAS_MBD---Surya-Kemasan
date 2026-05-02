<?php
require_once '../config/database.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $i = trim($_POST['id_barang'] ?? '');
    $n = trim($_POST['nama_barang'] ?? '');
    $j = $_POST['harga_jual'] ?? 0;
    $b = $_POST['harga_beli'] ?? 0;
    $k = $_POST['id_kategori'] ?? 0;

    if ($i === '') {
        die('Error: ID barang tidak boleh kosong.');
    }
    if ($n === '') {
        die('Error: Nama barang tidak boleh kosong.');
    }
    if (!is_numeric($j) || $j <= 0) {
        die('Error: Harga jual harus berupa angka dan lebih besar dari 0.');
    }
    if (!is_numeric($b) || $b <= 0) {
        die('Error: Harga beli harus berupa angka dan lebih besar dari 0.');
    }

    try {
        $q = 'INSERT INTO barang (id_barang, nama_barang, harga_jual, harga_beli, id_kategori) VALUES (:id_barang, :nama_barang, :harga_jual, :harga_beli, :id_kategori)';
        $s = $pdo->prepare($q);
        $s->bindParam(':id_barang', $i);
        $s->bindParam(':nama_barang', $n);
        $s->bindParam(':harga_jual', $j);
        $s->bindParam(':harga_beli', $b);
        $s->bindParam(':id_kategori', $k);
        $s->execute();
        header('Location: ../public/index.php?status=success_insert');
        exit();
    } catch (PDOException $e) {
        die('Error database (Insert): ' . $e->getMessage());
    }
}

header('Location: ../public/tambah.php');
exit();

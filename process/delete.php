<?php
require_once '../config/database.php';

if (!empty($_GET['id'])) {
    $i = $_GET['id'];

    try {
        $q = 'DELETE FROM barang WHERE id_barang = :id_barang';
        $s = $pdo->prepare($q);
        $s->bindParam(':id_barang', $i);
        $s->execute();
        header('Location: ../public/index.php?status=success_delete');
        exit();
    } catch (PDOException $e) {
        die('Error database (Delete): ' . $e->getMessage());
    }
}

header('Location: ../public/index.php');
exit();

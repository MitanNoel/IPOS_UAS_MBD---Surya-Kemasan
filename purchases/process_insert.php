<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah.php');
    exit();
}

$id_supplier = (int) ($_POST['id_supplier'] ?? 0);
$tanggal = $_POST['tanggal'] ?? date('Y-m-d');
$id_user = $_SESSION['user_id'];

$id_barangs = $_POST['id_barang'] ?? [];
$qtys = $_POST['qty'] ?? [];
$prices = $_POST['harga_beli'] ?? [];

if ($id_supplier <= 0 || empty($id_barangs) || count($id_barangs) !== count($qtys) || count($id_barangs) !== count($prices)) {
    header('Location: tambah.php?status=error');
    exit();
}

try {
    $pdo->beginTransaction();

    // Calculate total
    $total = 0.0;
    $lines = [];
    for ($i = 0; $i < count($id_barangs); $i++) {
        $idb = trim($id_barangs[$i]);
        $q = (int) $qtys[$i];
        $p = (float) $prices[$i];
        if ($idb === '' || $q <= 0) continue;
        $subtotal = $q * $p;
        $total += $subtotal;
        $lines[] = [ 'id_barang' => $idb, 'qty' => $q, 'harga_beli' => $p, 'subtotal' => $subtotal ];
    }

    if (empty($lines)) {
        $pdo->rollBack();
        header('Location: tambah.php?status=error');
        exit();
    }

    $stmt = $pdo->prepare('INSERT INTO pembelian (id_user, id_supplier, tanggal, total) VALUES (:id_user, :id_supplier, :tanggal, :total)');
    $stmt->execute(['id_user' => $id_user, 'id_supplier' => $id_supplier, 'tanggal' => $tanggal, 'total' => $total]);
    $purchaseId = (int) $pdo->lastInsertId();

    $stmtDet = $pdo->prepare('INSERT INTO detail_pembelian (id_barang, id_pembelian, qty, harga_beli, subtotal) VALUES (:id_barang, :id_pembelian, :qty, :harga_beli, :subtotal)');
    foreach ($lines as $ln) {
        $stmtDet->execute([
            'id_barang' => $ln['id_barang'],
            'id_pembelian' => $purchaseId,
            'qty' => $ln['qty'],
            'harga_beli' => $ln['harga_beli'],
            'subtotal' => $ln['subtotal']
        ]);
    }

    $pdo->commit();
    header('Location: index.php?status=created');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: tambah.php?status=error');
    exit();
}
?>
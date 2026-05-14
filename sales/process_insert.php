<?php
require_once '../auth/check_auth.php';
// Allow admin and kasir
if (!is_admin() && !is_kasir()) {
    header('Location: ../dashboard/index.php?error=access_denied');
    exit();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pos.php');
    exit();
}

$id_user = $_SESSION['user_id'];
$id_barangs = $_POST['id_barang'] ?? [];
$qtys = $_POST['qty'] ?? [];
$prices = $_POST['harga_jual'] ?? [];

if (empty($id_barangs)) {
    header('Location: pos.php?status=error');
    exit();
}

try {
    $pdo->beginTransaction();

    $total = 0.0;
    $lines = [];
    for ($i = 0; $i < count($id_barangs); $i++) {
        $idb = trim($id_barangs[$i]);
        $q = (int) ($qtys[$i] ?? 0);
        $p = (float) ($prices[$i] ?? 0);
        if ($idb === '' || $q <= 0) continue;
        $subtotal = $q * $p;
        $total += $subtotal;
        $lines[] = ['id_barang' => $idb, 'qty' => $q, 'harga_jual' => $p, 'subtotal' => $subtotal];
    }

    if (empty($lines)) {
        $pdo->rollBack();
        header('Location: pos.php?status=error');
        exit();
    }

    $stmt = $pdo->prepare('INSERT INTO penjualan (id_user, tanggal, total) VALUES (:id_user, :tanggal, :total)');
    $stmt->execute(['id_user' => $id_user, 'tanggal' => date('Y-m-d'), 'total' => $total]);
    $saleId = (int) $pdo->lastInsertId();

    $stmtDet = $pdo->prepare('INSERT INTO detail_penjualan (id_barang, id_penjualan, qty, harga_jual, subtotal) VALUES (:id_barang, :id_penjualan, :qty, :harga_jual, :subtotal)');
    foreach ($lines as $ln) {
        $stmtDet->execute([
            'id_barang' => $ln['id_barang'],
            'id_penjualan' => $saleId,
            'qty' => $ln['qty'],
            'harga_jual' => $ln['harga_jual'],
            'subtotal' => $ln['subtotal']
        ]);
    }

    $pdo->commit();
    header('Location: view.php?id=' . urlencode((string)$saleId) . '&status=created');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: pos.php?status=error');
    exit();
}
?>
<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$id_pembelian = (int) ($_POST['id_pembelian'] ?? 0);
$id_supplier = (int) ($_POST['id_supplier'] ?? 0);
$tanggal = $_POST['tanggal'] ?? date('Y-m-d');

$id_barangs = $_POST['id_barang'] ?? [];
$qtys = $_POST['qty'] ?? [];
$prices = $_POST['harga_beli'] ?? [];

if ($id_pembelian <= 0 || $id_supplier <= 0 || empty($id_barangs) || count($id_barangs) !== count($qtys) || count($id_barangs) !== count($prices)) {
    header('Location: edit.php?id=' . urlencode((string)$id_pembelian) . '&status=error');
    exit();
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    header('Location: edit.php?id=' . urlencode((string)$id_pembelian) . '&status=error');
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM pembelian WHERE id_pembelian = :id');
    $stmt->execute(['id' => $id_pembelian]);
    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->rollBack();
        header('Location: index.php?status=error');
        exit();
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM supplier WHERE id_supplier = :id');
    $stmt->execute(['id' => $id_supplier]);
    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->rollBack();
        header('Location: edit.php?id=' . urlencode((string)$id_pembelian) . '&status=error');
        exit();
    }

    $total = 0.0;
    $lines = [];
    $seen = [];
    for ($i = 0; $i < count($id_barangs); $i++) {
        $idb = trim((string) $id_barangs[$i]);
        $q = (int) ($qtys[$i] ?? 0);
        $p = (float) ($prices[$i] ?? 0);
        if ($idb === '' || $q <= 0 || $p < 0) continue;
        if (isset($seen[$idb])) {
            $pdo->rollBack();
            header('Location: edit.php?id=' . urlencode((string)$id_pembelian) . '&status=error');
            exit();
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM barang WHERE id_barang = :id');
        $stmt->execute(['id' => $idb]);
        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->rollBack();
            header('Location: edit.php?id=' . urlencode((string)$id_pembelian) . '&status=error');
            exit();
        }

        $seen[$idb] = true;
        $subtotal = $q * $p;
        $total += $subtotal;
        $lines[] = [ 'id_barang' => $idb, 'qty' => $q, 'harga_beli' => $p, 'subtotal' => $subtotal ];
    }

    if (empty($lines)) {
        $pdo->rollBack();
        header('Location: edit.php?id=' . urlencode((string)$id_pembelian) . '&status=error');
        exit();
    }

    // Update pembelian header
    $stmt = $pdo->prepare('UPDATE pembelian SET id_supplier = :id_supplier, tanggal = :tanggal, total = :total WHERE id_pembelian = :id');
    $stmt->execute(['id_supplier' => $id_supplier, 'tanggal' => $tanggal, 'total' => $total, 'id' => $id_pembelian]);

    // Delete old detail lines and insert new ones
    $stmt = $pdo->prepare('DELETE FROM detail_pembelian WHERE id_pembelian = :id');
    $stmt->execute(['id' => $id_pembelian]);

    $stmtDet = $pdo->prepare('INSERT INTO detail_pembelian (id_barang, id_pembelian, qty, harga_beli, subtotal) VALUES (:id_barang, :id_pembelian, :qty, :harga_beli, :subtotal)');
    foreach ($lines as $ln) {
        $stmtDet->execute([
            'id_barang' => $ln['id_barang'],
            'id_pembelian' => $id_pembelian,
            'qty' => $ln['qty'],
            'harga_beli' => $ln['harga_beli'],
            'subtotal' => $ln['subtotal']
        ]);
    }

    $pdo->commit();
    header('Location: index.php?status=updated');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: edit.php?id=' . urlencode((string)$id_pembelian) . '&status=error');
    exit();
}
?>
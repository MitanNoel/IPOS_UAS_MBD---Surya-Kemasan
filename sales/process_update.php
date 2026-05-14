<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$id_penjualan = (int) ($_POST['id_penjualan'] ?? 0);
$tanggal = $_POST['tanggal'] ?? '';
$id_barangs = $_POST['id_barang'] ?? [];
$qtys = $_POST['qty'] ?? [];
$prices = $_POST['harga_jual'] ?? [];

if ($id_penjualan <= 0 || empty($id_barangs) || count($id_barangs) !== count($qtys) || count($id_barangs) !== count($prices)) {
    header('Location: index.php?status=error');
    exit();
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    header('Location: edit.php?id=' . urlencode((string) $id_penjualan) . '&status=error');
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT * FROM penjualan WHERE id_penjualan = :id LIMIT 1');
    $stmt->execute(['id' => $id_penjualan]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        $pdo->rollBack();
        header('Location: index.php?status=error');
        exit();
    }

    $total = 0.0;
    $lines = [];
    $seen = [];

    for ($i = 0; $i < count($id_barangs); $i++) {
        $idb = trim((string) $id_barangs[$i]);
        $q = (int) ($qtys[$i] ?? 0);
        $p = (float) ($prices[$i] ?? 0);

        if ($idb === '' || $q <= 0 || $p < 0) {
            continue;
        }

        if (isset($seen[$idb])) {
            $pdo->rollBack();
            header('Location: edit.php?id=' . urlencode((string) $id_penjualan) . '&status=error');
            exit();
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM barang WHERE id_barang = :id');
        $stmt->execute(['id' => $idb]);
        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->rollBack();
            header('Location: edit.php?id=' . urlencode((string) $id_penjualan) . '&status=error');
            exit();
        }

        $stmt = $pdo->prepare('SELECT COALESCE((SELECT SUM(qty) FROM detail_pembelian WHERE id_barang = :id), 0) - COALESCE((SELECT SUM(qty) FROM detail_penjualan WHERE id_barang = :id AND id_penjualan <> :sale_id), 0) AS stok_tersedia');
        $stmt->execute(['id' => $idb, 'sale_id' => $id_penjualan]);
        $available = (int) $stmt->fetchColumn();

        if ($q > $available) {
            $pdo->rollBack();
            header('Location: edit.php?id=' . urlencode((string) $id_penjualan) . '&status=error');
            exit();
        }

        $seen[$idb] = true;
        $subtotal = $q * $p;
        $total += $subtotal;
        $lines[] = [
            'id_barang' => $idb,
            'qty' => $q,
            'harga_jual' => $p,
            'subtotal' => $subtotal,
        ];
    }

    if (empty($lines)) {
        $pdo->rollBack();
        header('Location: edit.php?id=' . urlencode((string) $id_penjualan) . '&status=error');
        exit();
    }

    $stmt = $pdo->prepare('UPDATE penjualan SET tanggal = :tanggal, total = :total WHERE id_penjualan = :id');
    $stmt->execute(['tanggal' => $tanggal, 'total' => $total, 'id' => $id_penjualan]);

    $stmt = $pdo->prepare('DELETE FROM detail_penjualan WHERE id_penjualan = :id');
    $stmt->execute(['id' => $id_penjualan]);

    $stmtDet = $pdo->prepare('INSERT INTO detail_penjualan (id_barang, id_penjualan, qty, harga_jual, subtotal) VALUES (:id_barang, :id_penjualan, :qty, :harga_jual, :subtotal)');
    foreach ($lines as $line) {
        $stmtDet->execute([
            'id_barang' => $line['id_barang'],
            'id_penjualan' => $id_penjualan,
            'qty' => $line['qty'],
            'harga_jual' => $line['harga_jual'],
            'subtotal' => $line['subtotal'],
        ]);
    }

    $pdo->commit();
    header('Location: index.php?status=updated');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: edit.php?id=' . urlencode((string) $id_penjualan) . '&status=error');
    exit();
}
?>

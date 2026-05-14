<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$id_user = (int) ($_POST['id_user'] ?? 0);

if ($id_user <= 0) {
    header('Location: index.php?status=error');
    exit();
}

if (isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] === $id_user) {
    header('Location: index.php?status=error');
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM user WHERE role = "admin"');
    $stmt->execute();
    $adminCount = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT role FROM user WHERE id_user = :id LIMIT 1');
    $stmt->execute(['id' => $id_user]);
    $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$targetUser) {
        header('Location: index.php?status=error');
        exit();
    }

    if ($targetUser['role'] === 'admin' && $adminCount <= 1) {
        header('Location: index.php?status=error');
        exit();
    }

    $stmt = $pdo->prepare('DELETE FROM user WHERE id_user = :id_user');
    $stmt->execute(['id_user' => $id_user]);

    header('Location: index.php?status=deleted');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?status=error');
    exit();
}

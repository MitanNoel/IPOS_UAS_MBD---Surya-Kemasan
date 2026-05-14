<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah.php');
    exit();
}

$nama_user = trim($_POST['nama_user'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = trim($_POST['role'] ?? '');

if ($nama_user === '' || $username === '' || $password === '' || !in_array($role, ['admin', 'kasir'], true)) {
    header('Location: tambah.php?status=error');
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM user WHERE username = :username');
    $stmt->execute(['username' => $username]);
    if ((int) $stmt->fetchColumn() > 0) {
        header('Location: tambah.php?status=error');
        exit();
    }

    $sql = 'INSERT INTO user (nama_user, username, password, role) VALUES (:nama_user, :username, :password, :role)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nama_user' => $nama_user,
        'username' => $username,
        'password' => $password,
        'role' => $role
    ]);

    header('Location: index.php?status=created');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?status=error');
    exit();
}

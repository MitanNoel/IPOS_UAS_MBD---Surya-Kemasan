<?php
require_once '../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = (string) ($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header('Location: ../public/login.php?status=error&msg=' . urlencode('Username dan kata sandi wajib diisi.'));
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: ../public/login.php?status=error');
        exit();
    }

    $storedPassword = (string) ($user['password_hash'] ?? ($user['password'] ?? ''));
    $passwordIsHashed = password_get_info($storedPassword)['algo'] !== 0;
    $passwordValid = $passwordIsHashed ? password_verify($password, $storedPassword) : hash_equals($storedPassword, $password);

    if (!$passwordValid) {
        header('Location: ../public/login.php?status=error');
        exit();
    }

    login_user($user);

    header('Location: ../public/index.php');
    exit();
} catch (PDOException $e) {
    header('Location: ../public/login.php?status=pending-schema&msg=' . urlencode('Tabel pengguna belum siap atau koneksi database bermasalah.'));
    exit();
}

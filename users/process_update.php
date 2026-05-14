<?php
require_once '../auth/check_auth.php';
require_admin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$id_user = (int) ($_POST['id_user'] ?? 0);
$nama_user = trim($_POST['nama_user'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = trim($_POST['role'] ?? '');

if ($id_user <= 0 || $nama_user === '' || $username === '' || !in_array($role, ['admin', 'kasir'], true)) {
    header('Location: index.php?status=error');
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT * FROM user WHERE id_user = :id LIMIT 1');
    $stmt->execute(['id' => $id_user]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingUser) {
        header('Location: index.php?status=error');
        exit();
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM user WHERE username = :username AND id_user != :id_user');
    $stmt->execute([
        'username' => $username,
        'id_user' => $id_user
    ]);
    if ((int) $stmt->fetchColumn() > 0) {
        header('Location: edit.php?id=' . urlencode((string) $id_user) . '&status=error');
        exit();
    }

    if ($password !== '') {
        $sql = 'UPDATE user SET nama_user = :nama_user, username = :username, password = :password, role = :role WHERE id_user = :id_user';
        $params = [
            'nama_user' => $nama_user,
            'username' => $username,
            'password' => $password,
            'role' => $role,
            'id_user' => $id_user
        ];
    } else {
        $sql = 'UPDATE user SET nama_user = :nama_user, username = :username, role = :role WHERE id_user = :id_user';
        $params = [
            'nama_user' => $nama_user,
            'username' => $username,
            'role' => $role,
            'id_user' => $id_user
        ];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if (isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] === $id_user) {
        $_SESSION['username'] = $username;
        $_SESSION['user_name'] = $nama_user;
        $_SESSION['role'] = $role;
    }

    header('Location: index.php?status=updated');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?status=error');
    exit();
}

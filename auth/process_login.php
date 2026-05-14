<?php
require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate input
    if (empty($username) || empty($password)) {
        header('Location: login.php?error=required_fields');
        exit();
    }

    try {
        // Query to find user
        $sql = "SELECT * FROM user WHERE username = :username LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify user exists and password matches
        if ($user && $user['password'] === $password) {
            // Set session variables
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['nama_user'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();

            // Redirect to dashboard
            header('Location: ../dashboard/index.php');
            exit();
        } else {
            // Invalid credentials
            header('Location: login.php?error=invalid_credentials');
            exit();
        }
    } catch (PDOException $e) {
        header('Location: login.php?error=invalid_credentials');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
?>

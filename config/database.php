<?php
$host = 'localhost';
$port = '3306';
$dbname = 'rahasia';
$username = 'rahasia';
$password = 'rahasia';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);
    
    // Mengatur mode error ke PDO::ERRMODE_EXCEPTION
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Menangani kegagalan koneksi
    die("Koneksi database gagal: " . $e->getMessage());
}
?>

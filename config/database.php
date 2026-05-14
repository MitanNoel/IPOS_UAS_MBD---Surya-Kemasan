<?php

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Load the real environment file(s) if present.
loadEnv(__DIR__ . '/../.env');
loadEnv('/workspaces/.codespaces/shared/.env');

// Ambil data dari environment yang benar-benar ada
$host     = $_ENV['DB_HOST'] ?? null;
$port     = $_ENV['DB_PORT'] ?? null;
$dbname   = $_ENV['DB_DATABASE'] ?? null;
$username = $_ENV['DB_USERNAME'] ?? null;
$password = $_ENV['DB_PASSWORD'] ?? null;

if ($host === null) {
    $host = $_ENV['database.default.hostname'] ?? null;
    $port = $_ENV['database.default.port'] ?? $port;
    $dbname = $_ENV['database.default.database'] ?? $dbname;
    $username = $_ENV['database.default.username'] ?? $username;
    $password = $_ENV['database.default.password'] ?? $password;
}

if (!$host || !$port || !$dbname || $username === null || $password === null) {
    die('Koneksi database gagal: variabel environment database belum lengkap.');
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Koneksi Berhasil!"; 
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
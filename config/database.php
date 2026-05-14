<?php

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        $_ENV[$name] = $value;

        $aliases = [
            'database.default.hostname' => 'DB_HOST',
            'database.default.port' => 'DB_PORT',
            'database.default.database' => 'DB_DATABASE',
            'database.default.username' => 'DB_USERNAME',
            'database.default.password' => 'DB_PASSWORD',
        ];

        if (isset($aliases[$name])) {
            $_ENV[$aliases[$name]] = $value;
        }
    }
}

loadEnv(__DIR__ . '/../.env');

// Ambil data dari $_ENV
$host     = $_ENV['DB_HOST'] ?? $_ENV['database.default.hostname'] ?? 'localhost';
$port     = $_ENV['DB_PORT'] ?? '3306';
$dbname   = $_ENV['DB_DATABASE'] ?? $_ENV['database.default.database'] ?? '';
$username = $_ENV['DB_USERNAME'] ?? $_ENV['database.default.username'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? $_ENV['database.default.password'] ?? '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Koneksi Berhasil!"; 
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
<?php
function o_env($f)
{
    if (!is_file($f)) {
        return;
    }
    foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
        $l = trim($l);
        if ($l === '' || $l[0] === '#' || strpos($l, '=') === false) {
            continue;
        }
        [$n, $v] = explode('=', $l, 2);
        $_ENV[trim($n)] = trim($v);
    }
}

o_env(__DIR__ . '/../.env');

$h = $_ENV['DB_HOST'] ?? 'localhost';
$p = $_ENV['DB_PORT'] ?? '3306';
$d = $_ENV['DB_DATABASE'] ?? '';
$u = $_ENV['DB_USERNAME'] ?? '';
$w = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host={$h};port={$p};dbname={$d};charset=utf8", $u, $w);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}
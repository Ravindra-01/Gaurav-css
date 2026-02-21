<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env only in local/Docker environment
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// ✅ getenv() works on both Render (system env) and local (from .env)
$host   = getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? '';
$user   = getenv('DB_USER') ?: $_ENV['DB_USER'] ?? '';
$pass   = getenv('DB_PASS') ?: $_ENV['DB_PASS'] ?? '';
$name   = getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? '';
$port   = (int)(getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?? 3306);

$conn = mysqli_connect($host, $user, $pass, $name, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
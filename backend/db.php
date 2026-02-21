<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// ✅ Only load .env if it exists (local Docker)
// On Render, env vars are injected automatically — no .env file needed
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

$conn = mysqli_connect(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME'],
    (int) $_ENV['DB_PORT']
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
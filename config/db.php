<?php
// ==============================
// Database connection (PDO)
// ==============================
// Edit these if your XAMPP MySQL user/password differ.
// Default XAMPP settings are usually: user 'root', password '' (empty).

$db_host = 'localhost';
$db_name = 'uiu_talent_hunt';
$db_user = 'root';
$db_pass = '';
$db_charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw exceptions on error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // return rows as assoc arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                     // use real prepared statements
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // In production you would log this instead of showing it to users.
    die("Database connection failed: " . $e->getMessage());
}

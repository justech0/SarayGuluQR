<?php
// Database configuration and shared settings
$DB_HOST = 'localhost';
$DB_NAME = 'u220042353_saray';
$DB_USER = 'u220042353_saray';
$DB_PASS = 'Saray!Gulu72.';

// Logo yolu: Hostinger'a yüklediğiniz dosyayı bu yola yerleştirin
$LOGO_URL = '/saray-logo.png';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Veritabanı bağlantısı başarısız: ' . htmlspecialchars($e->getMessage()));
}

// CSRF token helper
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

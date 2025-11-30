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

function ensure_feedback_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS feedbacks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(120) NOT NULL,
        rating INT NOT NULL,
        comment TEXT,
        branch_id INT NULL,
        topic VARCHAR(50) DEFAULT NULL,
        contact VARCHAR(150) DEFAULT NULL,
        language VARCHAR(5) DEFAULT 'tr',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_feedbacks_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
        CHECK (rating BETWEEN 1 AND 5)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $columns = $pdo->query("SHOW COLUMNS FROM feedbacks")->fetchAll(PDO::FETCH_COLUMN);

    $alterations = [
        'customer_name' => "ALTER TABLE feedbacks ADD COLUMN customer_name VARCHAR(120) NOT NULL DEFAULT 'Ziyaretçi' AFTER id",
        'branch_id' => "ALTER TABLE feedbacks ADD COLUMN branch_id INT NULL AFTER comment",
        'topic' => "ALTER TABLE feedbacks ADD COLUMN topic VARCHAR(50) DEFAULT NULL AFTER branch_id",
        'contact' => "ALTER TABLE feedbacks ADD COLUMN contact VARCHAR(150) DEFAULT NULL AFTER topic",
        'language' => "ALTER TABLE feedbacks ADD COLUMN language VARCHAR(5) DEFAULT 'tr' AFTER contact",
        'created_at' => "ALTER TABLE feedbacks ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER language",
    ];

    foreach ($alterations as $column => $sql) {
        if (!in_array($column, $columns, true)) {
            $pdo->exec($sql);
        }
    }
}

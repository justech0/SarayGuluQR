<?php
require_once __DIR__ . '/config.php';

function is_logged_in(): bool
{
    return isset($_SESSION['admin_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function convert_to_webp(string $source, string $destination, int $quality = 80): bool
{
    $imageInfo = getimagesize($source);
    if ($imageInfo === false) {
        return false;
    }

    $mime = $imageInfo['mime'];
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        case 'image/webp':
            return move_uploaded_file($source, $destination);
        default:
            return false;
    }

    $result = imagewebp($image, $destination, $quality);
    imagedestroy($image);
    return $result;
}

function handle_image_upload(string $fieldName, string $targetDir): ?string
{
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $tmpName = $_FILES[$fieldName]['tmp_name'];
    $fileName = pathinfo($_FILES[$fieldName]['name'], PATHINFO_FILENAME);
    $originalExt = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $fileName) . '_' . time();
    $webpDestination = rtrim($targetDir, '/') . '/' . $safeName . '.webp';

    // First try WebP conversion
    if (convert_to_webp($tmpName, $webpDestination)) {
        return str_replace(__DIR__ . '/', '', $webpDestination);
    }

    // If WebP fails, keep original extension
    $fallbackDest = rtrim($targetDir, '/') . '/' . $safeName . '.' . $originalExt;
    if (move_uploaded_file($tmpName, $fallbackDest)) {
        return str_replace(__DIR__ . '/', '', $fallbackDest);
    }

    return null;
}

function flash_message(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $msg;
    }
    return null;
}

function fetch_counts(PDO $pdo): array
{
    $tables = ['products', 'categories', 'feedbacks'];
    $counts = [];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM {$table}");
        $counts[$table] = (int)$stmt->fetch()['total'];
    }
    return $counts;
}

function normalize_phrase(string $value): string
{
    $value = preg_replace('/\s+/', ' ', trim($value));
    return strtolower($value);
}

function verify_reset_phrase(string $input): bool
{
    global $RESET_CHALLENGE_ANSWER;
    if (empty($RESET_CHALLENGE_ANSWER)) {
        return false;
    }
    return normalize_phrase($input) === normalize_phrase($RESET_CHALLENGE_ANSWER);
}

function ensure_campaign_table(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS campaigns (
        id INT PRIMARY KEY,
        is_active TINYINT(1) NOT NULL DEFAULT 0,
        image_path VARCHAR(255) NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM campaigns WHERE id = 1');
    $stmt->execute();
    if ((int)$stmt->fetch()['total'] === 0) {
        $pdo->prepare('INSERT INTO campaigns (id, is_active, image_path) VALUES (1, 0, NULL)')->execute();
    }
}

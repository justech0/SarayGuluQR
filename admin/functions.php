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
        case 'image/webp':
            return imagewebp(imagecreatefromwebp($source), $destination, $quality);
        default:
            return false;
    }

    $result = imagewebp($image, $destination, $quality);
    imagedestroy($image);
    return $result;
}

function handle_image_upload(string $fieldName, string $targetDir, int $maxSizeMb = 8, int $maxWidth = 1200, int $quality = 75): ?string
{
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }

    if (!isset($_FILES[$fieldName]['error']) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $tmpName = $_FILES[$fieldName]['tmp_name'];
    $fileSize = (int)($_FILES[$fieldName]['size'] ?? 0);
    if ($fileSize > ($maxSizeMb * 1024 * 1024)) {
        return null;
    }

    $imageInfo = getimagesize($tmpName);
    if ($imageInfo === false) {
        return null;
    }

    [$width, $height] = $imageInfo;
    $mime = $imageInfo['mime'];
    $supported = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $supported, true)) {
        return null;
    }

    $fileName = pathinfo($_FILES[$fieldName]['name'], PATHINFO_FILENAME);
    $originalExt = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $fileName) . '_' . time();
    $webpDestination = rtrim($targetDir, '/') . '/' . $safeName . '.webp';

    $src = null;
    if ($mime === 'image/jpeg') {
        $src = @imagecreatefromjpeg($tmpName);
    } elseif ($mime === 'image/png') {
        $src = @imagecreatefrompng($tmpName);
        if ($src) {
            imagepalettetotruecolor($src);
            imagealphablending($src, true);
            imagesavealpha($src, true);
        }
    } elseif ($mime === 'image/webp') {
        $src = @imagecreatefromwebp($tmpName);
    }

    if (!$src) {
        return null;
    }

    $scale = 1.0;
    if ($width > $maxWidth) {
        $scale = $maxWidth / $width;
    }
    $newWidth = (int)($width * $scale);
    $newHeight = (int)($height * $scale);

    if ($scale < 1) {
        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($src);
        $src = $dst;
    }

    if (function_exists('imagewebp') && imagewebp($src, $webpDestination, $quality)) {
        imagedestroy($src);
        return str_replace(__DIR__ . '/', '', $webpDestination);
    }

    $fallbackDest = rtrim($targetDir, '/') . '/' . $safeName . '.' . $originalExt;
    $saveResult = false;
    if ($mime === 'image/png') {
        $saveResult = imagepng($src, $fallbackDest); // keep alpha
    } else {
        $saveResult = imagejpeg($src, $fallbackDest, $quality);
    }

    imagedestroy($src);

    if ($saveResult) {
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
        id INT AUTO_INCREMENT PRIMARY KEY,
        is_active TINYINT(1) NOT NULL DEFAULT 0,
        image_path VARCHAR(255) DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $exists = (int)$pdo->query('SELECT COUNT(*) FROM campaigns WHERE id = 1')->fetchColumn();
    if ($exists === 0) {
        $pdo->exec("INSERT INTO campaigns (id, is_active, image_path) VALUES (1, 0, NULL)");
    }
}

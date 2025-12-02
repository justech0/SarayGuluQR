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
    $imageInfo = @getimagesize($source);
    if ($imageInfo === false) {
        return false;
    }

    $mime = $imageInfo['mime'];
    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($source);
            if ($image) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($source);
            break;
        case 'image/webp':
            // Already WebP
            return move_uploaded_file($source, $destination);
        default:
            return false;
    }

    if (!$image) {
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
    $extension = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $fileName);

    $webpPath = rtrim($targetDir, '/') . '/' . $safeName . '_' . time() . '.webp';
    $fallbackPath = rtrim($targetDir, '/') . '/' . $safeName . '_' . time() . '.' . $extension;

    if (convert_to_webp($tmpName, $webpPath)) {
        return $webpPath;
    }

    if (move_uploaded_file($tmpName, $fallbackPath)) {
        return $fallbackPath;
    }

    return null;
}

function relative_upload_path(string $absolutePath): string
{
    return ltrim(str_replace(__DIR__ . '/', '', $absolutePath), '/');
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

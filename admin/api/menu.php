<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
$basePath = $basePath === '' ? '.' : $basePath;

function build_image_url(?string $path, string $basePath): ?string
{
    if (!$path) {
        return null;
    }
    $cleanPath = ltrim($path, '/');
    return $basePath . '/' . $cleanPath;
}

try {
    $categories = $pdo->query('SELECT id, name, description, image_path FROM categories ORDER BY created_at DESC')->fetchAll();
    $products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC')->fetchAll();
    $branches = $pdo->query('SELECT * FROM branches ORDER BY id ASC')->fetchAll();
    $version = get_menu_version($pdo);

    $response = [
        'categories' => array_map(function ($row) use ($basePath) {
            return [
                'id' => (string)$row['id'],
                'name' => [
                    'tr' => $row['name'],
                    'en' => $row['name'],
                    'ar' => $row['name'],
                ],
                'description' => [
                    'tr' => $row['description'] ?? '',
                    'en' => $row['description'] ?? '',
                    'ar' => $row['description'] ?? '',
                ],
                'image' => build_image_url($row['image_path'], $basePath),
            ];
        }, $categories),
        'products' => array_map(function ($row) use ($basePath) {
            return [
                'id' => (string)$row['id'],
                'categoryId' => (string)$row['category_id'],
                'name' => [
                    'tr' => $row['name'],
                    'en' => $row['name'],
                    'ar' => $row['name'],
                ],
                'description' => [
                    'tr' => $row['description'] ?? '',
                    'en' => $row['description'] ?? '',
                    'ar' => $row['description'] ?? '',
                ],
                'price' => (float)$row['price'],
                'image' => build_image_url($row['image_path'], $basePath),
                'categoryName' => $row['category_name'] ?? null,
                'createdAt' => $row['created_at'],
            ];
        }, $products),
        'branches' => array_map(function ($row) {
            return [
                'id' => (string)$row['id'],
                'name' => $row['name'],
                'wifiName' => $row['wifi_name'] ?? null,
                'wifiPassword' => $row['wifi_password'] ?? null,
            ];
        }, $branches),
        'version' => $version,
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Veri alınamadı', 'detail' => $e->getMessage()]);
}

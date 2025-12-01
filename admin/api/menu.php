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
    ensure_default_menu($pdo);

    $categories = $pdo->query('SELECT id, parent_id, name, description, image_path, sort_order FROM categories ORDER BY sort_order ASC, id ASC')->fetchAll();
    $products = $pdo->query('SELECT p.*, c.name AS category_name, c.parent_id FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC')->fetchAll();
    $branches = $pdo->query('SELECT * FROM branches ORDER BY id ASC')->fetchAll();
    $campaign = $pdo->query('SELECT image_path, is_active FROM campaigns ORDER BY id ASC LIMIT 1')->fetch();
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
                'parentId' => $row['parent_id'] ? (string)$row['parent_id'] : null,
                'sortOrder' => isset($row['sort_order']) ? (int)$row['sort_order'] : null,
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
        'campaign' => $campaign && $campaign['image_path']
            ? [
                'image' => build_image_url($campaign['image_path'], $basePath),
                'active' => (bool)$campaign['is_active'],
            ]
            : ['image' => null, 'active' => false],
        'version' => $version,
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Veri alınamadı', 'detail' => $e->getMessage()]);
}

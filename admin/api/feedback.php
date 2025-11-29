<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz veri']);
    exit;
}

$branchId = isset($input['branch_id']) ? (int)$input['branch_id'] : null;
$topic = trim($input['topic'] ?? '');
$rating = (int)($input['rating'] ?? 0);
$comment = trim($input['comment'] ?? '');
$contact = trim($input['contact'] ?? '');
$language = $input['language'] ?? 'tr';
$customerName = trim($input['customer_name'] ?? 'Ziyaretçi');

if ($rating < 1 || $rating > 5 || $comment === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Lütfen puan ve mesaj alanlarını doldurun.']);
    exit;
}

try {
    $existingColumns = $pdo->query("SHOW COLUMNS FROM feedbacks")->fetchAll(PDO::FETCH_COLUMN);
    $dataMap = [
        'customer_name' => $customerName,
        'rating' => $rating,
        'comment' => $comment,
        'branch_id' => $branchId ?: null,
        'topic' => $topic ?: null,
        'contact' => $contact ?: null,
        'language' => $language,
    ];

    $columns = [];
    $params = [];
    foreach ($dataMap as $column => $value) {
        if (in_array($column, $existingColumns, true)) {
            $columns[] = $column;
            $params[":{$column}"] = $value;
        }
    }

    if (empty($columns)) {
        throw new RuntimeException('Tabloda beklenen kolonlar bulunamadı.');
    }

    $placeholders = array_map(fn($c) => ':' . $c, $columns);
    $sql = 'INSERT INTO feedbacks (' . implode(',', $columns) . ') VALUES (' . implode(',', $placeholders) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Kaydedilemedi', 'detail' => $e->getMessage()]);
}

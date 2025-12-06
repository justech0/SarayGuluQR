<?php
require_once __DIR__ . '/../functions.php';
ensure_feedback_schema($pdo);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isMultipart = stripos($contentType, 'multipart/form-data') !== false;

if ($isMultipart) {
    $input = $_POST;
} else {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
}

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
$imagePath = null;

if ($rating < 1 || $rating > 5 || $comment === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Lütfen puan ve mesaj alanlarını doldurun.']);
    exit;
}

if ($isMultipart && !empty($_FILES['image']['name'])) {
    $imagePath = handle_image_upload('image', __DIR__ . '/../uploads/feedbacks', 4, 1000, 72);
}

try {
    // Şema eksikse otomatik oluştur
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

    $existingColumns = $pdo->query("SHOW COLUMNS FROM feedbacks")->fetchAll(PDO::FETCH_COLUMN);
    $dataMap = [
        'customer_name' => $customerName,
        'rating' => $rating,
        'comment' => $comment,
        'branch_id' => $branchId ?: null,
        'topic' => $topic ?: null,
        'contact' => $contact ?: null,
        'image_path' => $imagePath ?: null,
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

    $imageUrl = $imagePath ? '/admin/' . ltrim($imagePath, '/') : null;

    echo json_encode(['success' => true, 'image_url' => $imageUrl]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Kaydedilemedi: ' . $e->getMessage()]);
}

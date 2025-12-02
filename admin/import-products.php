<?php
require_once __DIR__ . '/header.php';

$results = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash_message('error', 'Geçersiz oturum.');
        header('Location: import-products.php');
        exit;
    }

    if (empty($_FILES['file']['tmp_name'])) {
        flash_message('error', 'Lütfen bir CSV veya JSON dosyası seçin.');
        header('Location: import-products.php');
        exit;
    }

    $tmp = $_FILES['file']['tmp_name'];
    $name = strtolower($_FILES['file']['name']);
    $isCsv = str_ends_with($name, '.csv');
    $isJson = str_ends_with($name, '.json');

    if (!$isCsv && !$isJson) {
        flash_message('error', 'Yalnızca CSV veya JSON yükleyebilirsiniz.');
        header('Location: import-products.php');
        exit;
    }

    $pdo->beginTransaction();
    $inserted = 0;
    $skipped = [];
    $createdCategories = [];
    $rowsRead = 0;

    try {
        $dataRows = [];
        if ($isCsv) {
            if (($handle = fopen($tmp, 'r')) !== false) {
                while (($row = fgetcsv($handle, 0, ',')) !== false) {
                    if (count($row) === 1 && trim($row[0]) === '') {
                        continue;
                    }
                    $rowsRead++;
                    // Expecting columns: category_name, product_name, description, price, image_url, sort_order
                    [$categoryName, $productName, $description, $price, $imageUrl, $sortOrder] = array_pad($row, 6, '');
                    $dataRows[] = compact('categoryName', 'productName', 'description', 'price', 'imageUrl', 'sortOrder');
                }
                fclose($handle);
            }
        } else {
            $json = file_get_contents($tmp);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                foreach ($decoded as $row) {
                    $rowsRead++;
                    $dataRows[] = [
                        'categoryName' => $row['category_name'] ?? '',
                        'productName' => $row['product_name'] ?? '',
                        'description' => $row['description'] ?? '',
                        'price' => $row['price'] ?? '',
                        'imageUrl' => $row['image_url'] ?? '',
                        'sortOrder' => $row['sort_order'] ?? '',
                    ];
                }
            }
        }

        foreach ($dataRows as $index => $row) {
            $categoryName = trim((string)$row['categoryName']);
            $productName = trim((string)$row['productName']);
            $description = trim((string)($row['description'] ?? ''));
            $price = $row['price'] === '' ? 0 : $row['price'];
            $sortOrder = $row['sortOrder'] === '' ? null : $row['sortOrder'];

            if ($productName === '' || $categoryName === '') {
                $skipped[] = "Satır " . ($index + 1) . ": kategori veya ürün adı eksik.";
                continue;
            }

            if (!is_numeric($price)) {
                $skipped[] = "Satır " . ($index + 1) . ": fiyat sayısal değil.";
                continue;
            }

            if (function_exists('mb_strlen') && mb_strlen($productName) > 190) {
                $productName = mb_substr($productName, 0, 190);
            }
            if (function_exists('mb_strlen') && mb_strlen($description) > 1000) {
                $description = mb_substr($description, 0, 1000);
            }

            $catStmt = $pdo->prepare('SELECT id FROM categories WHERE name = :name LIMIT 1');
            $catStmt->execute([':name' => $categoryName]);
            $categoryId = $catStmt->fetchColumn();

            if (!$categoryId) {
                $createCat = $pdo->prepare('INSERT INTO categories (name, description) VALUES (:name, :description)');
                $createCat->execute([
                    ':name' => $categoryName,
                    ':description' => '',
                ]);
                $categoryId = $pdo->lastInsertId();
                $createdCategories[] = $categoryName;
            }

            $insert = $pdo->prepare('INSERT INTO products (category_id, name, description, price, image_path, created_at) VALUES (:category, :name, :description, :price, :image, NOW())');
            $insert->execute([
                ':category' => $categoryId,
                ':name' => $productName,
                ':description' => $description,
                ':price' => (float)$price,
                ':image' => null,
            ]);
            $inserted++;
        }

        $pdo->commit();
        $results = [
            'read' => $rowsRead,
            'inserted' => $inserted,
            'skipped' => $skipped,
            'createdCategories' => $createdCategories,
        ];
        flash_message('success', 'İçe aktarma tamamlandı.');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash_message('error', 'İçe aktarma hatası: ' . $e->getMessage());
    }

    header('Location: import-products.php');
    exit;
}
?>
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-serif text-saray-gold tracking-[0.12em]">Toplu Ürün Yükle</h1>
        <p class="text-sm text-saray-muted">CSV veya JSON ile hızlı ürün aktarımı yapın.</p>
    </div>

    <form method="POST" enctype="multipart/form-data" class="bg-black/50 border border-saray-gold/15 rounded-2xl p-6 space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
        <div class="space-y-2">
            <label class="text-sm text-saray-muted">Dosya Seç (.csv veya .json)</label>
            <input type="file" name="file" accept=".csv,.json" class="block w-full text-sm text-saray-text bg-black/40 border border-saray-gold/20 rounded-lg p-3" required>
        </div>
        <div class="text-xs text-saray-muted space-y-1">
            <p>CSV kolonları: category_name, product_name, description, price, image_url, sort_order</p>
            <p>Bozuk satırlar atlanır ve raporlanır; kategori yoksa otomatik oluşturulur.</p>
        </div>
        <button type="submit" class="px-6 py-3 rounded-lg bg-saray-gold text-saray-black font-semibold hover:bg-saray-darkGold transition">İçe Aktar</button>
    </form>

    <?php if ($results): ?>
        <div class="bg-black/50 border border-saray-gold/20 rounded-xl p-4 space-y-2">
            <p class="text-sm text-saray-text">Okunan satır: <?php echo $results['read']; ?></p>
            <p class="text-sm text-green-300">Eklenen ürün: <?php echo $results['inserted']; ?></p>
            <p class="text-sm text-saray-muted">Yeni kategoriler: <?php echo implode(', ', $results['createdCategories']); ?></p>
            <?php if (!empty($results['skipped'])): ?>
                <div class="text-sm text-red-200 space-y-1">
                    <?php foreach ($results['skipped'] as $skip): ?>
                        <div>• <?php echo sanitize($skip); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>

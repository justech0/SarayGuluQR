<?php
require_once __DIR__ . '/functions.php';
require_login();

$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$updated = $unchanged = $invalid = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        flash_message('error', 'Geçersiz istek. Lütfen tekrar deneyin.');
        header('Location: bulk-prices.php' . ($selectedCategory ? '?category=' . $selectedCategory : ''));
        exit;
    }

    $prices = $_POST['price'] ?? [];
    if (!is_array($prices)) {
        $prices = [];
    }

    try {
        $pdo->beginTransaction();
        $selectStmt = $pdo->prepare('SELECT price FROM products WHERE id = :id LIMIT 1');
        $updateStmt = $pdo->prepare('UPDATE products SET price = :price WHERE id = :id LIMIT 1');
        $changed = false;

        foreach ($prices as $productId => $rawPrice) {
            $id = (int)$productId;
            $raw = trim((string)$rawPrice);

            if ($id <= 0) {
                $invalid++;
                continue;
            }

            if ($raw === '') {
                $unchanged++;
                continue;
            }

            $parsed = parse_price_input($raw);
            if ($parsed === null) {
                $invalid++;
                continue;
            }

            $selectStmt->execute([':id' => $id]);
            $current = $selectStmt->fetch();
            if (!$current) {
                $invalid++;
                continue;
            }

            $currentPrice = number_format((float)$current['price'], 2, '.', '');
            if ($currentPrice === $parsed) {
                $unchanged++;
                continue;
            }

            $updateStmt->execute([
                ':price' => $parsed,
                ':id' => $id,
            ]);
            $updated++;
            $changed = true;
        }

        $pdo->commit();

        if ($changed) {
            bump_menu_version($pdo);
        }

        $message = sprintf(
            '%d ürün güncellendi, %d ürün değişmedi, %d satır atlandı.',
            $updated,
            $unchanged,
            $invalid
        );
        flash_message('success', $message);
    } catch (PDOException $e) {
        $pdo->rollBack();
        flash_message('error', 'Fiyatlar güncellenemedi: ' . sanitize($e->getMessage()));
    }

    header('Location: bulk-prices.php' . ($selectedCategory ? '?category=' . $selectedCategory : ''));
    exit;
}

$categoryStmt = $pdo->query('SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC');
$categories = $categoryStmt->fetchAll();

$params = [];
$sql = 'SELECT p.id, p.name, p.price, p.category_id, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id';

if ($selectedCategory > 0) {
    $sql .= ' WHERE p.category_id = :cat';
    $params[':cat'] = $selectedCategory;
}

$sql .= ' ORDER BY c.sort_order ASC, p.sort_order ASC, p.id ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-xl font-serif tracking-[0.18em] text-saray-gold">Toplu Fiyat Güncelleme</h2>
            <p class="text-sm text-saray-muted">Tüm ürünlerin fiyatlarını tek ekranda düzenleyin.</p>
        </div>
        <form method="GET" class="flex items-center gap-3">
            <label class="text-sm text-saray-muted">Kategori</label>
            <select name="category" class="bg-white/5 border border-saray-gold/30 rounded-lg px-3 py-2 text-sm text-saray-text focus:border-saray-gold focus:ring-1 focus:ring-saray-gold">
                <option value="0" <?php echo $selectedCategory === 0 ? 'selected' : ''; ?>>Tümü</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int)$cat['id']; ?>" <?php echo $selectedCategory === (int)$cat['id'] ? 'selected' : ''; ?>><?php echo sanitize($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="px-4 py-2 bg-saray-gold text-saray-black rounded-lg text-sm font-semibold hover:bg-saray-darkGold transition" type="submit">Filtrele</button>
        </form>
    </div>

    <form method="POST" class="glass rounded-2xl border border-saray-gold/20 p-4 bg-black/50 overflow-x-auto">
        <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
        <table class="min-w-full text-sm text-saray-text">
            <thead>
                <tr class="text-left border-b border-saray-gold/20">
                    <th class="py-3 px-2">ID</th>
                    <th class="py-3 px-2">Kategori</th>
                    <th class="py-3 px-2">Ürün</th>
                    <th class="py-3 px-2">Mevcut Fiyat</th>
                    <th class="py-3 px-2">Yeni Fiyat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-saray-gold/10">
                <?php foreach ($products as $product): ?>
                    <?php $currentPrice = number_format((float)$product['price'], 2, '.', ''); ?>
                    <tr class="hover:bg-white/5">
                        <td class="py-3 px-2 whitespace-nowrap text-saray-muted">#<?php echo $product['id']; ?></td>
                        <td class="py-3 px-2 whitespace-nowrap text-saray-muted"><?php echo $product['category_name'] ? sanitize($product['category_name']) : 'Kategori Yok'; ?></td>
                        <td class="py-3 px-2 min-w-[180px] text-saray-text"><?php echo sanitize($product['name']); ?></td>
                        <td class="py-3 px-2 whitespace-nowrap text-saray-gold font-semibold"><?php echo $currentPrice; ?> ₺</td>
                        <td class="py-3 px-2">
                            <input
                                type="text"
                                name="price[<?php echo $product['id']; ?>]"
                                value="<?php echo $currentPrice; ?>"
                                placeholder="<?php echo $currentPrice; ?>"
                                class="w-32 bg-white/5 border border-saray-gold/30 rounded-lg px-3 py-2 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold"
                            >
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="5" class="py-6 text-center text-saray-muted">Bu filtre ile ürün bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-4 flex items-center justify-end">
            <button type="submit" class="px-4 py-2 bg-saray-gold text-saray-black rounded-lg font-semibold hover:bg-saray-darkGold transition">Değişiklikleri Kaydet</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/footer.php'; ?>

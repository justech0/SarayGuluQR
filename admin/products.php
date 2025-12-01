<?php
require_once __DIR__ . '/functions.php';
require_login();

ensure_default_menu($pdo);

$categories = $pdo->query('SELECT id, name, parent_id FROM categories ORDER BY parent_id IS NOT NULL, name ASC')->fetchAll();
$categoryById = [];
foreach ($categories as $cat) {
    $categoryById[$cat['id']] = $cat;
}

$categoryOptions = array_map(function ($cat) use ($categoryById) {
    $label = $cat['name'];
    if (!empty($cat['parent_id']) && isset($categoryById[$cat['parent_id']])) {
        $label = $categoryById[$cat['parent_id']]['name'] . ' › ' . $cat['name'];
    }
    return ['id' => $cat['id'], 'label' => $label];
}, $categories);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        flash_message('error', 'Geçersiz istek.');
        header('Location: products.php');
        exit;
    }
    $bumped = false;
    try {
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $imagePath = handle_image_upload('image', __DIR__ . '/uploads/products');

            $stmt = $pdo->prepare('INSERT INTO products (name, description, price, category_id, image_path) VALUES (:name, :description, :price, :category_id, :image_path)');
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category_id' => $categoryId,
                ':image_path' => $imagePath ? str_replace(__DIR__ . '/', '', $imagePath) : null,
            ]);
            $bumped = true;
            flash_message('success', 'Ürün eklendi.');
        }

        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $newImage = handle_image_upload('image', __DIR__ . '/uploads/products');

            if ($newImage) {
                $stmt = $pdo->prepare('UPDATE products SET name=:name, description=:description, price=:price, category_id=:category_id, image_path=:image_path WHERE id=:id');
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $price,
                    ':category_id' => $categoryId,
                    ':image_path' => str_replace(__DIR__ . '/', '', $newImage),
                    ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare('UPDATE products SET name=:name, description=:description, price=:price, category_id=:category_id WHERE id=:id');
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $price,
                    ':category_id' => $categoryId,
                    ':id' => $id,
                ]);
            }
            $bumped = true;
            flash_message('success', 'Ürün güncellendi.');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM products WHERE id=:id');
            $stmt->execute([':id' => $id]);
            $bumped = true;
            flash_message('success', 'Ürün silindi.');
        }

        if ($bumped) {
            bump_menu_version($pdo);
        }
    } catch (Throwable $e) {
        flash_message('error', 'Kaydedilemedi: ' . $e->getMessage());
    }

    header('Location: products.php');
    exit;
}

$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id=:id');
    $stmt->execute([':id' => (int)$_GET['edit']]);
    $editProduct = $stmt->fetch();
}

$stmt = $pdo->query('SELECT p.*, c.name AS category_name, pc.name AS parent_name FROM products p LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN categories pc ON pc.id = c.parent_id ORDER BY p.created_at DESC');
$products = $stmt->fetchAll();

include 'header.php';
?>
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-lg text-saray-gold tracking-[0.15em]">Ürünler</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($products as $product): ?>
                <div class="p-4 rounded-xl border border-saray-gold/10 bg-white/5 flex flex-col gap-3">
                    <div class="w-full h-44 rounded-lg overflow-hidden bg-black/40 border border-saray-gold/20">
                        <?php if ($product['image_path']): ?>
                            <img src="<?php echo sanitize($product['image_path']); ?>" alt="<?php echo sanitize($product['name']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-saray-muted text-xs">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-saray-text text-lg"><?php echo sanitize($product['name']); ?></h3>
                            <p class="text-xs text-saray-muted"><?php echo sanitize(trim(($product['parent_name'] ? $product['parent_name'].' › ' : '') . ($product['category_name'] ?? 'Kategori Yok'))); ?></p>
                        </div>
                        <div class="text-saray-gold font-serif text-xl">₺<?php echo number_format((float)$product['price'], 2, ',', '.'); ?></div>
                    </div>
                    <p class="text-sm text-saray-muted leading-snug flex-1"><?php echo sanitize($product['description']); ?></p>
                    <div class="flex gap-2">
                        <a href="?edit=<?php echo $product['id']; ?>" class="px-3 py-1 rounded-lg bg-saray-gold/15 text-saray-gold text-xs">Düzenle</a>
                        <form method="POST" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                            <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                            <button class="px-3 py-1 rounded-lg bg-red-500/20 text-red-200 text-xs">Sil</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="glass rounded-2xl border border-saray-gold/20 p-6 bg-black/50">
        <h3 class="font-serif text-md text-saray-gold tracking-[0.1em] mb-4"><?php echo $editProduct ? 'Ürün Düzenle' : 'Yeni Ürün'; ?></h3>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="action" value="<?php echo $editProduct ? 'update' : 'create'; ?>">
            <?php if ($editProduct): ?>
                <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
            <?php endif; ?>
            <div>
                <label class="block text-xs text-saray-muted mb-1">Ürün Adı</label>
                <input name="name" value="<?php echo $editProduct ? sanitize($editProduct['name']) : ''; ?>" required class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-3 py-2 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none">
            </div>
            <div>
                <label class="block text-xs text-saray-muted mb-1">Kategori</label>
                <select name="category_id" class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-3 py-2 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none">
                    <?php foreach ($categoryOptions as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $editProduct && (int)$editProduct['category_id'] === (int)$cat['id'] ? 'selected' : ''; ?>><?php echo sanitize($cat['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs text-saray-muted mb-1">Fiyat</label>
                <input type="number" step="0.01" name="price" value="<?php echo $editProduct ? sanitize($editProduct['price']) : ''; ?>" required class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-3 py-2 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none">
            </div>
            <div>
                <label class="block text-xs text-saray-muted mb-1">Açıklama</label>
                <textarea name="description" rows="3" class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-3 py-2 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none"><?php echo $editProduct ? sanitize($editProduct['description']) : ''; ?></textarea>
            </div>
            <div>
                <label class="block text-xs text-saray-muted mb-1">Görsel (WebP)</label>
                <input type="file" name="image" accept="image/*" class="w-full text-sm text-saray-muted">
                <?php if ($editProduct && $editProduct['image_path']): ?>
                    <p class="text-[10px] text-saray-muted mt-1">Mevcut: <?php echo sanitize($editProduct['image_path']); ?></p>
                <?php endif; ?>
            </div>
            <button class="w-full py-2 bg-saray-gold text-saray-black rounded-lg font-semibold hover:bg-saray-darkGold transition">
                <?php echo $editProduct ? 'Güncelle' : 'Ekle'; ?>
            </button>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>

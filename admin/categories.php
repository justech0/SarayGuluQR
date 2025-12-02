<?php
require_once __DIR__ . '/functions.php';
require_login();

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        flash_message('error', 'Geçersiz istek.');
        header('Location: categories.php');
        exit;
    }
    $bumped = false;
    try {
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $imagePath = handle_image_upload('image', __DIR__ . '/uploads/categories');

            $stmt = $pdo->prepare('INSERT INTO categories (name, description, image_path) VALUES (:name, :description, :image_path)');
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':image_path' => $imagePath
            ]);
            $bumped = true;
            flash_message('success', 'Kategori eklendi.');
        }

        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $newImage = handle_image_upload('image', __DIR__ . '/uploads/categories');

            if ($newImage) {
                $stmt = $pdo->prepare('UPDATE categories SET name=:name, description=:description, image_path=:image_path WHERE id=:id');
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':image_path' => $newImage,
                    ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare('UPDATE categories SET name=:name, description=:description WHERE id=:id');
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':id' => $id,
                ]);
            }
            $bumped = true;
            flash_message('success', 'Kategori güncellendi.');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id=:id');
            $stmt->execute([':id' => $id]);
            $bumped = true;
            flash_message('success', 'Kategori silindi.');
        }

        if ($bumped) {
            bump_menu_version($pdo);
        }
    } catch (Throwable $e) {
        flash_message('error', 'Kaydedilemedi: ' . $e->getMessage());
    }

    header('Location: categories.php');
    exit;
}

$editCategory = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id=:id');
    $stmt->execute([':id' => (int)$_GET['edit']]);
    $editCategory = $stmt->fetch();
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY created_at DESC')->fetchAll();

include 'header.php';
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-lg text-saray-gold tracking-[0.15em]">Kategoriler</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($categories as $cat): ?>
                <div class="p-4 rounded-xl border border-saray-gold/10 bg-white/5 flex items-center gap-4">
                    <div class="w-20 h-20 rounded-lg overflow-hidden bg-black/40 border border-saray-gold/20">
                        <?php if (!empty($cat['image_path'])): ?>
                            <img src="<?php echo '/admin/' . sanitize(ltrim($cat['image_path'], '/')); ?>" alt="<?php echo sanitize($cat['name'] ?? ''); ?>" class="w-full h-full object-contain bg-black">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-saray-muted text-xs">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold text-saray-text"><?php echo sanitize($cat['name'] ?? ''); ?></h3>
                            <span class="text-[10px] text-saray-muted"><?php echo date('d.m.Y', strtotime($cat['created_at']));?></span>
                        </div>
                        <p class="text-sm text-saray-muted leading-snug"><?php echo sanitize($cat['description'] ?? ''); ?></p>
                        <div class="flex gap-2 mt-3">
                            <a href="?edit=<?php echo $cat['id']; ?>" class="px-3 py-1 rounded-lg bg-saray-gold/15 text-saray-gold text-xs">Düzenle</a>
                            <form method="POST" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                <button class="px-3 py-1 rounded-lg bg-red-500/20 text-red-200 text-xs">Sil</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="glass rounded-2xl border border-saray-gold/20 p-6 bg-black/50">
        <h3 class="font-serif text-md text-saray-gold tracking-[0.1em] mb-4"><?php echo $editCategory ? 'Kategori Düzenle' : 'Yeni Kategori'; ?></h3>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="action" value="<?php echo $editCategory ? 'update' : 'create'; ?>">
            <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
            <?php endif; ?>
            <div>
                <label class="block text-xs text-saray-muted mb-1">Kategori Adı</label>
                <input name="name" value="<?php echo $editCategory ? sanitize($editCategory['name']) : ''; ?>" required class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-3 py-2 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none">
            </div>
            <div>
                <label class="block text-xs text-saray-muted mb-1">Açıklama</label>
                <textarea name="description" rows="3" class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-3 py-2 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none"><?php echo $editCategory ? sanitize($editCategory['description']) : ''; ?></textarea>
            </div>
            <div>
                <label class="block text-xs text-saray-muted mb-1">Görsel (WebP)</label>
                <input type="file" name="image" accept="image/*" class="w-full text-sm text-saray-muted">
                <?php if ($editCategory && $editCategory['image_path']): ?>
                    <p class="text-[10px] text-saray-muted mt-1">Mevcut: <?php echo sanitize($editCategory['image_path']); ?></p>
                <?php endif; ?>
            </div>
            <button class="w-full py-2 bg-saray-gold text-saray-black rounded-lg font-semibold hover:bg-saray-darkGold transition">
                <?php echo $editCategory ? 'Güncelle' : 'Ekle'; ?>
            </button>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>

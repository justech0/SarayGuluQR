<?php
require_once __DIR__ . '/functions.php';
require_login();

ensure_campaign_schema($pdo);
ensure_category_schema($pdo);
ensure_products_schema($pdo);

$current = $pdo->query('SELECT * FROM campaigns ORDER BY id ASC LIMIT 1')->fetch();
$current = $current ?: ['id' => null, 'image_path' => null, 'is_active' => 0];
if (!$current['id']) {
    $pdo->exec("INSERT INTO campaigns (image_path, is_active) VALUES (NULL, 0)");
    $current = $pdo->query('SELECT * FROM campaigns ORDER BY id ASC LIMIT 1')->fetch();
}
$isCurrentlyActive = !empty($current['is_active']);
$previewSrc = $current['image_path'] ? '/admin/' . ltrim($current['image_path'], '/') : null;

$action = $_POST['action'] ?? 'save';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        flash_message('error', 'Geçersiz istek.');
        header('Location: campaign.php');
        exit;
    }

    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $newImage = handle_image_upload('image', __DIR__ . '/uploads/campaigns');

    try {
        if ($action === 'delete_image') {
            $stmt = $pdo->prepare('UPDATE campaigns SET image_path = NULL, is_active = 0 WHERE id = :id');
            $stmt->execute([':id' => $current['id']]);
            if (!empty($current['image_path'])) {
                $filePath = __DIR__ . '/' . ltrim($current['image_path'], '/');
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            flash_message('success', 'Kampanya görseli silindi.');
        } else {
            if (!empty($_FILES['image']['name']) && !$newImage) {
                throw new RuntimeException('Görsel yüklenemedi. Lütfen farklı bir dosya deneyin.');
            }

            if ($newImage) {
                $relativePath = relative_upload_path($newImage);
                $stmt = $pdo->prepare('UPDATE campaigns SET image_path = :path, is_active = :active WHERE id = :id');
                $stmt->execute([
                    ':path' => $relativePath,
                    ':active' => $isActive,
                    ':id' => $current['id'],
                ]);
            } else {
                $stmt = $pdo->prepare('UPDATE campaigns SET is_active = :active WHERE id = :id');
                $stmt->execute([
                    ':active' => $isActive,
                    ':id' => $current['id'],
                ]);
            }
            flash_message('success', 'Kampanya ayarları güncellendi.');
        }

        bump_menu_version($pdo);
    } catch (Throwable $e) {
        flash_message('error', 'Kaydedilemedi: ' . $e->getMessage());
    }

    header('Location: campaign.php');
    exit;
}

include 'header.php';
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="p-6 rounded-2xl border border-saray-gold/20 bg-black/40">
            <h2 class="font-serif text-lg text-saray-gold tracking-[0.15em] mb-4">Kampanya Pop-up</h2>

            <form method="POST" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" <?php echo $isCurrentlyActive ? 'checked' : ''; ?>>
                        <span class="w-16 h-9 flex items-center rounded-full p-1 transition-all duration-300 <?php echo $isCurrentlyActive ? 'bg-green-500/70' : 'bg-red-500/60'; ?> peer-checked:bg-green-500/70">
                            <span class="w-7 h-7 bg-white rounded-full shadow-md transform transition-all duration-300 peer-checked:translate-x-7"></span>
                        </span>
                        <span class="ml-3 text-sm text-saray-text">Aktif</span>
                    </label>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs text-saray-muted">Görsel Yükle</label>
                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-saray-muted">
                    <?php if (!empty($current['image_path'])): ?>
                        <p class="text-[11px] text-saray-muted">Mevcut: <?php echo sanitize($current['image_path']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="flex gap-3">
                    <button name="action" value="save" class="px-4 py-2 bg-saray-gold text-saray-black rounded-lg font-semibold hover:bg-saray-darkGold transition text-sm">Kaydet</button>
                    <?php if (!empty($current['image_path'])): ?>
                        <button name="action" value="delete_image" class="px-4 py-2 border border-red-400/60 text-red-100 rounded-lg text-sm hover:bg-red-500/10" type="submit">Görseli Sil</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div class="glass rounded-2xl border border-saray-gold/20 p-6 bg-black/50">
        <h3 class="font-serif text-md text-saray-gold tracking-[0.1em] mb-4">Önizleme</h3>
        <?php if (!empty($current['image_path'])): ?>
            <div class="w-full rounded-xl overflow-hidden border border-saray-gold/30 bg-black/40 flex items-center justify-center">
                <img src="<?php echo sanitize($previewSrc); ?>" alt="Kampanya" class="w-full max-h-64 object-contain">
            </div>
        <?php else: ?>
            <p class="text-saray-muted text-sm">Henüz bir görsel eklenmedi.</p>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer.php'; ?>

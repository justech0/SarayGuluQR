<?php
require_once __DIR__ . '/functions.php';
require_login();

ensure_campaign_schema($pdo);
ensure_category_schema($pdo);
ensure_products_schema($pdo);

$current = $pdo->query('SELECT * FROM campaigns ORDER BY id ASC LIMIT 1')->fetch();

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
        if ($newImage) {
            $stmt = $pdo->prepare('UPDATE campaigns SET image_path = :path, is_active = :active WHERE id = :id');
            $stmt->execute([
                ':path' => str_replace(__DIR__ . '/', '', $newImage),
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
        bump_menu_version($pdo);
        flash_message('success', 'Kampanya ayarları güncellendi.');
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
            <h2 class="font-serif text-lg text-saray-gold tracking-[0.15em] mb-3">Kampanya Görseli</h2>
            <p class="text-sm text-saray-muted mb-4">Splash ekran sonrasında ilk girişte bir kez gösterilecek küçük pop-up için görsel ekleyin. WebP formatı otomatik oluşturulur.</p>

            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
                <div class="flex items-center gap-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" class="w-4 h-4 text-saray-gold border-saray-gold/40 rounded bg-white/5" <?php echo !empty($current['is_active']) ? 'checked' : ''; ?>>
                        <span class="text-sm text-saray-text">Aktif</span>
                    </label>
                    <span class="text-xs text-saray-muted">Aktif olduğunda görsel ilk ziyaret sonrası bir kez gösterilir.</span>
                </div>

                <div>
                    <label class="block text-xs text-saray-muted mb-1">Görsel (WebP)</label>
                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-saray-muted">
                    <?php if (!empty($current['image_path'])): ?>
                        <p class="text-[11px] text-saray-muted mt-2">Mevcut: <?php echo sanitize($current['image_path']); ?></p>
                    <?php endif; ?>
                </div>

                <button class="px-4 py-2 bg-saray-gold text-saray-black rounded-lg font-semibold hover:bg-saray-darkGold transition text-sm">Kaydet</button>
            </form>
        </div>
    </div>
    <div class="glass rounded-2xl border border-saray-gold/20 p-6 bg-black/50">
        <h3 class="font-serif text-md text-saray-gold tracking-[0.1em] mb-4">Önizleme</h3>
        <?php if (!empty($current['image_path'])): ?>
            <div class="w-full rounded-xl overflow-hidden border border-saray-gold/20 bg-black/40">
                <img src="<?php echo sanitize($current['image_path']); ?>" alt="Kampanya" class="w-full h-56 object-contain bg-black/30">
            </div>
        <?php else: ?>
            <p class="text-saray-muted text-sm">Henüz bir görsel eklenmedi.</p>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer.php'; ?>

<?php
require_once __DIR__ . '/functions.php';
require_login();
ensure_campaign_table($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash_message('error', 'Geçersiz oturum.');
        header('Location: campaign.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save') {
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $imagePath = handle_image_upload('image', __DIR__ . '/uploads/campaigns');

            $stmt = $pdo->prepare('SELECT image_path FROM campaigns WHERE id = 1');
            $stmt->execute();
            $existing = $stmt->fetch();
            $currentImage = $existing['image_path'] ?? null;

            if ($imagePath) {
                if ($currentImage && file_exists(__DIR__ . '/' . $currentImage)) {
                    @unlink(__DIR__ . '/' . $currentImage);
                }
                $currentImage = $imagePath;
            }

            $upsert = $pdo->prepare('REPLACE INTO campaigns (id, is_active, image_path) VALUES (1, :active, :image)');
            $upsert->execute([
                ':active' => $isActive,
                ':image' => $currentImage,
            ]);
            bump_menu_version($pdo);

            flash_message('success', 'Kampanya güncellendi.');
        } elseif ($action === 'delete_image') {
            $stmt = $pdo->prepare('SELECT image_path FROM campaigns WHERE id = 1');
            $stmt->execute();
            $row = $stmt->fetch();
            $path = $row['image_path'] ?? null;

            if ($path && file_exists(__DIR__ . '/' . $path)) {
                @unlink(__DIR__ . '/' . $path);
            }

            $pdo->prepare('UPDATE campaigns SET image_path = NULL, is_active = 0 WHERE id = 1')->execute();
            bump_menu_version($pdo);
            flash_message('success', 'Görsel silindi ve kampanya pasif edildi.');
        }
    } catch (Throwable $e) {
        flash_message('error', 'Kayıt başarısız: ' . $e->getMessage());
    }

    header('Location: campaign.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM campaigns WHERE id = 1');
$stmt->execute();
$campaign = $stmt->fetch();
$isActive = (int)($campaign['is_active'] ?? 0) === 1;
$imagePath = $campaign['image_path'] ?? null;

require_once __DIR__ . '/header.php';
?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-serif text-saray-gold tracking-[0.12em]">Kampanya Pop-up</h1>
            <p class="text-sm text-saray-muted">Görseli ve görünürlüğü buradan yönetin.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-3">
                <span class="text-sm text-saray-muted">Durum:</span>
                <label class="relative inline-flex items-center cursor-pointer select-none">
                    <input id="is_active" name="is_active" type="checkbox" form="campaignForm" class="sr-only peer" <?php echo $isActive ? 'checked' : ''; ?>>
                    <div class="w-16 h-9 bg-white/10 peer-checked:bg-saray-gold/70 rounded-full border border-saray-gold/40 transition-colors relative">
                        <div class="absolute top-1 left-1 w-7 h-7 bg-white rounded-full shadow transform transition-transform duration-300 peer-checked:translate-x-7"></div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <form id="campaignForm" method="POST" enctype="multipart/form-data" class="bg-black/50 border border-saray-gold/15 rounded-2xl p-6 space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="action" value="save">

        <div class="space-y-2">
            <label class="text-sm text-saray-muted">Kampanya Görseli</label>
            <input type="file" name="image" accept="image/*" class="block w-full text-sm text-saray-text bg-black/40 border border-saray-gold/20 rounded-lg p-3" />
            <p class="text-xs text-saray-muted">WebP/JPG/PNG yükleyebilirsiniz. Yeni yükleme mevcut görseli değiştirir.</p>
        </div>

        <div class="border border-dashed border-saray-gold/30 rounded-xl p-4 bg-white/5">
            <?php if ($imagePath): ?>
                <div class="flex items-center gap-4">
                    <img src="/admin/<?php echo sanitize($imagePath); ?>" alt="Kampanya" class="h-32 w-auto rounded-lg object-contain bg-black/40 border border-saray-gold/20">
                    <div class="space-y-2">
                        <p class="text-sm text-saray-text">Güncel görsel</p>
                        <button form="deleteImageForm" type="submit" class="px-3 py-2 text-sm rounded-lg border border-red-500/50 text-red-200 hover:bg-red-500/10 transition">Görseli Sil</button>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-sm text-saray-muted">Henüz bir görsel eklenmedi.</p>
            <?php endif; ?>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-3 rounded-lg bg-saray-gold text-saray-black font-semibold hover:bg-saray-darkGold transition">Kaydet</button>
            <a href="index.php" class="px-4 py-3 rounded-lg border border-saray-gold/30 text-saray-text hover:bg-white/5">İptal</a>
        </div>
    </form>

    <form id="deleteImageForm" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="action" value="delete_image">
    </form>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>

<?php include 'header.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        flash_message('error', 'Geçersiz istek.');
        header('Location: branches.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $wifi_name = trim($_POST['wifi_name'] ?? '');
    $wifi_password = trim($_POST['wifi_password'] ?? '');

    $stmt = $pdo->prepare('UPDATE branches SET wifi_name=:wifi_name, wifi_password=:wifi_password WHERE id=:id');
    $stmt->execute([
        ':wifi_name' => $wifi_name,
        ':wifi_password' => $wifi_password,
        ':id' => $id,
    ]);
    flash_message('success', 'WiFi bilgileri güncellendi.');
    header('Location: branches.php');
    exit;
}

$branches = $pdo->query('SELECT * FROM branches ORDER BY id ASC')->fetchAll();
?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="font-serif text-lg text-saray-gold tracking-[0.15em]">Şubeler & WiFi</h2>
        <p class="text-xs text-saray-muted">Müşterilerin kolayca kopyalayacağı bilgiler</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php foreach ($branches as $branch): ?>
            <form method="POST" class="p-5 rounded-2xl border border-saray-gold/15 bg-white/5 space-y-3">
                <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="id" value="<?php echo $branch['id']; ?>">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-saray-text"><?php echo sanitize($branch['name']); ?></h3>
                    <span class="text-[10px] text-saray-muted">Güncelle</span>
                </div>
                <div>
                    <label class="block text-xs text-saray-muted mb-1">WiFi Adı</label>
                    <input name="wifi_name" value="<?php echo sanitize($branch['wifi_name']); ?>" class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-3 py-2 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none">
                </div>
                <div>
                    <label class="block text-xs text-saray-muted mb-1">WiFi Şifresi</label>
                    <input name="wifi_password" value="<?php echo sanitize($branch['wifi_password']); ?>" class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-3 py-2 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none">
                </div>
                <button class="w-full py-2 bg-saray-gold text-saray-black rounded-lg font-semibold hover:bg-saray-darkGold transition">Kaydet</button>
            </form>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'footer.php'; ?>

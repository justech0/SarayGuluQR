<?php include 'header.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        flash_message('error', 'Geçersiz istek.');
        header('Location: feedbacks.php');
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM feedbacks WHERE id=:id');
    $stmt->execute([':id' => $id]);
    flash_message('success', 'Yorum silindi.');
    header('Location: feedbacks.php');
    exit;
}

$feedbacks = $pdo->query('SELECT * FROM feedbacks ORDER BY created_at DESC')->fetchAll();
?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="font-serif text-lg text-saray-gold tracking-[0.15em]">Geri Bildirimler</h2>
        <p class="text-xs text-saray-muted">Müşteri puanları ve yorumlar</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php foreach ($feedbacks as $fb): ?>
            <div class="p-4 rounded-xl border border-saray-gold/10 bg-white/5 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-saray-text"><?php echo sanitize($fb['customer_name']); ?></div>
                        <div class="text-[10px] text-saray-muted"><?php echo date('d.m.Y H:i', strtotime($fb['created_at'])); ?></div>
                    </div>
                    <div class="text-saray-gold font-bold">★ <?php echo (int)$fb['rating']; ?>/5</div>
                </div>
                <p class="text-sm text-saray-muted leading-snug flex-1"><?php echo sanitize($fb['comment']); ?></p>
                <form method="POST" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                    <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="id" value="<?php echo $fb['id']; ?>">
                    <button class="w-full py-2 bg-red-500/20 text-red-200 rounded-lg text-sm">Sil</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'footer.php'; ?>

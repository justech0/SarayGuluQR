<?php
require_once __DIR__ . '/functions.php';
require_login();
ensure_feedback_schema($pdo);

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

$feedbacks = $pdo->query('SELECT f.*, b.name AS branch_name FROM feedbacks f LEFT JOIN branches b ON b.id = f.branch_id ORDER BY f.created_at DESC')->fetchAll();
$topicLabels = [
    'taste' => 'Lezzet ve Kalite',
    'service' => 'Hizmet Hızı',
    'staff' => 'Personel',
    'hygiene' => 'Hijyen',
    'other' => 'Diğer',
];

include 'header.php';
?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="font-serif text-lg text-saray-gold tracking-[0.15em]">Geri Bildirimler</h2>
        <p class="text-xs text-saray-muted">Müşteri puanları ve yorumlar</p>
    </div>

    <?php if (empty($feedbacks)): ?>
        <div class="p-6 rounded-xl border border-saray-gold/10 bg-white/5 text-saray-muted text-sm">Henüz geri bildirim alınmadı.</div>
    <?php else: ?>
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

                    <div class="text-[11px] text-saray-muted space-y-1">
                        <?php if (!empty($fb['branch_name'])): ?>
                            <div>Şube: <span class="text-saray-text"><?php echo sanitize($fb['branch_name']); ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($fb['topic'])): ?>
                            <?php $topicText = $topicLabels[$fb['topic']] ?? $fb['topic']; ?>
                            <div>Konu: <span class="text-saray-text uppercase text-[10px]"><?php echo sanitize($topicText); ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($fb['contact'])): ?>
                            <div>İletişim: <span class="text-saray-text"><?php echo sanitize($fb['contact']); ?></span></div>
                        <?php endif; ?>
                    </div>

                    <p class="text-sm text-saray-muted leading-snug flex-1 border-t border-saray-gold/10 pt-2 mt-2"><?php echo sanitize($fb['comment']); ?></p>
                    <form method="POST" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                        <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="id" value="<?php echo $fb['id']; ?>">
                        <button class="w-full py-2 bg-red-500/20 text-red-200 rounded-lg text-sm">Sil</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>

<?php include 'header.php'; ?>
<?php
$counts = fetch_counts($pdo);

$stmt = $pdo->query('SELECT customer_name, rating, comment, created_at FROM feedbacks ORDER BY created_at DESC LIMIT 5');
$latestFeedbacks = $stmt->fetchAll();
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="p-6 rounded-2xl border border-saray-gold/30 bg-black/40">
        <p class="text-xs uppercase tracking-widest text-saray-muted mb-2">Toplam Ürün</p>
        <div class="text-3xl font-serif text-saray-gold"><?php echo $counts['products']; ?></div>
    </div>
    <div class="p-6 rounded-2xl border border-saray-gold/30 bg-black/40">
        <p class="text-xs uppercase tracking-widest text-saray-muted mb-2">Toplam Kategori</p>
        <div class="text-3xl font-serif text-saray-gold"><?php echo $counts['categories']; ?></div>
    </div>
    <div class="p-6 rounded-2xl border border-saray-gold/30 bg-black/40">
        <p class="text-xs uppercase tracking-widest text-saray-muted mb-2">Geri Bildirimler</p>
        <div class="text-3xl font-serif text-saray-gold"><?php echo $counts['feedbacks']; ?></div>
    </div>
</div>

<div class="bg-black/50 border border-saray-gold/20 rounded-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-serif text-lg text-saray-gold tracking-[0.15em]">Son Yorumlar</h2>
        <a href="feedbacks.php" class="text-xs text-saray-muted underline">Tümü</a>
    </div>
    <?php if (empty($latestFeedbacks)): ?>
        <p class="text-saray-muted text-sm">Henüz yorum yok.</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($latestFeedbacks as $feedback): ?>
                <div class="p-4 rounded-xl border border-saray-gold/10 bg-white/5">
                    <div class="flex items-center justify-between mb-1">
                        <div class="text-sm font-semibold text-saray-text"><?php echo sanitize($feedback['customer_name']); ?></div>
                        <div class="text-xs text-saray-gold font-bold">★ <?php echo (int)$feedback['rating']; ?>/5</div>
                    </div>
                    <p class="text-sm text-saray-muted">"<?php echo sanitize($feedback['comment']); ?>"</p>
                    <p class="text-[10px] text-saray-muted mt-2"><?php echo date('d.m.Y H:i', strtotime($feedback['created_at'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>

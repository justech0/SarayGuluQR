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
                <?php $imgPath = !empty($fb['image_path']) ? '/admin/' . ltrim($fb['image_path'], '/') : null; ?>
                <div class="p-4 rounded-xl border border-saray-gold/10 bg-white/5 flex flex-col gap-2 fb-card" data-comment="<?php echo sanitize($fb['comment']); ?>" data-image="<?php echo $imgPath ? sanitize($imgPath) : ''; ?>">
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

                    <?php if ($imgPath): ?>
                        <div class="flex items-center gap-2 pt-1">
                            <span class="text-[10px] uppercase tracking-[0.18em] text-saray-muted">Görsel</span>
                            <img src="<?php echo sanitize($imgPath); ?>" alt="Geri bildirim görseli" class="h-12 w-12 rounded-lg object-cover border border-saray-gold/30 cursor-pointer fb-preview">
                        </div>
                    <?php endif; ?>

                    <div class="flex gap-2 pt-2">
                        <button type="button" class="flex-1 py-2 bg-saray-gold/15 text-saray-gold rounded-lg text-sm fb-open">Görüntüle</button>
                        <form method="POST" onsubmit="return confirm('Silmek istediğinize emin misiniz?');" class="flex-1">
                            <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="id" value="<?php echo $fb['id']; ?>">
                            <button class="w-full py-2 bg-red-500/20 text-red-200 rounded-lg text-sm">Sil</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<div id="fbModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/70" aria-hidden="true"></div>
    <div class="relative bg-saray-black border border-saray-gold/30 rounded-2xl max-w-xl w-full p-6 flex flex-col gap-4">
        <button id="fbModalClose" class="absolute top-3 right-3 text-saray-muted hover:text-saray-gold text-xl">×</button>
        <p id="fbModalComment" class="text-saray-text text-sm leading-relaxed"></p>
        <img id="fbModalImage" src="" alt="Görsel" class="hidden w-full max-h-[60vh] object-contain rounded-lg border border-saray-gold/30" />
    </div>
</div>
<script>
  const fbModal = document.getElementById('fbModal');
  const fbModalClose = document.getElementById('fbModalClose');
  const fbModalComment = document.getElementById('fbModalComment');
  const fbModalImage = document.getElementById('fbModalImage');

  function toggleFbModal(open) {
    if (open) {
      fbModal?.classList.remove('hidden');
      fbModal?.classList.add('flex');
    } else {
      fbModal?.classList.add('hidden');
      fbModal?.classList.remove('flex');
    }
  }

  document.querySelectorAll('.fb-card').forEach(card => {
    const openBtn = card.querySelector('.fb-open');
    const preview = card.querySelector('.fb-preview');
    const comment = card.getAttribute('data-comment') || '';
    const image = card.getAttribute('data-image') || '';
    const openModal = () => {
      fbModalComment.textContent = comment;
      if (image) {
        fbModalImage.src = image;
        fbModalImage.classList.remove('hidden');
      } else {
        fbModalImage.classList.add('hidden');
        fbModalImage.src = '';
      }
      toggleFbModal(true);
    };
    openBtn?.addEventListener('click', openModal);
    preview?.addEventListener('click', openModal);
  });

  fbModalClose?.addEventListener('click', () => toggleFbModal(false));
  fbModal?.addEventListener('click', (e) => {
    if (e.target === fbModal) toggleFbModal(false);
  });
</script>
<?php include 'footer.php'; ?>

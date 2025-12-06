<?php
require_once __DIR__ . '/functions.php';
require_login();
ensure_sort_order_columns($pdo);

$categories = $pdo->query('SELECT id, name, COALESCE(sort_order, 0) AS sort_order FROM categories ORDER BY sort_order ASC, id ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        flash_message('error', 'Geçersiz istek. Lütfen tekrar deneyin.');
        header('Location: category-order.php');
        exit;
    }

    $order = $_POST['order'] ?? [];
    if (!is_array($order)) {
        $order = [];
    }

    try {
        $pdo->beginTransaction();
        $update = $pdo->prepare('UPDATE categories SET sort_order = :sort_order WHERE id = :id LIMIT 1');
        $position = 1;
        foreach ($order as $id) {
            $catId = (int)$id;
            if ($catId <= 0) {
                continue;
            }
            $update->execute([
                ':sort_order' => $position++,
                ':id' => $catId,
            ]);
        }
        $pdo->commit();
        bump_menu_version($pdo);
        flash_message('success', 'Kategori sırası güncellendi.');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash_message('error', 'Sıra kaydedilemedi: ' . sanitize($e->getMessage()));
    }

    header('Location: category-order.php');
    exit;
}

include __DIR__ . '/header.php';
?>
<div class="space-y-6">
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div>
            <h2 class="text-xl font-serif tracking-[0.18em] text-saray-gold">Kategori Sırası</h2>
            <p class="text-sm text-saray-muted">Kategorileri sürükleyip bırakarak menüdeki sırasını belirleyin.</p>
        </div>
        <div class="text-saray-muted text-sm">Sürükle-bırak ile sıralayıp kaydet butonuna basın.</div>
    </div>

    <form method="POST" class="glass rounded-2xl border border-saray-gold/20 p-4 bg-black/50 space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
        <ul id="sortable" class="space-y-2">
            <?php foreach ($categories as $cat): ?>
                <li class="flex items-center justify-between bg-white/5 border border-saray-gold/15 rounded-lg px-4 py-3 cursor-move" draggable="true" data-id="<?php echo (int)$cat['id']; ?>">
                    <div class="flex items-center gap-3">
                        <span class="text-saray-muted">☰</span>
                        <span class="text-saray-text font-medium"><?php echo sanitize($cat['name']); ?></span>
                    </div>
                    <span class="text-xs text-saray-muted">#<?php echo (int)$cat['sort_order']; ?></span>
                    <input type="hidden" name="order[]" value="<?php echo (int)$cat['id']; ?>">
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (empty($categories)): ?>
            <p class="text-center text-saray-muted">Hiç kategori bulunamadı.</p>
        <?php endif; ?>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-saray-gold text-saray-black rounded-lg font-semibold hover:bg-saray-darkGold transition">Kaydet</button>
        </div>
    </form>
</div>

<script>
    const list = document.getElementById('sortable');
    if (list) {
        let dragged;
        list.addEventListener('dragstart', (e) => {
            dragged = e.target;
            e.target.classList.add('opacity-60');
        });
        list.addEventListener('dragend', (e) => {
            e.target.classList.remove('opacity-60');
            refreshOrderInputs();
        });
        list.addEventListener('dragover', (e) => {
            e.preventDefault();
            const target = e.target.closest('li');
            if (!target || target === dragged) return;
            const rect = target.getBoundingClientRect();
            const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
            list.insertBefore(dragged, next ? target.nextSibling : target);
        });
    }

    function refreshOrderInputs() {
        const items = list.querySelectorAll('li');
        items.forEach((item, index) => {
            const input = item.querySelector('input[name="order[]"]');
            if (input) {
                input.value = item.dataset.id;
            }
            const badge = item.querySelector('span.text-xs');
            if (badge) {
                badge.textContent = `#${index + 1}`;
            }
        });
    }
</script>
<?php include __DIR__ . '/footer.php'; ?>

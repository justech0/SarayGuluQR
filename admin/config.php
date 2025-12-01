<?php
// Database configuration and shared settings
$DB_HOST = 'localhost';
$DB_NAME = 'u220042353_saray';
$DB_USER = 'u220042353_saray';
$DB_PASS = 'Saray!Gulu72.';

// Logo yolu: Hostinger'a yüklediğiniz dosyayı bu yola yerleştirin
$LOGO_URL = '/saray-logo.png';

// "Şifremi unuttum" için güvenlik metni. Güvenlik için kendi metninizi burada belirleyin.
$RESET_CHALLENGE_PROMPT = 'Size verilen güvenlik metnini aynen yazın';
$RESET_CHALLENGE_ANSWER = 'saray gulu 2024';

// Menü önbellek sürümü için meta tablosu
function ensure_meta_table(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS meta (
        meta_key VARCHAR(64) PRIMARY KEY,
        meta_value TEXT DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

function get_menu_version(PDO $pdo): int
{
    ensure_meta_table($pdo);
    $stmt = $pdo->prepare('SELECT meta_value FROM meta WHERE meta_key = :key LIMIT 1');
    $stmt->execute([':key' => 'menu_version']);
    $row = $stmt->fetch();
    if (!$row) {
        $pdo->prepare('INSERT INTO meta (meta_key, meta_value) VALUES (:key, :value)')
            ->execute([':key' => 'menu_version', ':value' => '1']);
        return 1;
    }
    return (int)$row['meta_value'] ?: 1;
}

function bump_menu_version(PDO $pdo): int
{
    ensure_meta_table($pdo);
    $current = get_menu_version($pdo) + 1;
    $stmt = $pdo->prepare('REPLACE INTO meta (meta_key, meta_value) VALUES (:key, :value)');
    $stmt->execute([':key' => 'menu_version', ':value' => (string)$current]);
    return $current;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Veritabanı bağlantısı başarısız: ' . htmlspecialchars($e->getMessage()));
}

// CSRF token helper
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function ensure_feedback_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS feedbacks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(120) NOT NULL,
        rating INT NOT NULL,
        comment TEXT,
        branch_id INT NULL,
        topic VARCHAR(50) DEFAULT NULL,
        contact VARCHAR(150) DEFAULT NULL,
        language VARCHAR(5) DEFAULT 'tr',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_feedbacks_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
        CHECK (rating BETWEEN 1 AND 5)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $columns = $pdo->query("SHOW COLUMNS FROM feedbacks")->fetchAll(PDO::FETCH_COLUMN);

    $alterations = [
        'customer_name' => "ALTER TABLE feedbacks ADD COLUMN customer_name VARCHAR(120) NOT NULL DEFAULT 'Ziyaretçi' AFTER id",
        'branch_id' => "ALTER TABLE feedbacks ADD COLUMN branch_id INT NULL AFTER comment",
        'topic' => "ALTER TABLE feedbacks ADD COLUMN topic VARCHAR(50) DEFAULT NULL AFTER branch_id",
        'contact' => "ALTER TABLE feedbacks ADD COLUMN contact VARCHAR(150) DEFAULT NULL AFTER topic",
        'language' => "ALTER TABLE feedbacks ADD COLUMN language VARCHAR(5) DEFAULT 'tr' AFTER contact",
        'created_at' => "ALTER TABLE feedbacks ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER language",
    ];

    foreach ($alterations as $column => $sql) {
        if (!in_array($column, $columns, true)) {
            $pdo->exec($sql);
        }
    }
}

function ensure_category_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        parent_id INT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $columns = $pdo->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('parent_id', $columns, true)) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN parent_id INT NULL AFTER id");
        $pdo->exec("ALTER TABLE categories ADD CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL");
    }
}

function ensure_products_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

function ensure_default_menu(PDO $pdo): void
{
    ensure_category_schema($pdo);
    ensure_products_schema($pdo);

    $categoryTree = [
        ['name' => 'GÜNE BAŞLARKEN', 'parent' => null],
        ['name' => 'TOSTLAR', 'parent' => null],
        ['name' => 'BAŞLANGIÇLAR', 'parent' => null],
        ['name' => 'BURGERLER', 'parent' => null],
        ['name' => 'SALATALAR', 'parent' => null],
        ['name' => 'PİZZA & PİDELER', 'parent' => null],
        ['name' => 'MAKARNALAR & NOODLE', 'parent' => null],
        ['name' => 'LEZZETLİ TAVUKLAR', 'parent' => null],
        ['name' => 'YÖRESEL ETLER', 'parent' => null],
        ['name' => 'KEBAP VE IZGARALAR', 'parent' => null],
        ['name' => 'FAJITALAR', 'parent' => null],
        ['name' => 'SOĞUK İÇECEKLER', 'parent' => null],
        ['name' => 'İLAVE İÇECEKLER', 'parent' => null],
        ['name' => 'SOĞUK SIKMALAR', 'parent' => null],
        ['name' => 'SICAK İÇECEKLER', 'parent' => null],
        ['name' => 'BİTKİ ÇAYLARI', 'parent' => null],
        ['name' => 'TÜRK KAHVELERİ', 'parent' => null],
        ['name' => 'DÜNYA KAHVELERİ', 'parent' => null],
        ['name' => 'DÜNYA KAHVELERİ – SERT İÇİM', 'parent' => 'DÜNYA KAHVELERİ'],
        ['name' => 'DÜNYA KAHVELERİ – YUMUŞAK İÇİM', 'parent' => 'DÜNYA KAHVELERİ'],
        ['name' => 'DÜNYA KAHVELERİ – TATLI İÇİM', 'parent' => 'DÜNYA KAHVELERİ'],
        ['name' => 'BÖLGESEL KAHVELER', 'parent' => null],
        ['name' => 'SOĞUK KAHVELER', 'parent' => null],
        ['name' => 'SOĞUK KAHVELER – SERT İÇİM', 'parent' => 'SOĞUK KAHVELER'],
        ['name' => 'SOĞUK KAHVELER – TATLI İÇİM', 'parent' => 'SOĞUK KAHVELER'],
        ['name' => 'KOKTEYLLER', 'parent' => null],
        ['name' => 'MILKSHAKELER', 'parent' => null],
        ['name' => 'FROZENLER', 'parent' => null],
        ['name' => 'FRAPPELER', 'parent' => null],
        ['name' => 'WAFFLE', 'parent' => null],
        ['name' => 'PASTALAR', 'parent' => null],
        ['name' => 'SÜTLÜ TATLILAR', 'parent' => null],
        ['name' => 'DONDURMALAR', 'parent' => null],
    ];

    $categoryIds = [];
    foreach ($categoryTree as $cat) {
        if ($cat['parent'] !== null) {
            continue;
        }
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE name = :name AND parent_id IS NULL LIMIT 1');
        $stmt->execute([':name' => $cat['name']]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            $categoryIds[$cat['name']] = (int)$existing;
            continue;
        }
        $pdo->prepare('INSERT INTO categories (name) VALUES (:name)')->execute([':name' => $cat['name']]);
        $categoryIds[$cat['name']] = (int)$pdo->lastInsertId();
    }

    // Insert children after parents are known
    foreach ($categoryTree as $cat) {
        if ($cat['parent'] === null) {
            continue;
        }
        $parentId = $categoryIds[$cat['parent']] ?? null;
        if (!$parentId) {
            continue;
        }
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE name = :name AND parent_id = :parent LIMIT 1');
        $stmt->execute([':name' => $cat['name'], ':parent' => $parentId]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            $categoryIds[$cat['name']] = (int)$existing;
            continue;
        }
        $pdo->prepare('INSERT INTO categories (name, parent_id) VALUES (:name, :parent)')
            ->execute([':name' => $cat['name'], ':parent' => $parentId]);
        $categoryIds[$cat['name']] = (int)$pdo->lastInsertId();
    }

    $products = [
        ['cat' => 'GÜNE BAŞLARKEN', 'name' => 'Yöresel serpme kahvaltı'],
        ['cat' => 'GÜNE BAŞLARKEN', 'name' => 'Tek kişilik kahvaltı tabağı'],
        ['cat' => 'GÜNE BAŞLARKEN', 'name' => 'Kavurmalı yumurta'],
        ['cat' => 'GÜNE BAŞLARKEN', 'name' => 'Tereyağlı sahanda yumurta'],
        ['cat' => 'GÜNE BAŞLARKEN', 'name' => 'Sucuklu yumurta'],
        ['cat' => 'GÜNE BAŞLARKEN', 'name' => 'Pastırmalı yumurta'],
        ['cat' => 'GÜNE BAŞLARKEN', 'name' => 'Omlet çeşitleri'],
        ['cat' => 'TOSTLAR', 'name' => 'Kaşarlı tost'],
        ['cat' => 'TOSTLAR', 'name' => 'Karışık tost'],
        ['cat' => 'TOSTLAR', 'name' => 'Kavurmalı kaşarlı tost'],
        ['cat' => 'BAŞLANGIÇLAR', 'name' => 'Günün çorbası'],
        ['cat' => 'BAŞLANGIÇLAR', 'name' => 'Patates tava'],
        ['cat' => 'BAŞLANGIÇLAR', 'name' => 'Atıştırma sepeti'],
        ['cat' => 'BURGERLER', 'name' => 'Klasik hamburger'],
        ['cat' => 'BURGERLER', 'name' => 'Cheese burger'],
        ['cat' => 'BURGERLER', 'name' => 'Çıtır tavuk burger'],
        ['cat' => 'BURGERLER', 'name' => 'Sultan lokum burger'],
        ['cat' => 'SALATALAR', 'name' => 'Tavuklu Sezar salata'],
        ['cat' => 'SALATALAR', 'name' => 'Çoban salata'],
        ['cat' => 'SALATALAR', 'name' => 'Izgara hellim salata'],
        ['cat' => 'SALATALAR', 'name' => 'Ton balıklı salata'],
        ['cat' => 'SALATALAR', 'name' => 'Keçi peynirli Saray salata'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Margarita pizza'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Mantarlı pizza'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Mix karnaval pizza'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Pizza ala tono'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Üç peynirli pastırmalı pizza'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Sucuklu pizza'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Kaşarlı pide'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Kıymalı pide'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Kuşbaşılı pide'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Kuşbaşı kaşarlı'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Kıymalı kaşarlı'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Pide special'],
        ['cat' => 'PİZZA & PİDELER', 'name' => 'Lahmacun'],
        ['cat' => 'MAKARNALAR & NOODLE', 'name' => 'Fettucine Alfredo'],
        ['cat' => 'MAKARNALAR & NOODLE', 'name' => 'Penne Arabiata'],
        ['cat' => 'MAKARNALAR & NOODLE', 'name' => 'Sebzeli noodle'],
        ['cat' => 'MAKARNALAR & NOODLE', 'name' => 'Tavuklu noodle'],
        ['cat' => 'MAKARNALAR & NOODLE', 'name' => 'Dana etli noodle'],
        ['cat' => 'LEZZETLİ TAVUKLAR', 'name' => 'Tavuk külbastı'],
        ['cat' => 'LEZZETLİ TAVUKLAR', 'name' => 'Fesleğenli mantarlı tavuk'],
        ['cat' => 'LEZZETLİ TAVUKLAR', 'name' => 'Kremalı mantarlı tavuk'],
        ['cat' => 'LEZZETLİ TAVUKLAR', 'name' => 'Mexican soslu tavuk'],
        ['cat' => 'LEZZETLİ TAVUKLAR', 'name' => 'Köri soslu tavuk'],
        ['cat' => 'LEZZETLİ TAVUKLAR', 'name' => 'Tavuk schnitzel'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Mantar soslu dana bonfile'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Cafe de Paris soslu dana bonfile'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Izgara dana antrikot'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Saray lokum'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Çökertme kebabı'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Izgara köfte'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Kaşarlı köfte'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Kuzu pirzola'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Fırında kuzu sırtı'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Çömlekte kuzu tandır'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Fırında kuzu incik'],
        ['cat' => 'YÖRESEL ETLER', 'name' => 'Saç tava (kuzu)'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Tavuk şiş'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Yaprak kanat'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Domatesli kebap'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Patlıcanlı kebap'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Karışık et tabağı'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Acılı kebap'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Acısız kebap'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Çöp şiş'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Ciğer'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Sarma beyti kebap'],
        ['cat' => 'KEBAP VE IZGARALAR', 'name' => 'Fıstıklı kebap'],
        ['cat' => 'FAJITALAR', 'name' => 'Dana etli fajita'],
        ['cat' => 'FAJITALAR', 'name' => 'Tavuklu fajita'],
        ['cat' => 'FAJITALAR', 'name' => 'Fajita kombo'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Su'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Sade soda'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Limonlu soda'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Meyveli soda'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Soğuk çay (limon, mango, karpuz, şeftali)'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Coca Cola'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Coca Cola Zero'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Fanta'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Enerji içeceği'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Ayran'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Yayık ayran'],
        ['cat' => 'SOĞUK İÇECEKLER', 'name' => 'Şalgam'],
        ['cat' => 'İLAVE İÇECEKLER', 'name' => 'Cappy şeftali'],
        ['cat' => 'İLAVE İÇECEKLER', 'name' => 'Cappy vişne'],
        ['cat' => 'İLAVE İÇECEKLER', 'name' => 'Cappy kayısı'],
        ['cat' => 'İLAVE İÇECEKLER', 'name' => 'Cappy karışık'],
        ['cat' => 'SOĞUK SIKMALAR', 'name' => 'Portakal suyu'],
        ['cat' => 'SOĞUK SIKMALAR', 'name' => 'Limonata'],
        ['cat' => 'SOĞUK SIKMALAR', 'name' => 'Naneli limonata'],
        ['cat' => 'SOĞUK SIKMALAR', 'name' => 'Churchill'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Çay'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Fincan çay'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Sade Nescafe'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Sütlü Nescafe'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Sıcak çikolata'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Sıcak beyaz çikolata'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Dondurmalı sıcak çikolata'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Salep'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Dondurmalı salep'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Aromalı salep'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Chai Tea Latte'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Ballı süt'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Ballı tarçınlı süt'],
        ['cat' => 'SICAK İÇECEKLER', 'name' => 'Bal badem salep'],
        ['cat' => 'BİTKİ ÇAYLARI', 'name' => 'Kış çayı'],
        ['cat' => 'BİTKİ ÇAYLARI', 'name' => 'Ihlamur'],
        ['cat' => 'BİTKİ ÇAYLARI', 'name' => 'Nane limon'],
        ['cat' => 'BİTKİ ÇAYLARI', 'name' => 'Papatya çayı'],
        ['cat' => 'BİTKİ ÇAYLARI', 'name' => 'Ada çayı'],
        ['cat' => 'BİTKİ ÇAYLARI', 'name' => 'Yeşil çay'],
        ['cat' => 'BİTKİ ÇAYLARI', 'name' => 'Elma tarçın'],
        ['cat' => 'TÜRK KAHVELERİ', 'name' => 'Türk kahvesi'],
        ['cat' => 'TÜRK KAHVELERİ', 'name' => 'Damla sakızlı Türk kahvesi'],
        ['cat' => 'TÜRK KAHVELERİ', 'name' => 'Sütlü Türk kahvesi'],
        ['cat' => 'TÜRK KAHVELERİ', 'name' => 'Süvari Türk kahvesi'],
        ['cat' => 'TÜRK KAHVELERİ', 'name' => 'Dibek kahvesi'],
        ['cat' => 'TÜRK KAHVELERİ', 'name' => 'Menengiç kahvesi'],
        ['cat' => 'TÜRK KAHVELERİ', 'name' => 'Osmanlı kahvesi'],
        ['cat' => 'DÜNYA KAHVELERİ – SERT İÇİM', 'name' => 'Espresso'],
        ['cat' => 'DÜNYA KAHVELERİ – SERT İÇİM', 'name' => 'Double espresso'],
        ['cat' => 'DÜNYA KAHVELERİ – SERT İÇİM', 'name' => 'Americano'],
        ['cat' => 'DÜNYA KAHVELERİ – SERT İÇİM', 'name' => 'Flat white'],
        ['cat' => 'DÜNYA KAHVELERİ – SERT İÇİM', 'name' => 'Cortado'],
        ['cat' => 'DÜNYA KAHVELERİ – SERT İÇİM', 'name' => 'Con panna'],
        ['cat' => 'DÜNYA KAHVELERİ – YUMUŞAK İÇİM', 'name' => 'Latte'],
        ['cat' => 'DÜNYA KAHVELERİ – YUMUŞAK İÇİM', 'name' => 'Cappuccino'],
        ['cat' => 'DÜNYA KAHVELERİ – YUMUŞAK İÇİM', 'name' => 'Affagato'],
        ['cat' => 'DÜNYA KAHVELERİ – TATLI İÇİM', 'name' => 'White Chocolate Mocha'],
        ['cat' => 'DÜNYA KAHVELERİ – TATLI İÇİM', 'name' => 'Mocha'],
        ['cat' => 'DÜNYA KAHVELERİ – TATLI İÇİM', 'name' => 'Zebra Mocha'],
        ['cat' => 'DÜNYA KAHVELERİ – TATLI İÇİM', 'name' => 'Caramel Latte'],
        ['cat' => 'DÜNYA KAHVELERİ – TATLI İÇİM', 'name' => 'Caramel Macchiato'],
        ['cat' => 'BÖLGESEL KAHVELER', 'name' => 'Guatemala'],
        ['cat' => 'BÖLGESEL KAHVELER', 'name' => 'Etiyopya'],
        ['cat' => 'BÖLGESEL KAHVELER', 'name' => 'Brezilya'],
        ['cat' => 'BÖLGESEL KAHVELER', 'name' => 'Colombia'],
        ['cat' => 'BÖLGESEL KAHVELER', 'name' => 'Kenya'],
        ['cat' => 'BÖLGESEL KAHVELER', 'name' => 'Irish Cream'],
        ['cat' => 'BÖLGESEL KAHVELER', 'name' => 'Filtre kahve'],
        ['cat' => 'SOĞUK KAHVELER – SERT İÇİM', 'name' => 'Ice Latte'],
        ['cat' => 'SOĞUK KAHVELER – SERT İÇİM', 'name' => 'Ice Cappuccino'],
        ['cat' => 'SOĞUK KAHVELER – SERT İÇİM', 'name' => 'Ice Americano'],
        ['cat' => 'SOĞUK KAHVELER – SERT İÇİM', 'name' => 'Ice Filter Coffee'],
        ['cat' => 'SOĞUK KAHVELER – TATLI İÇİM', 'name' => 'Ice White Mocha'],
        ['cat' => 'SOĞUK KAHVELER – TATLI İÇİM', 'name' => 'Ice Mocha'],
        ['cat' => 'SOĞUK KAHVELER – TATLI İÇİM', 'name' => 'Ice Caramel Latte'],
        ['cat' => 'SOĞUK KAHVELER – TATLI İÇİM', 'name' => 'Ice Caramel Macchiato'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Mojito'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Çilekli Mojito'],
        ['cat' => 'KOKTEYLLER', 'name' => 'İtalyan sodası'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Karadut Yağmuru'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Frambuaz Aşkı'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Strawberry Dream'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Gönül Çelen'],
        ['cat' => 'KOKTEYLLER', 'name' => 'White Not'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Yuppy'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Coco Choco'],
        ['cat' => 'KOKTEYLLER', 'name' => 'White Nut'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Flat Nut'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Atom Vitamin'],
        ['cat' => 'KOKTEYLLER', 'name' => 'Saray Special'],
        ['cat' => 'MILKSHAKELER', 'name' => 'Çilekli milkshake'],
        ['cat' => 'MILKSHAKELER', 'name' => 'Çikolatalı milkshake'],
        ['cat' => 'MILKSHAKELER', 'name' => 'Karamelli milkshake'],
        ['cat' => 'MILKSHAKELER', 'name' => 'Vanilyalı milkshake'],
        ['cat' => 'MILKSHAKELER', 'name' => 'Muzlu milkshake'],
        ['cat' => 'MILKSHAKELER', 'name' => 'Oreolu milkshake'],
        ['cat' => 'FROZENLER', 'name' => 'Çilekli Frozen'],
        ['cat' => 'FROZENLER', 'name' => 'Nane Limon Frozen'],
        ['cat' => 'FROZENLER', 'name' => 'Karadut Frozen'],
        ['cat' => 'FROZENLER', 'name' => 'Şeftali Frozen'],
        ['cat' => 'FROZENLER', 'name' => 'Elma Frozen'],
        ['cat' => 'FROZENLER', 'name' => 'Frambuaz Frozen'],
        ['cat' => 'FROZENLER', 'name' => 'Muz Frozen'],
        ['cat' => 'FRAPPELER', 'name' => 'Çikolatalı frappe'],
        ['cat' => 'FRAPPELER', 'name' => 'Vanilyalı frappe'],
        ['cat' => 'FRAPPELER', 'name' => 'Oreolu frappe'],
        ['cat' => 'FRAPPELER', 'name' => 'Muzlu frappe'],
        ['cat' => 'FRAPPELER', 'name' => 'Frappuccino'],
        ['cat' => 'WAFFLE', 'name' => 'Karışık waffle'],
        ['cat' => 'WAFFLE', 'name' => 'Muzlu waffle'],
        ['cat' => 'WAFFLE', 'name' => 'Dondurmalı waffle'],
        ['cat' => 'WAFFLE', 'name' => 'Ballı cevizli waffle'],
        ['cat' => 'WAFFLE', 'name' => 'Fondü'],
        ['cat' => 'WAFFLE', 'name' => 'Meyve tabağı'],
        ['cat' => 'WAFFLE', 'name' => 'Çerez tabağı'],
        ['cat' => 'PASTALAR', 'name' => 'Çikolatalı dilim pasta'],
        ['cat' => 'PASTALAR', 'name' => 'Meyveli dilim pasta'],
        ['cat' => 'PASTALAR', 'name' => 'Tiramisu dilim pasta'],
        ['cat' => 'PASTALAR', 'name' => 'Frambuazlı cheesecake'],
        ['cat' => 'PASTALAR', 'name' => 'Limonlu cheesecake'],
        ['cat' => 'PASTALAR', 'name' => 'Fıstık rüyası'],
        ['cat' => 'PASTALAR', 'name' => 'Çikolatalı suffle'],
        ['cat' => 'PASTALAR', 'name' => 'Kuru pasta tabağı'],
        ['cat' => 'PASTALAR', 'name' => 'Brownie'],
        ['cat' => 'PASTALAR', 'name' => 'Ekler (porsiyon)'],
        ['cat' => 'PASTALAR', 'name' => 'Malaga'],
        ['cat' => 'PASTALAR', 'name' => 'San Sebastian'],
        ['cat' => 'SÜTLÜ TATLILAR', 'name' => 'Profiterol'],
        ['cat' => 'SÜTLÜ TATLILAR', 'name' => 'Supangle'],
        ['cat' => 'SÜTLÜ TATLILAR', 'name' => 'Sütlaç'],
        ['cat' => 'SÜTLÜ TATLILAR', 'name' => 'Trileçe'],
        ['cat' => 'SÜTLÜ TATLILAR', 'name' => 'Magnolia'],
        ['cat' => 'DONDURMALAR', 'name' => 'Çilekli dondurma'],
        ['cat' => 'DONDURMALAR', 'name' => 'Çikolatalı dondurma'],
        ['cat' => 'DONDURMALAR', 'name' => 'Muzlu dondurma'],
        ['cat' => 'DONDURMALAR', 'name' => 'Karamelli dondurma'],
        ['cat' => 'DONDURMALAR', 'name' => 'Vanilyalı dondurma'],
        ['cat' => 'DONDURMALAR', 'name' => 'Oreolu dondurma'],
        ['cat' => 'DONDURMALAR', 'name' => 'Fıstıklı dondurma'],
        ['cat' => 'DONDURMALAR', 'name' => 'Frambuazlı dondurma'],
        ['cat' => 'DONDURMALAR', 'name' => 'Karadutlu dondurma'],
        ['cat' => 'DONDURMALAR', 'name' => 'Limonlu dondurma'],
    ];

    $inserted = false;
    $productStmt = $pdo->prepare('SELECT id FROM products WHERE name = :name AND category_id = :cat LIMIT 1');
    $insertProduct = $pdo->prepare('INSERT INTO products (category_id, name, price) VALUES (:cat, :name, 0)');

    foreach ($products as $p) {
        $catId = $categoryIds[$p['cat']] ?? null;
        if (!$catId) {
            continue;
        }
        $productStmt->execute([':name' => $p['name'], ':cat' => $catId]);
        if ($productStmt->fetchColumn()) {
            continue;
        }
        $insertProduct->execute([':cat' => $catId, ':name' => $p['name']]);
        $inserted = true;
    }

    if ($inserted) {
        bump_menu_version($pdo);
    }
}

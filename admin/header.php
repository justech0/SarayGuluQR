<?php
require_once __DIR__ . '/functions.php';
require_login();
?>
<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saray Gülü | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              saray: {
                gold: '#D4AF37',
                darkGold: '#B8860B',
                black: '#111111',
                surface: '#1C1C1C',
                text: '#F5F5DC',
                muted: '#A8A29E'
              }
            },
            fontFamily: {
              serif: ['"Cinzel"', 'serif'],
              sans: ['"Lato"', 'sans-serif']
            },
            backgroundImage: {
              'noise': "url('https://www.transparenttextures.com/patterns/stardust.png')"
            }
          }
        }
      }
    </script>
    <style>
        body { background-color: #0d0d0d; color: #F5F5DC; }
        .glass { background: rgba(17, 17, 17, 0.7); border: 1px solid rgba(212, 175, 55, 0.12); backdrop-filter: blur(12px); }
        .nav-link { display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-radius:0.75rem; transition:all 0.2s ease; }
    </style>
</head>
<body class="min-h-screen bg-saray-black">
<?php $current = basename($_SERVER['PHP_SELF']); ?>
<?php $navItems = [
    ['href' => 'index.php', 'label' => 'Dashboard'],
    ['href' => 'categories.php', 'label' => 'Kategoriler'],
    ['href' => 'products.php', 'label' => 'Ürünler'],
    ['href' => 'branches.php', 'label' => 'Şubeler & WiFi'],
    ['href' => 'feedbacks.php', 'label' => 'Geri Bildirimler'],
]; ?>
<div class="min-h-screen flex bg-noise">
    <aside class="w-72 hidden lg:flex flex-col border-r border-saray-gold/20 bg-black/60">
        <div class="px-6 py-8 border-b border-saray-gold/10">
            <div class="flex flex-col items-center gap-3">
                <div class="w-16 h-16 rounded-full border border-saray-gold/40 overflow-hidden bg-black/60">
                    <img src="assets/logo.svg" alt="Saray Gülü" class="w-full h-full object-cover">
                </div>
                <div class="text-center">
                    <div class="text-saray-gold font-serif text-xl tracking-[0.25em]">SARAY GÜLÜ</div>
                    <div class="text-xs uppercase text-saray-muted tracking-[0.4em]">Admin Paneli</div>
                </div>
            </div>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 text-sm">
            <?php foreach ($navItems as $item): ?>
                <a href="<?php echo $item['href']; ?>" class="nav-link <?php echo $current===$item['href'] ? 'bg-saray-gold/10 text-saray-gold border border-saray-gold/30' : 'hover:bg-white/5 text-saray-text'; ?>"><?php echo $item['label']; ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="px-4 py-6 border-t border-saray-gold/10">
            <a href="logout.php" class="block w-full text-center py-3 rounded-lg bg-saray-gold text-saray-black font-semibold hover:bg-saray-darkGold transition">Çıkış Yap</a>
        </div>
    </aside>
    <div class="flex-1 flex flex-col">
        <header class="flex items-center justify-between px-6 py-4 border-b border-saray-gold/10 bg-black/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full border border-saray-gold/40 overflow-hidden bg-black/60">
                    <img src="assets/logo.svg" alt="Saray Gülü" class="w-full h-full object-cover">
                </div>
                <div>
                    <h1 class="font-serif text-lg text-saray-gold tracking-[0.15em]">Saray Gülü</h1>
                    <p class="text-[11px] text-saray-muted">Premium Yönetim Paneli</p>
                </div>
            </div>
            <div class="hidden lg:flex items-center gap-3">
                <div class="px-4 py-2 rounded-full bg-saray-gold/10 text-saray-gold text-xs">Güvenli Oturum</div>
            </div>
            <button id="mobileMenuButton" class="lg:hidden text-saray-gold border border-saray-gold/30 rounded-lg px-3 py-2 flex flex-col gap-1">
                <span class="block w-6 h-0.5 bg-saray-gold"></span>
                <span class="block w-6 h-0.5 bg-saray-gold"></span>
                <span class="block w-6 h-0.5 bg-saray-gold"></span>
            </button>
        </header>

        <div id="mobileMenu" class="lg:hidden fixed inset-0 z-30 bg-black/70 backdrop-blur-sm hidden">
            <div class="absolute top-0 right-0 w-64 h-full bg-saray-black border-l border-saray-gold/20 p-6 flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full border border-saray-gold/40 overflow-hidden bg-black/60">
                            <img src="assets/logo.svg" alt="Saray Gülü" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <div class="text-saray-gold font-serif text-sm tracking-[0.2em]">Saray Gülü</div>
                            <div class="text-[10px] text-saray-muted uppercase tracking-[0.3em]">Admin</div>
                        </div>
                    </div>
                    <button id="mobileMenuClose" class="text-saray-muted hover:text-saray-gold">×</button>
                </div>
                <nav class="flex-1 space-y-2 text-sm">
                    <?php foreach ($navItems as $item): ?>
                        <a href="<?php echo $item['href']; ?>" class="block px-4 py-3 rounded-lg <?php echo $current===$item['href'] ? 'bg-saray-gold/10 text-saray-gold border border-saray-gold/30' : 'hover:bg-white/5 text-saray-text'; ?>"><?php echo $item['label']; ?></a>
                    <?php endforeach; ?>
                </nav>
                <a href="logout.php" class="mt-6 block w-full text-center py-3 rounded-lg bg-saray-gold text-saray-black font-semibold hover:bg-saray-darkGold transition">Çıkış Yap</a>
            </div>
        </div>

        <main class="p-6">
            <?php if($flash = get_flash()): ?>
                <div class="mb-4 px-4 py-3 rounded-lg border <?php echo $flash['type']==='success' ? 'border-green-500/50 bg-green-500/10 text-green-200' : 'border-red-500/50 bg-red-500/10 text-red-100'; ?>">
                    <?php echo sanitize($flash['message']); ?>
                </div>
            <?php endif; ?>

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
    </style>
</head>
<body class="min-h-screen bg-saray-black">
<div class="min-h-screen flex bg-noise">
    <aside class="w-72 hidden lg:flex flex-col border-r border-saray-gold/20 bg-black/60">
        <div class="px-6 py-8 border-b border-saray-gold/10">
            <div class="text-center">
                <div class="text-saray-gold font-serif text-2xl tracking-[0.3em]">SARAY</div>
                <div class="text-xs uppercase text-saray-muted tracking-[0.4em]">Gülü Admin</div>
            </div>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 text-sm">
            <?php $current = basename($_SERVER['PHP_SELF']); ?>
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $current==='index.php' ? 'bg-saray-gold/10 text-saray-gold border border-saray-gold/30' : 'hover:bg-white/5 text-saray-text'; ?>">Dashboard</a>
            <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $current==='categories.php' ? 'bg-saray-gold/10 text-saray-gold border border-saray-gold/30' : 'hover:bg-white/5 text-saray-text'; ?>">Kategoriler</a>
            <a href="products.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $current==='products.php' ? 'bg-saray-gold/10 text-saray-gold border border-saray-gold/30' : 'hover:bg-white/5 text-saray-text'; ?>">Ürünler</a>
            <a href="branches.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $current==='branches.php' ? 'bg-saray-gold/10 text-saray-gold border border-saray-gold/30' : 'hover:bg-white/5 text-saray-text'; ?>">Şubeler & WiFi</a>
            <a href="feedbacks.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $current==='feedbacks.php' ? 'bg-saray-gold/10 text-saray-gold border border-saray-gold/30' : 'hover:bg-white/5 text-saray-text'; ?>">Geri Bildirimler</a>
        </nav>
        <div class="px-4 py-6 border-t border-saray-gold/10">
            <a href="logout.php" class="block w-full text-center py-3 rounded-lg bg-saray-gold text-saray-black font-semibold hover:bg-saray-darkGold transition">Çıkış Yap</a>
        </div>
    </aside>
    <div class="flex-1 flex flex-col">
        <header class="flex items-center justify-between px-6 py-4 border-b border-saray-gold/10 bg-black/50 backdrop-blur-sm">
            <div>
                <h1 class="font-serif text-xl text-saray-gold tracking-[0.2em]">Saray Gülü</h1>
                <p class="text-xs text-saray-muted">Premium Yönetim Paneli</p>
            </div>
            <div class="hidden lg:flex items-center gap-3">
                <div class="px-4 py-2 rounded-full bg-saray-gold/10 text-saray-gold text-xs">Güvenli Oturum</div>
            </div>
        </header>
        <main class="p-6">
            <?php if($flash = get_flash()): ?>
                <div class="mb-4 px-4 py-3 rounded-lg border <?php echo $flash['type']==='success' ? 'border-green-500/50 bg-green-500/10 text-green-200' : 'border-red-500/50 bg-red-500/10 text-red-100'; ?>">
                    <?php echo sanitize($flash['message']); ?>
                </div>
            <?php endif; ?>

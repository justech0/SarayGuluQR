<?php
require_once __DIR__ . '/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$mode = $_POST['mode'] ?? ($_GET['mode'] ?? 'login');
$flash = get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Geçersiz oturum isteği. Lütfen tekrar deneyin.';
    } elseif ($mode === 'reset') {
        $username = trim($_POST['username'] ?? '');
        $phrase = trim($_POST['reset_phrase'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($username === '' || $phrase === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'Lütfen tüm alanları doldurun.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Yeni şifre ve doğrulama eşleşmiyor.';
        } elseif (!verify_reset_phrase($phrase)) {
            $error = 'Güvenlik metni hatalı. Lütfen size verilen metni aynen girin.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = :username LIMIT 1');
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            if (!$admin) {
                $error = 'Bu kullanıcı adına ait yönetici bulunamadı.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $update = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
                $update->execute([':hash' => $hash, ':id' => $admin['id']]);
                flash_message('success', 'Şifre yenilendi. Yeni şifrenizle giriş yapabilirsiniz.');
                header('Location: login.php');
                exit;
            }
        }
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saray Gülü | Admin Girişi</title>
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-saray-black relative overflow-hidden">
    <div class="absolute inset-0 bg-noise opacity-20"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black via-[#1a1500] to-black opacity-80"></div>

    <div class="relative z-10 w-full max-w-md p-8 glass rounded-2xl shadow-2xl border border-saray-gold/30 bg-black/60">
        <div class="text-center mb-8 flex flex-col items-center gap-4">
            <img src="<?php echo htmlspecialchars($LOGO_URL); ?>" alt="Saray Gülü" class="h-14 w-auto object-contain">
            <h1 class="font-serif text-2xl text-saray-gold tracking-[0.22em]">Admin Panel</h1>
            <p class="text-sm text-saray-muted">Güvenli giriş yaparak kontrol sağlayın</p>
        </div>

        <?php if ($flash): ?>
            <div class="mb-4 px-4 py-3 rounded-lg border border-green-500/40 bg-green-500/10 text-green-100 text-sm"><?php echo sanitize($flash['message']); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-4 px-4 py-3 rounded-lg border border-red-500/40 bg-red-500/10 text-red-100 text-sm"><?php echo sanitize($error); ?></div>
        <?php endif; ?>

        <?php if ($mode === 'reset'): ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="mode" value="reset">
                <div class="grid gap-3">
                    <div>
                        <label class="block text-sm text-saray-muted mb-2">Kullanıcı Adı</label>
                        <input type="text" name="username" required class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-4 py-3 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none" placeholder="admin" value="<?php echo isset($_POST['username']) ? sanitize($_POST['username']) : ''; ?>">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm text-saray-muted">Güvenlik Metni</label>
                            <span class="text-[11px] text-saray-gold/80"><?php echo sanitize($RESET_CHALLENGE_PROMPT); ?></span>
                        </div>
                        <textarea name="reset_phrase" rows="2" required class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-4 py-3 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none" placeholder="Size verilen metni aynen yazın..."><?php echo isset($_POST['reset_phrase']) ? sanitize($_POST['reset_phrase']) : ''; ?></textarea>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm text-saray-muted mb-2">Yeni Şifre</label>
                            <input type="password" name="new_password" required class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-4 py-3 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none" placeholder="Yeni şifre">
                        </div>
                        <div>
                            <label class="block text-sm text-saray-muted mb-2">Yeni Şifre (Tekrar)</label>
                            <input type="password" name="confirm_password" required class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-4 py-3 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none" placeholder="Tekrar">
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <button type="submit" class="w-full py-3 bg-saray-gold text-saray-black font-semibold rounded-lg hover:bg-saray-darkGold transition">Şifreyi Güncelle</button>
                    <a href="login.php" class="block text-center text-sm text-saray-muted hover:text-saray-gold transition">Girişe geri dön</a>
                </div>
            </form>
        <?php else: ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo sanitize($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="mode" value="login">
                <div>
                    <label class="block text-sm text-saray-muted mb-2">Kullanıcı Adı</label>
                    <input type="text" name="username" required class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-4 py-3 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none" placeholder="admin">
                </div>
                <div>
                    <label class="block text-sm text-saray-muted mb-2">Şifre</label>
                    <input type="password" name="password" required class="w-full bg-white/5 border border-saray-gold/20 rounded-lg px-4 py-3 text-sm focus:border-saray-gold focus:ring-1 focus:ring-saray-gold outline-none" placeholder="••••••••">
                </div>
                <div class="space-y-3">
                    <button type="submit" class="w-full py-3 bg-saray-gold text-saray-black font-semibold rounded-lg hover:bg-saray-darkGold transition">Giriş Yap</button>
                    <a href="?mode=reset" class="block text-center text-sm text-saray-muted hover:text-saray-gold transition">Şifremi unuttum</a>
                </div>
            </form>
        <?php endif; ?>
        <p class="text-[10px] text-center text-saray-muted mt-6 tracking-[0.2em]">Saray Gülü | Premium Yönetim</p>
    </div>
</body>
</html>

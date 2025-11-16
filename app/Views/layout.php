<?php
// app/Views/layout.php
// Main layout used by your views. Expects $title and $content to be set.

if (!function_exists('url')) {
    /**
     * Build an application URL that respects APP_BASE.
     * Always returns a path that starts with a single slash.
     */
    function url(string $path = ''): string {
        $base = defined('APP_BASE') ? APP_BASE : '';
        $parts = explode('?', $path, 2);
        $p = '/' . ltrim($parts[0] ?? '', '/');
        $q = isset($parts[1]) ? ('?' . $parts[1]) : '';

        if ($p === '/') $p = '/';

        if ($base === '' || $base === '/') {
            return ($p === '/') ? '/' . ltrim($q, '?') : rtrim($p, '/') . $q;
        }

        $joined = rtrim($base, '/') . ($p === '/' ? '' : $p);
        return $joined . $q;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title ? ($title . ' | SayoNet') : 'SayoNet') ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="<?= htmlspecialchars(url('/assets/style.css')) ?>">
    <style>
        
        body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background:#f4f6f8; color:#222; margin:0; }
        .container { max-width:1000px; margin:28px auto; padding:0 20px; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
        .logo { font-weight:800; font-size:20px; color:#0f172a; text-decoration:none; }
        nav a { color:#2563eb; text-decoration:none; margin-left:12px; }
        .message { padding:12px; border-radius:8px; margin-bottom:12px; }
        .message.success { background:#d1fae5; color:#065f46; }
        .message.error { background:#fee2e2; color:#991b1b; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <div>
            <a class="logo" href="<?= htmlspecialchars(url('/')) ?>">SayoNet</a>
            
        </div>

        <div>
            <?php if (!empty($_SESSION['user'])): ?>
                <nav aria-label="Main navigation">
                    <a href="<?= htmlspecialchars(url('/feed')) ?>">Feed</a>
                    <a href="<?= htmlspecialchars(url('/dashboard')) ?>">Dashboard</a>
                    <a href="<?= htmlspecialchars(url('/logout')) ?>">Logout</a>
                </nav>
            <?php else: ?>
                <nav aria-label="Auth navigation">
                    <a href="<?= htmlspecialchars(url('/login')) ?>">Login</a>
                    <a href="<?= htmlspecialchars(url('/register')) ?>">Register</a>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <?php
            // Flash messages (Session class or $_SESSION fallback)
            if (class_exists('\\App\\Core\\Session')) {
                if (\App\Core\Session::get('success')): ?>
                    <div class="message success"><?= htmlspecialchars(\App\Core\Session::get('success')) ?></div>
                    <?php \App\Core\Session::remove('success'); ?>
                <?php endif;
                if (\App\Core\Session::get('error')): ?>
                    <div class="message error"><?= htmlspecialchars(\App\Core\Session::get('error')) ?></div>
                    <?php \App\Core\Session::remove('error'); ?>
                <?php endif;
            } else {
                if (!empty($_SESSION['success'])): ?>
                    <div class="message success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif;
                if (!empty($_SESSION['error'])): ?>
                    <div class="message error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif;
            }
        ?>

        <?php echo $content ?? '' ; ?>
    </main>

    <footer style="margin-top:20px; font-size:12px; color:#666;">
        <small>SayoNet — teaching project</small>
    </footer>
</div>
</body>
</html>

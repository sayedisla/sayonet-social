<?php
// app/Views/auth/login.php
use App\Core\Session;

$title = 'Login';
ob_start();

$base = defined('APP_BASE') ? APP_BASE : '';
if ($base === '/' || $base === '\\') $base = '';
function base_url(string $path = ''): string {
    $base = defined('APP_BASE') ? APP_BASE : '';
    $p = '/' . ltrim($path, '/');
    if ($base === '' || $base === '/') return ($p === '/') ? '/' : rtrim($p, '/');
    return rtrim($base, '/') . ($p === '/' ? '' : $p);
}

$flashSuccess = Session::get('success');
$flashError = Session::get('error');
?>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">SayoNet</div>
        <p class="auth-subtitle">A friendly social demo</p>

        <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
            <?php Session::remove('success'); ?>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
            <?php Session::remove('error'); ?>
        <?php endif; ?>

        <form method="POST" action="<?= htmlspecialchars(base_url('/login')) ?>" class="form-auth" autocomplete="off" novalidate>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input id="email" class="form-control" type="email" name="email" required placeholder="you@example.com" />
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input id="password" class="form-control" type="password" name="password" required placeholder="••••••••" />
            </div>

            <div style="display:flex; gap:10px; align-items:center; justify-content:space-between;">
                <button type="submit" class="btn btn-primary">Login</button>
                <a class="btn btn-outline" href="<?= htmlspecialchars(base_url('/register')) ?>">Register</a>
            </div>
        </form>

        <p style="margin-top:16px; color:#6b7280; font-size:13px;">
            By using this demo you accept this is just a learning project.
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';

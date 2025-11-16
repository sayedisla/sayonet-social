<?php
// app/Views/auth/register.php
use App\Core\Session;

$title = 'Register';
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
        <p class="auth-subtitle">Create your account — join the demo network</p>

        <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
            <?php Session::remove('success'); ?>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
            <?php Session::remove('error'); ?>
        <?php endif; ?>

        <form method="POST" action="<?= htmlspecialchars(base_url('/register')) ?>" class="form-auth" autocomplete="off" novalidate>
            <div class="form-group">
                <label class="form-label" for="name">Full name</label>
                <input id="name" class="form-control" type="text" name="name" required placeholder="Your name" />
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input id="email" class="form-control" type="email" name="email" required placeholder="you@example.com" />
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input id="password" class="form-control" type="password" name="password" required placeholder="At least 6 characters" />
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirm">Confirm password</label>
                <input id="password_confirm" class="form-control" type="password" name="password_confirm" required placeholder="Retype your password" />
            </div>

            <div style="display:flex; gap:10px; align-items:center; justify-content:space-between;">
                <button type="submit" class="btn btn-primary">Register</button>
                <a class="btn btn-outline" href="<?= htmlspecialchars(base_url('/login')) ?>">Have an account? Login</a>
            </div>
        </form>

        <p style="margin-top:16px; color:#6b7280; font-size:13px;">
            Your data is for demo purposes only.
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';

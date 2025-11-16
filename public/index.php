<?php
use App\Core\Session;

$title = 'Feed | AuthBoard';
ob_start();

$user = $user ?? null;
$posts = $posts ?? [];
$filter = $filter ?? '';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h2>Hi, <?= htmlspecialchars($user['name'] ?? 'Guest') ?></h2>

    <div style="display:flex; gap:8px; align-items:center;">
        <a href="<?= htmlspecialchars(url('/feed')) ?>" class="btn btn-outline" <?php if($filter===''){ echo 'style="font-weight:700"'; } ?>>All</a>
        <a href="<?= htmlspecialchars(url('/feed?filter=following')) ?>" class="btn btn-outline" <?php if($filter==='following'){ echo 'style="font-weight:700"'; } ?>>Following</a>
    </div>
</div>

<?php if (Session::get('success')): ?>
    <div class="message success"><?= htmlspecialchars(Session::get('success')); Session::remove('success'); ?></div>
<?php endif; ?>

<?php if (Session::get('error')): ?>
    <div class="message error"><?= htmlspecialchars(Session::get('error')); Session::remove('error'); ?></div>
<?php endif; ?>

<!-- Composer -->
<form action="<?= htmlspecialchars(url('/posts/store')) ?>" method="post" enctype="multipart/form-data" style="margin-bottom:16px;">
    <textarea name="content" rows="3" style="width:100%; padding:8px;" placeholder="What's happening?"></textarea>
    <div style="display:flex; gap:8px; margin-top:8px; align-items:center;">
        <input type="file" name="image" accept="image/*">
        <button type="submit" class="btn btn-primary">Post</button>
    </div>
</form>

<div id="posts">
<?php foreach($posts as $post):
    include __DIR__ . '/_post.php';
endforeach; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';

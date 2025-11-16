<?php
// app/Views/posts/_post.php
// expects $post and $user in scope

use App\Models\Like;
use App\Models\Comment;
use App\Models\Follow;

$liked = Like::hasUserLiked((int)$post['id'], (int)($user['id'] ?? 0));
$comments = Comment::findByPost((int)$post['id']);

// compute base path (safe)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = rtrim(dirname($scriptName), '/\\');
if ($scriptDir === '/' || $scriptDir === '\\') $scriptDir = '';
$scriptDir = preg_replace('#/index\.php$#', '', $scriptDir);
$basePath = $scriptDir;
$bp = function(string $path) use ($basePath) : string {
    if ($basePath === '') return $path;
    return rtrim($basePath, '/') . '/' . ltrim($path, '/');
};

// follow status
$isFollowing = false;
if (!empty($user) && (int)$user['id'] !== (int)$post['user_id']) {
    $isFollowing = Follow::isFollowing((int)$user['id'], (int)$post['user_id']);
}

// resolve image
$imageWebPath = !empty($post['image']) ? $post['image'] : null;
$imageExists = false;
$resolvedImageUrl = null;
if ($imageWebPath) {
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
    $candidates = [];
    if (strpos($imageWebPath, '/') === 0) {
        $candidates[] = $docRoot . $imageWebPath;
        $candidates[] = $docRoot . '/public' . $imageWebPath;
    } else {
        $candidates[] = $docRoot . '/public/' . ltrim($imageWebPath, '/');
        $candidates[] = $docRoot . '/' . ltrim($imageWebPath, '/');
    }
    foreach ($candidates as $p) {
        if ($p && file_exists($p) && is_file($p)) { $imageExists = true; break; }
    }
    if ($imageExists) {
        if (strpos($imageWebPath, '/') === 0) {
            $resolvedImageUrl = $imageWebPath;
        } else {
            $resolvedImageUrl = ($basePath ? rtrim($basePath, '/') . '/public/' : '/public/') . ltrim($imageWebPath, '/');
        }
    } else {
        $resolvedImageUrl = ($basePath ? rtrim($basePath, '/') . '/public/' : '/public/') . ltrim($imageWebPath, '/');
    }
}
?>
<article class="post-card" id="post-<?php echo (int)$post['id']; ?>">
  <div class="post-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div style="display:flex; align-items:center; gap:12px;">
      <div class="post-avatar"><?php echo htmlspecialchars(strtoupper(substr($post['author_name'] ?? 'U', 0, 1))); ?></div>
      <div>
        <div class="post-user"><?php echo htmlspecialchars($post['author_name'] ?? 'User'); ?></div>
        <div class="post-time" style="color:#666; font-size:13px;"><?php echo htmlspecialchars($post['created_at']); ?></div>
      </div>
    </div>

    <div style="display:flex; gap:8px; align-items:center;">
      <!-- Follow/Unfollow (only show when viewer is not the author) -->
      <?php if (!empty($user) && (int)$user['id'] !== (int)$post['user_id']): ?>
        <?php if ($isFollowing): ?>
          <form action="<?php echo htmlspecialchars(($bp)('/unfollow')); ?>" method="post" style="display:inline;">
            <input type="hidden" name="target_user_id" value="<?php echo (int)$post['user_id']; ?>">
            <button type="submit" class="btn btn-outline">Unfollow</button>
          </form>
        <?php else: ?>
          <form action="<?php echo htmlspecialchars(($bp)('/follow')); ?>" method="post" style="display:inline;">
            <input type="hidden" name="target_user_id" value="<?php echo (int)$post['user_id']; ?>">
            <button type="submit" class="btn btn-primary">Follow</button>
          </form>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Delete for owner -->
      <?php if (!empty($user) && (int)$user['id'] === (int)$post['user_id']): ?>
        <form action="<?php echo htmlspecialchars(($bp)('/posts/delete')); ?>" method="post" onsubmit="return confirm('Delete this post?');" style="display:inline;">
            <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($post['content'])): ?>
    <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
  <?php endif; ?>

  <?php if (!empty($resolvedImageUrl)): ?>
    <div class="post-image" style="margin-top:10px;">
      <img src="<?php echo htmlspecialchars($resolvedImageUrl); ?>" alt="Post image" style="max-width:100%; border-radius:10px;">
    </div>
  <?php endif; ?>

  <div class="post-stats" style="display:flex; gap:16px; margin-top:10px; color:#555;">
    <div class="post-stat"><strong class="like-count"><?php echo (int)$post['like_count']; ?></strong> Likes</div>
    <div class="post-stat"><strong class="comment-count"><?php echo (int)$post['comment_count']; ?></strong> Comments</div>
    <div class="post-stat"><strong class="share-count"><?php echo (int)$post['share_count']; ?></strong> Shares</div>
  </div>

  <div class="post-actions" style="display:flex; gap:8px; margin-top:12px;">
    <form action="<?php echo htmlspecialchars(($bp)('/posts/like')); ?>" method="post" style="display:inline;">
      <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
      <button type="submit" class="btn btn-outline"><?php echo $liked ? '❤ Liked' : '♡ Like'; ?></button>
    </form>

    <form action="#comments-<?php echo (int)$post['id']; ?>" style="display:inline;">
      <button type="submit" class="btn btn-outline">💬 Comment</button>
    </form>

    <form action="<?php echo htmlspecialchars(($bp)('/posts/share')); ?>" method="post" style="display:inline;">
      <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
      <button type="submit" class="btn btn-outline">🔁 Share</button>
    </form>
  </div>

  <div class="comments-section" id="comments-<?php echo (int)$post['id']; ?>" style="margin-top:12px;">
    <?php foreach ($comments as $c): ?>
      <div class="comment" style="padding:8px 0; border-bottom:1px solid #eee;">
        <strong><?php echo htmlspecialchars($c['name'] ?? 'User'); ?></strong>
        <div style="margin-top:4px;"><?php echo nl2br(htmlspecialchars($c['content'])); ?></div>
      </div>
    <?php endforeach; ?>

    <?php if (!empty($user)): ?>
      <form class="comment-form" action="<?php echo htmlspecialchars(($bp)('/posts/comment')); ?>" method="post" style="display:flex; gap:8px; margin-top:12px;">
        <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
        <input class="comment-input form-control" type="text" name="content" placeholder="Write a comment..." autocomplete="off" required>
        <button class="btn btn-primary" type="submit">Send</button>
      </form>
    <?php endif; ?>
  </div>
</article>

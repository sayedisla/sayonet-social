<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Share;
use App\Models\Follow;

class PostController extends Controller
{
    /**
     * Show feed (composer + posts)
     * Accepts optional ?filter=following to limit to followed users' posts.
     */
    public function index()
    {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $filter = trim($_GET['filter'] ?? '');

        if ($filter === 'following') {
            $followingIds = Follow::getFollowingIds((int)$user['id']);
            // If none followed, show empty feed
            if (empty($followingIds)) {
                $posts = [];
            } else {
                $posts = Post::findAllWithCountsByUserIds($followingIds, 50);
            }
        } else {
            // default: all posts
            $posts = Post::findAllWithCounts(50);
        }

        $this->view('posts/index.php', [
            'user' => $user,
            'posts' => $posts,
            'filter' => $filter
        ]);
    }

    /**
     * Store a new post (text + optional image)
     */
    public function store()
    {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $content = trim($_POST['content'] ?? '');

        // require content or image
        if ($content === '' && empty($_FILES['image']['name'])) {
            Session::set('error', 'Post cannot be empty.');
            header('Location: /feed');
            exit;
        }

        $imagePath = null;

        if (!empty($_FILES['image']['name'])) {
            $file = $_FILES['image'];

            // basic upload error check
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Session::set('error', 'Upload error: ' . (int)$file['error']);
                header('Location: /feed');
                exit;
            }

            // validate mime type via finfo
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $allowed = [
                'image/png'  => 'png',
                'image/jpeg' => 'jpg',
                'image/jpg'  => 'jpg',
                'image/gif'  => 'gif',
                'image/webp' => 'webp'
            ];

            if (!isset($allowed[$mime])) {
                Session::set('error', 'Invalid image type. Allowed: png, jpg, gif, webp.');
                header('Location: /feed');
                exit;
            }

            $ext = $allowed[$mime];
            $filename = bin2hex(random_bytes(12)) . '.' . $ext;

            // physical path (where file will be written)
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                Session::set('error', 'Failed to create uploads directory.');
                header('Location: /feed');
                exit;
            }

            $dest = $uploadDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                Session::set('error', 'Failed to move uploaded file.');
                header('Location: /feed');
                exit;
            }

            // Build a web-accessible URL dynamically.
            $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
            if ($scriptDir === '/' || $scriptDir === '\\') $scriptDir = '';
            $imagePath = $scriptDir . '/public/uploads/' . $filename;
        }

        Post::create((int)$user['id'], $content, $imagePath);

        Session::set('success', 'Post published.');
        header('Location: /feed');
        exit;
    }

    /**
     * Like (toggle) a post. Non-AJAX fallback: redirect back to feed.
     */
    public function like()
    {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        if (!$postId) {
            Session::set('error', 'Invalid like request.');
            header('Location: /feed');
            exit;
        }

        // toggle like
        if (Like::hasUserLiked($postId, $user['id'])) {
            Like::remove($postId, $user['id']);
        } else {
            Like::add($postId, $user['id']);
        }

        header('Location: /feed');
        exit;
    }

    /**
     * Comment (non-AJAX)
     */
    public function comment()
    {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if (!$postId || $content === '') {
            Session::set('error', 'Invalid comment.');
            header('Location: /feed');
            exit;
        }

        Comment::create($postId, $user['id'], $content);

        header('Location: /feed');
        exit;
    }

    /**
     * Share (non-AJAX)
     */
    public function share()
    {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        if (!$postId) {
            Session::set('error', 'Invalid share request.');
            header('Location: /feed');
            exit;
        }

        Share::create($postId, $user['id']);

        header('Location: /feed');
        exit;
    }

    /**
     * Follow a user
     * Expects POST 'target_user_id'
     */
    public function follow()
    {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $target = (int)($_POST['target_user_id'] ?? 0);
        if (!$target) {
            Session::set('error', 'Invalid target user.');
            header('Location: /feed');
            exit;
        }

        Follow::add((int)$user['id'], $target);
        header('Location: /feed');
        exit;
    }

    /**
     * Unfollow a user
     * Expects POST 'target_user_id'
     */
    public function unfollow()
    {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $target = (int)($_POST['target_user_id'] ?? 0);
        if (!$target) {
            Session::set('error', 'Invalid target user.');
            header('Location: /feed');
            exit;
        }

        Follow::remove((int)$user['id'], $target);
        header('Location: /feed');
        exit;
    }

    /**
     * Delete (owner-only)
     */
    public function delete()
    {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        if (!$postId) {
            Session::set('error', 'Invalid request.');
            header('Location: /feed');
            exit;
        }

        $post = Post::findById($postId);
        if (!$post) {
            Session::set('error', 'Post not found.');
            header('Location: /feed');
            exit;
        }

        if ($post['user_id'] != $user['id']) {
            Session::set('error', 'You can only delete your own posts.');
            header('Location: /feed');
            exit;
        }

        $deleted = Post::delete($postId);
        if ($deleted) {
            if (!empty($post['image'])) {
                $p = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($post['image'], '/');
                if (file_exists($p)) {
                    @unlink($p);
                }
            }
            Session::set('success', 'Post deleted.');
        } else {
            Session::set('error', 'Failed to delete post.');
        }

        header('Location: /feed');
        exit;
    }
}

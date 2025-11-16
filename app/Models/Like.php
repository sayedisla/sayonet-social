<?php
namespace App\Models;

use PDO;

class Like
{
    private static function connect(): PDO {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $db   = getenv('DB_NAME') ?: 'authboard';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }

    public static function hasUserLiked(int $postId, int $userId): bool {
        $stmt = self::connect()->prepare('SELECT 1 FROM likes WHERE post_id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$postId, $userId]);
        return (bool)$stmt->fetchColumn();
    }

    public static function add(int $postId, int $userId): int {
        $pdo = self::connect();
        $stmt = $pdo->prepare('INSERT IGNORE INTO likes (post_id, user_id) VALUES (?, ?)');
        $stmt->execute([$postId, $userId]);
        return (int)$pdo->lastInsertId();
    }

    public static function remove(int $postId, int $userId): void {
        $stmt = self::connect()->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?');
        $stmt->execute([$postId, $userId]);
    }

    public static function countForPost(int $postId): int {
        $stmt = self::connect()->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ?');
        $stmt->execute([$postId]);
        return (int)$stmt->fetchColumn();
    }
}

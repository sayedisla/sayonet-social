<?php
namespace App\Models;

use PDO;

class Share
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

    public static function create(int $postId, int $userId): int {
        $pdo = self::connect();
        $stmt = $pdo->prepare('INSERT INTO shares (post_id, user_id) VALUES (?, ?)');
        $stmt->execute([$postId, $userId]);
        return (int)$pdo->lastInsertId();
    }

    public static function countForPost(int $postId): int {
        $stmt = self::connect()->prepare('SELECT COUNT(*) FROM shares WHERE post_id = ?');
        $stmt->execute([$postId]);
        return (int)$stmt->fetchColumn();
    }
}

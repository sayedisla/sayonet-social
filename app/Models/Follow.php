<?php
namespace App\Models;

use PDO;

class Follow
{
    private static function connect(): PDO
    {
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

    /**
     * Create a follow relationship (follower -> following).
     * Uses INSERT IGNORE to avoid duplicates.
     */
    public static function add(int $followerId, int $followingId): bool
    {
        if ($followerId === $followingId) return false;
        $pdo = self::connect();
        $stmt = $pdo->prepare('INSERT IGNORE INTO follows (follower_id, following_id) VALUES (?, ?)');
        $stmt->execute([$followerId, $followingId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Remove follow relationship.
     */
    public static function remove(int $followerId, int $followingId): bool
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare('DELETE FROM follows WHERE follower_id = ? AND following_id = ?');
        $stmt->execute([$followerId, $followingId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Is $followerId following $followingId ?
     */
    public static function isFollowing(int $followerId, int $followingId): bool
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare('SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ? LIMIT 1');
        $stmt->execute([$followerId, $followingId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Return array of user IDs that $followerId follows.
     * Includes $followerId itself if you want to include self-posts (we won't by default).
     *
     * @return int[]
     */
    public static function getFollowingIds(int $followerId): array
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare('SELECT following_id FROM follows WHERE follower_id = ?');
        $stmt->execute([$followerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_map('intval', $rows ?: []);
    }

    /**
     * Counts for profile pages
     */
    public static function countFollowers(int $userId): int
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM follows WHERE following_id = ?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function countFollowing(int $userId): int
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM follows WHERE follower_id = ?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}

<?php
namespace App\Models;

use PDO;

class Post
{
    /**
     * Create and return a PDO connection
     *
     * @return PDO
     */
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
     * Insert a new post (with optional image path)
     *
     * @param int $userId
     * @param string $content
     * @param string|null $image Web-accessible path (e.g. '/uploads/abcd.jpg') or null
     * @return int Inserted post ID
     */
    public static function create(int $userId, string $content, ?string $image = null): int
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare('INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $content, $image]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Fetch posts with author info and aggregated counts (likes, comments, shares).
     * Uses sanitized interpolation for LIMIT to avoid PDO/MySQL issues with bound LIMIT params.
     *
     * @param int $limit
     * @return array
     */
    public static function findAllWithCounts(int $limit = 50): array
    {
        $pdo = self::connect();

        $limitInt = (int)$limit;
        if ($limitInt <= 0) {
            $limitInt = 50;
        }

        $sql = "
            SELECT
                p.*,
                u.name AS author_name,
                u.email AS author_email,
                COALESCE(lc.like_count, 0) AS like_count,
                COALESCE(cc.comment_count, 0) AS comment_count,
                COALESCE(sc.share_count, 0) AS share_count
            FROM posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) AS like_count
                FROM likes
                GROUP BY post_id
            ) lc ON lc.post_id = p.id
            LEFT JOIN (
                SELECT post_id, COUNT(*) AS comment_count
                FROM comments
                GROUP BY post_id
            ) cc ON cc.post_id = p.id
            LEFT JOIN (
                SELECT post_id, COUNT(*) AS share_count
                FROM shares
                GROUP BY post_id
            ) sc ON sc.post_id = p.id
            ORDER BY p.created_at DESC
            LIMIT {$limitInt}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetch posts by a list of user IDs with counts.
     *
     * @param int[] $userIds
     * @param int $limit
     * @return array
     */
    public static function findAllWithCountsByUserIds(array $userIds, int $limit = 50): array
    {
        if (empty($userIds)) return [];

        // Ensure all values are integers
        $userIds = array_map('intval', $userIds);
        $in = implode(',', $userIds);

        $pdo = self::connect();
        $limitInt = (int)$limit;
        if ($limitInt <= 0) $limitInt = 50;

        $sql = "
            SELECT
                p.*,
                u.name AS author_name,
                u.email AS author_email,
                COALESCE(lc.like_count, 0) AS like_count,
                COALESCE(cc.comment_count, 0) AS comment_count,
                COALESCE(sc.share_count, 0) AS share_count
            FROM posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) AS like_count
                FROM likes
                GROUP BY post_id
            ) lc ON lc.post_id = p.id
            LEFT JOIN (
                SELECT post_id, COUNT(*) AS comment_count
                FROM comments
                GROUP BY post_id
            ) cc ON cc.post_id = p.id
            LEFT JOIN (
                SELECT post_id, COUNT(*) AS share_count
                FROM shares
                GROUP BY post_id
            ) sc ON sc.post_id = p.id
            WHERE p.user_id IN ({$in})
            ORDER BY p.created_at DESC
            LIMIT {$limitInt}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a single post by id
     *
     * @param int $id
     * @return array|null
     */
    public static function findById(int $id): ?array
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare('SELECT p.*, u.name AS author_name, u.email AS author_email FROM posts p JOIN users u ON u.id = p.user_id WHERE p.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Delete a post row by ID.
     *
     * @param int $id
     * @return bool true if a row was deleted
     */
    public static function delete(int $id): bool
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}

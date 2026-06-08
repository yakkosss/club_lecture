<?php

require_once __DIR__ . '/../../config/Db.php';

class ProgressDao {

    /**
     * Progression d'un utilisateur pour un livre (null si pas encore enregistrée).
     */
    public static function findByBookAndUser(int $bookId, int $userId): ?array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM progress WHERE book_id = :book_id AND user_id = :user_id"
        );
        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId,  PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Toutes les progressions d'un utilisateur (pour le dashboard).
     */
    public static function getAllByUser(int $userId): array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT p.*, b.title, b.author, b.cover_path
             FROM progress p
             JOIN books b ON p.book_id = b.id
             WHERE p.user_id = :user_id
             ORDER BY p.updated_at DESC"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Progression moyenne sur un livre (pour la fiche livre).
     */
    public static function getAverageByBook(int $bookId): ?float {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT AVG(pourcentage) FROM progress WHERE book_id = :book_id"
        );
        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        $val = $stmt->fetchColumn();
        return $val !== null && $val !== false ? round((float)$val, 1) : null;
    }

    /**
     * Upsert : une seule ligne par (book_id, user_id).
     */
    public static function upsert(int $bookId, int $userId, int $pourcentage): void {
        $pourcentage = max(0, min(100, $pourcentage));
        $pdo         = Db::getConnection();

        $existing = self::findByBookAndUser($bookId, $userId);
        if ($existing) {
            $stmt = $pdo->prepare(
                "UPDATE progress SET pourcentage = :p WHERE book_id = :b AND user_id = :u"
            );
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO progress (book_id, user_id, pourcentage) VALUES (:b, :u, :p)"
            );
        }
        $stmt->bindValue(':b', $bookId,      PDO::PARAM_INT);
        $stmt->bindValue(':u', $userId,       PDO::PARAM_INT);
        $stmt->bindValue(':p', $pourcentage,  PDO::PARAM_INT);
        $stmt->execute();
    }
}

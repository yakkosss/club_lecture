<?php

require_once __DIR__ . '/../../config/Db.php';
require_once __DIR__ . '/Review.php';

class ReviewDao {

    /**
     * Avis visibles d'un livre (non masqués), avec infos auteur.
     */
    public static function getVisibleByBookId(int $bookId): array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT r.*, u.firstname, u.lastname
             FROM reviews r
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.book_id = :book_id AND r.hidden = 0
             ORDER BY r.created_at DESC"
        );
        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tous les avis d'un livre (admin/modérateur).
     */
    public static function getAllByBookId(int $bookId): array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT r.*, u.firstname, u.lastname
             FROM reviews r
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.book_id = :book_id
             ORDER BY r.created_at DESC"
        );
        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Avis d'un utilisateur pour un livre (1 max).
     */
    public static function findByBookAndUser(int $bookId, int $userId): ?array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM reviews WHERE book_id = :book_id AND user_id = :user_id"
        );
        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId,  PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getById(int $id): ?array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Note moyenne visible d'un livre.
     */
    public static function getAverageNote(int $bookId): ?float {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT AVG(note) AS avg_note FROM reviews WHERE book_id = :book_id AND hidden = 0"
        );
        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        $val = $stmt->fetchColumn();
        return $val !== null && $val !== false ? round((float)$val, 1) : null;
    }

    public static function create(Review $review): int {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO reviews (book_id, user_id, note, commentaire, hidden)
             VALUES (:book_id, :user_id, :note, :commentaire, 0)"
        );
        $stmt->bindValue(':book_id',     $review->getBookId(),      PDO::PARAM_INT);
        $stmt->bindValue(':user_id',     $review->getUserId(),      PDO::PARAM_INT);
        $stmt->bindValue(':note',        $review->getNote(),        PDO::PARAM_INT);
        $stmt->bindValue(':commentaire', $review->getCommentaire());
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, int $note, ?string $commentaire): void {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE reviews SET note = :note, commentaire = :commentaire WHERE id = :id"
        );
        $stmt->bindValue(':note',        $note,        PDO::PARAM_INT);
        $stmt->bindValue(':commentaire', $commentaire);
        $stmt->bindValue(':id',          $id,          PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Masque/démasque un avis (modération).
     */
    public static function setHidden(int $id, bool $hidden): void {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare("UPDATE reviews SET hidden = :hidden WHERE id = :id");
        $stmt->bindValue(':hidden', (int) $hidden, PDO::PARAM_INT);
        $stmt->bindValue(':id',     $id,           PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function delete(int $id): void {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

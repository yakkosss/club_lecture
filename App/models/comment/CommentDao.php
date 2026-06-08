<?php

require_once __DIR__ . '/../../config/Db.php';
require_once __DIR__ . '/Comment.php';

class CommentDao {

    /**
     * Commentaires approuvés d'un livre, triés par niveau/parent pour affichage hiérarchique.
     */
    public static function getApprovedByBookId(int $bookId): array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT c.*, u.firstname, u.lastname
             FROM comments c
             LEFT JOIN users u ON c.author = u.id
             WHERE c.book_id = :book_id
               AND c.comment_state = 'APPROVED'
             ORDER BY COALESCE(c.parent, c.id), c.id ASC"
        );
        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tous les commentaires d'un livre (admin/modérateur).
     */
    public static function getAllByBookId(int $bookId): array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT c.*, u.firstname, u.lastname
             FROM comments c
             LEFT JOIN users u ON c.author = u.id
             WHERE c.book_id = :book_id
             ORDER BY COALESCE(c.parent, c.id), c.id ASC"
        );
        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(int $id): ?array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(Comment $c): int {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO comments (book_id, author, comment_text, parent)
             VALUES (:book_id, :author, :comment_text, :parent)"
        );
        $stmt->bindValue(':book_id',      $c->getBookId(), PDO::PARAM_INT);
        $stmt->bindValue(':author',       $c->getAuthor(), PDO::PARAM_INT);
        $stmt->bindValue(':comment_text', $c->getCommentText());
        $stmt->bindValue(':parent',       $c->getParent(),
            $c->getParent() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    public static function updateText(int $id, string $text): void {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare("UPDATE comments SET comment_text = :t WHERE id = :id");
        $stmt->bindValue(':t',  $text);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function updateState(int $id, string $state): void {
        $allowed = ['APPROVED', 'WAITING', 'REJECTED', 'REPORTED'];
        if (!in_array($state, $allowed, true)) return;
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare("UPDATE comments SET comment_state = :s WHERE id = :id");
        $stmt->bindValue(':s',  $state);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function delete(int $id): void {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

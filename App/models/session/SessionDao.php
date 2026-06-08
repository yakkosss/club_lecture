<?php

require_once __DIR__ . '/../../config/Db.php';
require_once __DIR__ . '/Session.php';

class SessionDao {

    public static function getAll(): array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT s.*, b.title AS book_title, u.firstname AS creator_firstname, u.lastname AS creator_lastname
             FROM sessions s
             JOIN books b ON s.book_id = b.id
             LEFT JOIN users u ON s.created_by = u.id
             ORDER BY s.date_heure ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUpcoming(int $limit = 5): array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT s.*, b.title AS book_title
             FROM sessions s
             JOIN books b ON s.book_id = b.id
             WHERE s.date_heure >= NOW()
             ORDER BY s.date_heure ASC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(int $id): ?array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT s.*, b.title AS book_title, u.firstname AS creator_firstname, u.lastname AS creator_lastname
             FROM sessions s
             JOIN books b ON s.book_id = b.id
             LEFT JOIN users u ON s.created_by = u.id
             WHERE s.id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(Session $s): int {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO sessions (book_id, titre, date_heure, lien, lieu, description, created_by)
             VALUES (:book_id, :titre, :date_heure, :lien, :lieu, :description, :created_by)"
        );
        $stmt->bindValue(':book_id',     $s->getBookId(),      PDO::PARAM_INT);
        $stmt->bindValue(':titre',       $s->getTitre());
        $stmt->bindValue(':date_heure',  $s->getDateHeure());
        $stmt->bindValue(':lien',        $s->getLien());
        $stmt->bindValue(':lieu',        $s->getLieu());
        $stmt->bindValue(':description', $s->getDescription());
        $stmt->bindValue(':created_by',  $s->getCreatedBy(),   $s->getCreatedBy() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    public static function delete(int $id): void {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // ---- Inscriptions ----

    /**
     * Inscrit un utilisateur (ignore si déjà inscrit).
     */
    public static function registerAttendance(int $sessionId, int $userId): void {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "INSERT IGNORE INTO session_attendance (session_id, user_id) VALUES (:s, :u)"
        );
        $stmt->bindValue(':s', $sessionId, PDO::PARAM_INT);
        $stmt->bindValue(':u', $userId,    PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function cancelAttendance(int $sessionId, int $userId): void {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "DELETE FROM session_attendance WHERE session_id = :s AND user_id = :u"
        );
        $stmt->bindValue(':s', $sessionId, PDO::PARAM_INT);
        $stmt->bindValue(':u', $userId,    PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function isRegistered(int $sessionId, int $userId): bool {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT 1 FROM session_attendance WHERE session_id = :s AND user_id = :u"
        );
        $stmt->bindValue(':s', $sessionId, PDO::PARAM_INT);
        $stmt->bindValue(':u', $userId,    PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Liste des inscrits à une session (admin/modérateur).
     */
    public static function getAttendees(int $sessionId): array {
        $pdo  = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT sa.*, u.firstname, u.lastname, u.email
             FROM session_attendance sa
             JOIN users u ON sa.user_id = u.id
             WHERE sa.session_id = :s
             ORDER BY sa.created_at ASC"
        );
        $stmt->bindValue(':s', $sessionId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

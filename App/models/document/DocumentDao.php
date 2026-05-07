<?php
require_once __DIR__ . '/../../config/Db.php';
require_once __DIR__ . '/Document.php';

class DocumentDao {

    /**
     * Liste les documents d'une lecture, avec infos sur l'uploader.
     */
    public static function getByBookId(int $bookId): array {
        $pdo = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT d.*, u.firstname AS uploader_firstname, u.lastname AS uploader_lastname
             FROM documents d
             LEFT JOIN users u ON d.uploaded_by = u.id
             WHERE d.book_id = :book_id
             ORDER BY d.uploaded_at DESC"
        );
        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un document par son id.
     */
    public static function getById(int $id): ?array {
        $pdo = Db::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crée un document (lié à une lecture). Retourne l'id généré.
     */
    public static function create(Document $doc): int {
        $pdo = Db::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO documents (book_id, filename, filepath, mime, size, uploaded_by)
             VALUES (:book_id, :filename, :filepath, :mime, :size, :uploaded_by)"
        );
        $stmt->bindValue(':book_id',     $doc->getBookId(),  PDO::PARAM_INT);
        $stmt->bindValue(':filename',    $doc->getFilename());
        $stmt->bindValue(':filepath',    $doc->getFilepath());
        $stmt->bindValue(':mime',        $doc->getMime());
        $stmt->bindValue(':size',        $doc->getSize(),    PDO::PARAM_INT);
        $stmt->bindValue(':uploaded_by', $doc->getUploadedBy(),
            $doc->getUploadedBy() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    /**
     * Supprime un document. Retourne le filepath supprimé pour que le
     * contrôleur puisse nettoyer le fichier sur disque.
     */
    public static function delete(int $id): ?string {
        $pdo = Db::getConnection();

        $stmt = $pdo->prepare("SELECT filepath FROM documents WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $filepath = $row['filepath'] ?? null;

        $stmt = $pdo->prepare("DELETE FROM documents WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $filepath;
    }

    /**
     * Liste les filepaths pour un livre — utilisé avant de supprimer un livre
     * pour faire le ménage des fichiers physiques (la suppression des lignes
     * documents elle-même est gérée par la FK ON DELETE CASCADE).
     */
    public static function getFilepathsByBookId(int $bookId): array {
        $pdo = Db::getConnection();
        $stmt = $pdo->prepare("SELECT filepath FROM documents WHERE book_id = :book_id");
        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn($r) => $r['filepath'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

<?php
require_once __DIR__ . '/../../config/Db.php';
require_once __DIR__ . '/Book.php';

class BookDao {

    /**
     * Liste de toutes les lectures, classées par date de création décroissante
     * (puis par titre si created_at n'existe pas).
     */
    public static function getAllBooks(): array {
        $pdo = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT b.*, u.firstname AS creator_firstname, u.lastname AS creator_lastname
             FROM books b
             LEFT JOIN users u ON b.created_by = u.id
             ORDER BY b.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une lecture par son id (avec infos sur le créateur).
     */
    public static function getBookById(int $id): ?array {
        $pdo = Db::getConnection();
        $stmt = $pdo->prepare(
            "SELECT b.*, u.firstname AS creator_firstname, u.lastname AS creator_lastname
             FROM books b
             LEFT JOIN users u ON b.created_by = u.id
             WHERE b.id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crée une nouvelle lecture. Retourne l'id généré.
     */
    public static function createBook(Book $book): int {
        $pdo = Db::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO books (title, author, synopsis, cover_path, release_date, created_by)
             VALUES (:title, :author, :synopsis, :cover_path, :release_date, :created_by)"
        );
        $stmt->bindValue(':title',        $book->getTitle());
        $stmt->bindValue(':author',       $book->getAuthor());
        $stmt->bindValue(':synopsis',     $book->getSynopsis());
        $stmt->bindValue(':cover_path',   $book->getCoverPath());
        $stmt->bindValue(':release_date', $book->getReleaseDate());
        $stmt->bindValue(':created_by',   $book->getCreatedBy(), $book->getCreatedBy() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour une lecture existante. Si $coverPath est null, on ne touche
     * pas à la couverture (utile lorsqu'aucun nouveau fichier n'est uploadé).
     */
    public static function updateBook(int $id, string $title, string $author, ?string $synopsis, ?string $releaseDate, ?string $coverPath): void {
        $pdo = Db::getConnection();

        if ($coverPath !== null) {
            $stmt = $pdo->prepare(
                "UPDATE books
                 SET title = :title,
                     author = :author,
                     synopsis = :synopsis,
                     release_date = :release_date,
                     cover_path = :cover_path
                 WHERE id = :id"
            );
            $stmt->bindValue(':cover_path', $coverPath);
        } else {
            $stmt = $pdo->prepare(
                "UPDATE books
                 SET title = :title,
                     author = :author,
                     synopsis = :synopsis,
                     release_date = :release_date
                 WHERE id = :id"
            );
        }

        $stmt->bindValue(':title',        $title);
        $stmt->bindValue(':author',       $author);
        $stmt->bindValue(':synopsis',     $synopsis);
        $stmt->bindValue(':release_date', $releaseDate);
        $stmt->bindValue(':id',           $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Supprime une lecture. La FK ON DELETE SET NULL côté users gère le cas
     * où le créateur a été supprimé. Renvoie le cover_path supprimé pour
     * que le contrôleur puisse nettoyer le fichier sur disque.
     */
    public static function deleteBook(int $id): ?string {
        $pdo = Db::getConnection();

        $stmt = $pdo->prepare("SELECT cover_path FROM books WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $coverPath = $row['cover_path'] ?? null;

        $stmt = $pdo->prepare("DELETE FROM books WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $coverPath;
    }
}

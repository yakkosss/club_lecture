<?php
require_once __DIR__ . '/../models/book/BookDao.php';
require_once __DIR__ . '/../models/book/Book.php';
require_once __DIR__ . '/../models/document/DocumentDao.php';
require_once __DIR__ . '/../services/AccessGuard.php';
require_once __DIR__ . '/../services/UploadService.php';

/**
 * BookController — CRUD des "lectures" (table books).
 *
 * Matrice des droits (V1) :
 *   - Créer / modifier : Admin OUI, Modérateur OUI (limité), Membre NON
 *   - Supprimer        : Admin OUI uniquement
 *   - Consulter        : tous les utilisateurs connectés
 *
 * Le "limité" pour le modérateur est interprété comme : peut créer / modifier
 * mais NE PEUT PAS supprimer.
 */
class BookController {

    /**
     * Liste de toutes les lectures.
     */
    public function index(): void {
        AccessGuard::requireLogin();
        $books = BookDao::getAllBooks();
        require __DIR__ . '/../views/book/index.php';
    }

    /**
     * Fiche détaillée d'une lecture (avec emplacements pour docs/avis/progression
     * qui seront remplis par les modules suivants).
     */
    public function show(): void {
        AccessGuard::requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $book = $id > 0 ? BookDao::getBookById($id) : null;

        if (!$book) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        // Données associées passées à la vue (respect MVC : la vue ne fait pas de SQL).
        $documents = DocumentDao::getByBookId((int) $book['id']);

        require __DIR__ . '/../views/book/show.php';
    }

    public function displayCreateForm(): void {
        AccessGuard::requireRole('admin', 'moderator');
        require __DIR__ . '/../views/book/create.php';
    }

    public function createBook(): void {
        $user = AccessGuard::requireRole('admin', 'moderator');

        $title       = trim($_POST['title'] ?? '');
        $author      = trim($_POST['author'] ?? '');
        $synopsis    = trim($_POST['synopsis'] ?? '');
        $releaseDate = trim($_POST['release_date'] ?? '');

        if ($title === '' || $author === '') {
            $_SESSION['flash_error'] = "Le titre et l'auteur sont obligatoires.";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=displayCreateForm');
            exit;
        }

        // Upload couverture (optionnel)
        [$coverPath, $uploadError] = UploadService::saveCoverImage($_FILES['cover'] ?? []);
        if ($uploadError) {
            $_SESSION['flash_error'] = $uploadError;
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=displayCreateForm');
            exit;
        }

        $book = new Book(
            $title,
            $author,
            $synopsis !== '' ? $synopsis : null,
            $coverPath,
            $releaseDate !== '' ? $releaseDate : null,
            (int) $user['id']
        );

        try {
            $id = BookDao::createBook($book);
            $_SESSION['flash_success'] = "Lecture créée.";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $id);
            exit;
        } catch (Throwable $e) {
            // En cas d'échec BDD on supprime la couverture qu'on venait d'uploader
            UploadService::deleteRelativeFile($coverPath);
            $_SESSION['flash_error'] = "Impossible de créer la lecture.";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=displayCreateForm');
            exit;
        }
    }

    public function displayEditForm(): void {
        AccessGuard::requireRole('admin', 'moderator');
        $id = (int) ($_GET['id'] ?? 0);
        $book = $id > 0 ? BookDao::getBookById($id) : null;

        if (!$book) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        require __DIR__ . '/../views/book/edit.php';
    }

    public function updateBook(): void {
        AccessGuard::requireRole('admin', 'moderator');

        $id = (int) ($_POST['id'] ?? 0);
        $existing = $id > 0 ? BookDao::getBookById($id) : null;
        if (!$existing) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $title       = trim($_POST['title'] ?? '');
        $author      = trim($_POST['author'] ?? '');
        $synopsis    = trim($_POST['synopsis'] ?? '');
        $releaseDate = trim($_POST['release_date'] ?? '');

        if ($title === '' || $author === '') {
            $_SESSION['flash_error'] = "Le titre et l'auteur sont obligatoires.";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=displayEditForm&id=' . $id);
            exit;
        }

        // Couverture : ne change que si un nouveau fichier est uploadé
        [$newCoverPath, $uploadError] = UploadService::saveCoverImage($_FILES['cover'] ?? []);
        if ($uploadError) {
            $_SESSION['flash_error'] = $uploadError;
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=displayEditForm&id=' . $id);
            exit;
        }

        try {
            BookDao::updateBook(
                $id,
                $title,
                $author,
                $synopsis !== '' ? $synopsis : null,
                $releaseDate !== '' ? $releaseDate : null,
                $newCoverPath
            );
            if ($newCoverPath !== null && !empty($existing['cover_path'])) {
                UploadService::deleteRelativeFile($existing['cover_path']);
            }
            $_SESSION['flash_success'] = "Lecture mise à jour.";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $id);
            exit;
        } catch (Throwable $e) {
            if ($newCoverPath !== null) {
                UploadService::deleteRelativeFile($newCoverPath);
            }
            $_SESSION['flash_error'] = "Impossible de mettre à jour la lecture.";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=displayEditForm&id=' . $id);
            exit;
        }
    }

    /**
     * Suppression — réservée aux admins uniquement.
     */
    public function deleteBook(): void {
        AccessGuard::requireRole('admin');

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = "Identifiant invalide.";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=index');
            exit;
        }

        try {
            // Récupère les chemins des PDF pour les nettoyer du disque
            // (les lignes en BDD seront purgées par la FK ON DELETE CASCADE).
            $docPaths = DocumentDao::getFilepathsByBookId($id);

            $coverPath = BookDao::deleteBook($id);
            UploadService::deleteRelativeFile($coverPath);

            foreach ($docPaths as $p) {
                UploadService::deleteStorageFile($p);
            }

            $_SESSION['flash_success'] = "Lecture supprimée.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de supprimer cette lecture (lectures/avis liés ?).";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=index');
        exit;
    }
}

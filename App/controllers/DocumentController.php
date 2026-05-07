<?php
require_once __DIR__ . '/../models/document/DocumentDao.php';
require_once __DIR__ . '/../models/document/Document.php';
require_once __DIR__ . '/../models/book/BookDao.php';
require_once __DIR__ . '/../services/AccessGuard.php';
require_once __DIR__ . '/../services/UploadService.php';

/**
 * DocumentController — gestion des PDF rattachés aux lectures.
 *
 * Matrice des droits (V1) :
 *   - Uploader  : Admin OUI, Modérateur OUI, Membre NON
 *   - Télécharger : tout utilisateur connecté
 *   - Supprimer : Admin OUI, Modérateur OUI
 *
 * Les fichiers sont stockés dans App/storage/documents (HORS docroot Web)
 * et ne sont accessibles que via l'action `download` qui vérifie les droits.
 */
class DocumentController {

    /**
     * Upload d'un PDF lié à une lecture.
     */
    public function upload(): void {
        $user   = AccessGuard::requireRole('admin', 'moderator');
        $bookId = (int) ($_POST['book_id'] ?? 0);

        $book = $bookId > 0 ? BookDao::getBookById($bookId) : null;
        if (!$book) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        [$info, $error] = UploadService::savePdfDocument($_FILES['document'] ?? []);
        if ($error) {
            $_SESSION['flash_error'] = $error;
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $bookId);
            exit;
        }

        $doc = new Document(
            $bookId,
            $info['filename'],
            $info['filepath'],
            $info['mime'],
            $info['size'],
            (int) $user['id']
        );

        try {
            DocumentDao::create($doc);
            $_SESSION['flash_success'] = "Document ajouté.";
        } catch (Throwable $e) {
            // En cas d'échec en BDD on supprime le fichier orphelin
            UploadService::deleteStorageFile($info['filepath']);
            $_SESSION['flash_error'] = "Impossible d'enregistrer le document.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $bookId);
        exit;
    }

    /**
     * Téléchargement d'un document. Le script vérifie les droits
     * (utilisateur connecté) puis sert le fichier.
     */
    public function download(): void {
        AccessGuard::requireLogin();

        $id  = (int) ($_GET['id'] ?? 0);
        $doc = $id > 0 ? DocumentDao::getById($id) : null;
        if (!$doc) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $absolutePath = UploadService::resolveStoragePath($doc['filepath']);
        if ($absolutePath === null || !is_file($absolutePath)) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        // On nettoie tout output buffer pour éviter de polluer le stream binaire
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($doc['mime'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $this->headerSafe($doc['filename']) . '"');
        header('Content-Length: ' . filesize($absolutePath));
        header('Cache-Control: private, no-transform, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($absolutePath);
        exit;
    }

    /**
     * Suppression d'un document (admin/modérateur).
     */
    public function delete(): void {
        AccessGuard::requireRole('admin', 'moderator');

        $id = (int) ($_POST['id'] ?? 0);
        $doc = $id > 0 ? DocumentDao::getById($id) : null;
        if (!$doc) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }
        $bookId = (int) $doc['book_id'];

        try {
            $filepath = DocumentDao::delete($id);
            UploadService::deleteStorageFile($filepath);
            $_SESSION['flash_success'] = "Document supprimé.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de supprimer ce document.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $bookId);
        exit;
    }

    /**
     * Échappe un nom de fichier pour le header HTTP Content-Disposition.
     */
    private function headerSafe(string $filename): string {
        return str_replace(['"', "\r", "\n"], ['', '', ''], $filename);
    }
}

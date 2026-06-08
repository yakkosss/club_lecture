<?php

require_once __DIR__ . '/../models/review/ReviewDao.php';
require_once __DIR__ . '/../models/review/Review.php';
require_once __DIR__ . '/../services/AccessGuard.php';

/**
 * ReviewController — EF-05 Avis (note 1-5 + commentaire + modération).
 *
 * Droits :
 *   - Publier/modifier/supprimer son propre avis : tout utilisateur connecté
 *   - Masquer/démasquer un avis                  : admin, modérateur
 *   - Supprimer n'importe quel avis              : admin
 */
class ReviewController {

    public function store(): void {
        $user   = AccessGuard::requireLogin();
        $bookId = (int) ($_POST['book_id'] ?? 0);
        $note   = (int) ($_POST['note'] ?? 0);
        $comm   = trim($_POST['commentaire'] ?? '');

        if ($bookId <= 0 || $note < 1 || $note > 5) {
            $_SESSION['flash_error'] = "Note invalide (1 à 5 requis).";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $bookId);
            exit;
        }

        $existing = ReviewDao::findByBookAndUser($bookId, (int) $user['id']);
        if ($existing) {
            $_SESSION['flash_error'] = "Vous avez déjà publié un avis pour ce livre.";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $bookId);
            exit;
        }

        try {
            ReviewDao::create(new Review($bookId, (int) $user['id'], $note, $comm !== '' ? $comm : null));
            $_SESSION['flash_success'] = "Avis publié.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de publier l'avis.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $bookId);
        exit;
    }

    public function update(): void {
        $user = AccessGuard::requireLogin();
        $id   = (int) ($_POST['id'] ?? 0);
        $review = $id > 0 ? ReviewDao::getById($id) : null;

        if (!$review) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        // Seul l'auteur ou un admin peut modifier
        if ((int) $review['user_id'] !== (int) $user['id'] && $user['role'] !== 'admin') {
            AccessGuard::deny();
        }

        $note = (int) ($_POST['note'] ?? 0);
        $comm = trim($_POST['commentaire'] ?? '');

        if ($note < 1 || $note > 5) {
            $_SESSION['flash_error'] = "Note invalide (1 à 5 requis).";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $review['book_id']);
            exit;
        }

        try {
            ReviewDao::update($id, $note, $comm !== '' ? $comm : null);
            $_SESSION['flash_success'] = "Avis mis à jour.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de mettre à jour l'avis.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $review['book_id']);
        exit;
    }

    public function delete(): void {
        $user = AccessGuard::requireLogin();
        $id   = (int) ($_POST['id'] ?? 0);
        $review = $id > 0 ? ReviewDao::getById($id) : null;

        if (!$review) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        // Auteur ou admin peuvent supprimer
        if ((int) $review['user_id'] !== (int) $user['id'] && $user['role'] !== 'admin') {
            AccessGuard::deny();
        }

        try {
            ReviewDao::delete($id);
            $_SESSION['flash_success'] = "Avis supprimé.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de supprimer l'avis.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $review['book_id']);
        exit;
    }

    /**
     * Masque ou démasque un avis (admin/modérateur).
     */
    public function toggleHidden(): void {
        AccessGuard::requireRole('admin', 'moderator');

        $id     = (int) ($_POST['id'] ?? 0);
        $review = $id > 0 ? ReviewDao::getById($id) : null;

        if (!$review) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $newHidden = !(bool) $review['hidden'];
        try {
            ReviewDao::setHidden($id, $newHidden);
            $_SESSION['flash_success'] = $newHidden ? "Avis masqué." : "Avis réactivé.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de modifier la visibilité de l'avis.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $review['book_id']);
        exit;
    }
}

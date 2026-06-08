<?php

require_once __DIR__ . '/../models/comment/CommentDao.php';
require_once __DIR__ . '/../models/comment/Comment.php';
require_once __DIR__ . '/../services/AccessGuard.php';

class CommentsController {

    public function store(): void {
        $user   = AccessGuard::requireLogin();
        $bookId = (int) ($_POST['book_id'] ?? 0);
        $text   = trim($_POST['comment_text'] ?? '');
        $parent = ($_POST['parent'] ?? '') !== '' ? (int) $_POST['parent'] : null;

        if ($bookId <= 0 || $text === '') {
            $_SESSION['flash_error'] = "Le commentaire ne peut pas etre vide.";
            header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $bookId . '#comments');
            exit;
        }

        if ($parent !== null) {
            $parentRow = CommentDao::getById($parent);
            if (!$parentRow || (int) $parentRow['book_id'] !== $bookId) {
                $parent = null;
            }
        }

        try {
            CommentDao::create(new Comment((int) $user['id'], $bookId, $text, $parent));
            $_SESSION['flash_success'] = "Commentaire envoye (en attente de validation).";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible d'enregistrer le commentaire.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $bookId . '#comments');
        exit;
    }

    public function updateText(): void {
        $user = AccessGuard::requireLogin();
        $id   = (int) ($_POST['id'] ?? 0);
        $c    = $id > 0 ? CommentDao::getById($id) : null;

        if (!$c) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        if ((int) $c['author'] !== (int) $user['id'] && $user['role'] !== 'admin') {
            AccessGuard::deny();
        }

        $text = trim($_POST['comment_text'] ?? '');
        if ($text !== '') {
            CommentDao::updateText($id, $text);
            $_SESSION['flash_success'] = "Commentaire modifie.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $c['book_id'] . '#comments');
        exit;
    }

    public function delete(): void {
        $user = AccessGuard::requireLogin();
        $id   = (int) ($_POST['id'] ?? 0);
        $c    = $id > 0 ? CommentDao::getById($id) : null;

        if (!$c) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        if ((int) $c['author'] !== (int) $user['id'] && !in_array($user['role'], ['admin', 'moderator'], true)) {
            AccessGuard::deny();
        }

        CommentDao::delete($id);
        $_SESSION['flash_success'] = "Commentaire supprime.";

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $c['book_id'] . '#comments');
        exit;
    }

    public function updateState(): void {
        AccessGuard::requireRole('admin', 'moderator');

        $id    = (int) ($_POST['id'] ?? 0);
        $state = $_POST['state'] ?? '';
        $c     = $id > 0 ? CommentDao::getById($id) : null;

        if (!$c) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        CommentDao::updateState($id, $state);
        $_SESSION['flash_success'] = "Statut mis a jour.";

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $c['book_id'] . '#comments');
        exit;
    }

    public function report(): void {
        $user = AccessGuard::requireLogin();
        $id   = (int) ($_POST['id'] ?? 0);
        $c    = $id > 0 ? CommentDao::getById($id) : null;

        if (!$c) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        if ((int) $c['author'] === (int) $user['id']) {
            $_SESSION['flash_error'] = "Vous ne pouvez pas signaler votre propre commentaire.";
        } else {
            CommentDao::updateState($id, 'REPORTED');
            $_SESSION['flash_success'] = "Commentaire signale.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=Book&action=show&id=' . $c['book_id'] . '#comments');
        exit;
    }
}

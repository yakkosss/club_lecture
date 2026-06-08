<?php

require_once __DIR__ . '/../models/session/SessionDao.php';
require_once __DIR__ . '/../models/session/Session.php';
require_once __DIR__ . '/../models/book/BookDao.php';
require_once __DIR__ . '/../services/AccessGuard.php';

/**
 * SessionController — EF-07 Sessions (lives/rencontres).
 *
 * Droits :
 *   - Créer / supprimer : admin, modérateur
 *   - S'inscrire / se désinscrire : tout connecté
 *   - Voir liste inscrits : admin, modérateur
 */
class SessionController {

    public function index(): void {
        AccessGuard::requireLogin();
        $sessions = SessionDao::getAll();
        require __DIR__ . '/../views/session/index.php';
    }

    public function show(): void {
        AccessGuard::requireLogin();
        $user = $_SESSION['user'];
        $id   = (int) ($_GET['id'] ?? 0);
        $session = $id > 0 ? SessionDao::getById($id) : null;

        if (!$session) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $isRegistered = SessionDao::isRegistered($id, (int) $user['id']);
        $attendees    = null;
        if (in_array($user['role'], ['admin', 'moderator'], true)) {
            $attendees = SessionDao::getAttendees($id);
        }

        require __DIR__ . '/../views/session/show.php';
    }

    public function displayCreateForm(): void {
        AccessGuard::requireRole('admin', 'moderator');
        $books = BookDao::getAllBooks();
        require __DIR__ . '/../views/session/create.php';
    }

    public function create(): void {
        $user  = AccessGuard::requireRole('admin', 'moderator');
        $bookId = (int) ($_POST['book_id'] ?? 0);
        $titre  = trim($_POST['titre'] ?? '');
        $dateHeure = trim($_POST['date_heure'] ?? '');
        $lien   = trim($_POST['lien'] ?? '') ?: null;
        $lieu   = trim($_POST['lieu'] ?? '') ?: null;
        $desc   = trim($_POST['description'] ?? '') ?: null;

        if ($bookId <= 0 || $titre === '' || $dateHeure === '') {
            $_SESSION['flash_error'] = "Livre, titre et date/heure sont obligatoires.";
            header('Location: ' . BASE_URL . 'index.php?controller=Session&action=displayCreateForm');
            exit;
        }

        try {
            $id = SessionDao::create(new Session($bookId, $titre, $dateHeure, $lien, $lieu, $desc, (int) $user['id']));
            $_SESSION['flash_success'] = "Session créée.";
            header('Location: ' . BASE_URL . 'index.php?controller=Session&action=show&id=' . $id);
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de créer la session.";
            header('Location: ' . BASE_URL . 'index.php?controller=Session&action=displayCreateForm');
        }
        exit;
    }

    public function delete(): void {
        AccessGuard::requireRole('admin', 'moderator');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'index.php?controller=Session&action=index');
            exit;
        }
        try {
            SessionDao::delete($id);
            $_SESSION['flash_success'] = "Session supprimée.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de supprimer la session.";
        }
        header('Location: ' . BASE_URL . 'index.php?controller=Session&action=index');
        exit;
    }

    public function register(): void {
        $user = AccessGuard::requireLogin();
        $id   = (int) ($_POST['session_id'] ?? 0);
        if ($id > 0) {
            SessionDao::registerAttendance($id, (int) $user['id']);
            $_SESSION['flash_success'] = "Inscription confirmée.";
        }
        header('Location: ' . BASE_URL . 'index.php?controller=Session&action=show&id=' . $id);
        exit;
    }

    public function unregister(): void {
        $user = AccessGuard::requireLogin();
        $id   = (int) ($_POST['session_id'] ?? 0);
        if ($id > 0) {
            SessionDao::cancelAttendance($id, (int) $user['id']);
            $_SESSION['flash_success'] = "Désinscription effectuée.";
        }
        header('Location: ' . BASE_URL . 'index.php?controller=Session&action=show&id=' . $id);
        exit;
    }
}

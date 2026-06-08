<?php
require_once __DIR__ . '/../models/user/UserDao.php';
require_once __DIR__ . '/../models/user/User.php';
require_once __DIR__ . '/../models/role/Role.php';
require_once __DIR__ . '/../services/AccessGuard.php';

class UserController {

    /**
     * Liste des utilisateurs — réservée aux admins.
     */
    public function index(): void {
        AccessGuard::requireRole('admin');
        $users = UserDao::getAllUsers();
        require __DIR__ . '/../views/users/index.php';
    }

    public function displayCreateForm(): void {
        AccessGuard::requireRole('admin');
        require __DIR__ . '/../views/user/create.php';
    }

    public function createUser(): void {
        AccessGuard::requireRole('admin');

        $firstname = trim($_POST['firstname'] ?? '');
        $lastname  = trim($_POST['lastname'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $rolePost  = $_POST['role'] ?? 'member';
        $password  = $_POST['password'] ?? '';

        if ($firstname === '' || $lastname === '' || $email === '' || $password === '') {
            $_SESSION['flash_error'] = "Tous les champs sont obligatoires.";
            header('Location: ' . BASE_URL . 'index.php?controller=User&action=displayCreateForm');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = "L'adresse email n'est pas valide.";
            header('Location: ' . BASE_URL . 'index.php?controller=User&action=displayCreateForm');
            exit;
        }

        $role = Role::tryFrom($rolePost) ?? Role::member;
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            UserDao::createUser(new User($firstname, $lastname, $email, $hash, $role));
            $_SESSION['flash_success'] = "Utilisateur créé.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de créer l'utilisateur (email déjà utilisé ?).";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=User&action=displayCreateForm');
        exit;
    }

    /**
     * Promouvoir / rétrograder un utilisateur (EF-02) — admin uniquement.
     */
    public function updateRole(): void {
        $currentUser = AccessGuard::requireRole('admin');

        $targetId = (int) ($_POST['user_id'] ?? 0);
        $rolePost  = $_POST['role'] ?? '';

        // Un admin ne peut pas rétrograder son propre compte
        if ($targetId === (int) $currentUser['id']) {
            $_SESSION['flash_error'] = "Vous ne pouvez pas modifier votre propre rôle.";
            header('Location: ' . BASE_URL . 'index.php?controller=User&action=index');
            exit;
        }

        $role = Role::tryFrom($rolePost);
        if (!$role || $targetId <= 0) {
            $_SESSION['flash_error'] = "Rôle invalide.";
            header('Location: ' . BASE_URL . 'index.php?controller=User&action=index');
            exit;
        }

        try {
            UserDao::updateRole($targetId, $role);
            $_SESSION['flash_success'] = "Rôle mis à jour.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de modifier le rôle.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=User&action=index');
        exit;
    }

    /**
     * Suppression d'un utilisateur — admin uniquement.
     */
    public function deleteUser(): void {
        $currentUser = AccessGuard::requireRole('admin');

        $targetId = (int) ($_POST['user_id'] ?? 0);

        if ($targetId === (int) $currentUser['id']) {
            $_SESSION['flash_error'] = "Vous ne pouvez pas supprimer votre propre compte.";
            header('Location: ' . BASE_URL . 'index.php?controller=User&action=index');
            exit;
        }

        try {
            UserDao::deleteById($targetId);
            $_SESSION['flash_success'] = "Utilisateur supprimé.";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Impossible de supprimer cet utilisateur.";
        }

        header('Location: ' . BASE_URL . 'index.php?controller=User&action=index');
        exit;
    }
}

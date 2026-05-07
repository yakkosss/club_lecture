<?php
/**
 * AccessGuard — point d'entrée unique pour le contrôle d'accès.
 *
 * Toute action sensible doit appeler une de ces méthodes au début du
 * controller. Si l'utilisateur n'a pas le droit, on renvoie une 403
 * (page d'erreur dédiée) et on stoppe l'exécution. Cela évite la double
 * vérification et garantit le respect de la matrice des droits, même
 * si l'URL est forgée à la main.
 */

require_once __DIR__ . '/AuthService.php';

class AccessGuard {

    /**
     * Exige qu'un utilisateur soit connecté.
     * Sinon redirige vers le formulaire de connexion.
     */
    public static function requireLogin(): array {
        $user = AuthService::getSessionUser();
        if (!$user) {
            $_SESSION['flash_error'] = "Vous devez être connecté pour accéder à cette page.";
            header('Location: ' . BASE_URL . 'index.php?controller=Auth&action=displayLoginForm');
            exit;
        }
        return $user;
    }

    /**
     * Exige que l'utilisateur connecté ait un des rôles passés.
     * Renvoie une page 403 sinon.
     *
     * @param string ...$allowedRoles  Liste des rôles autorisés ('admin', 'moderator', 'member')
     */
    public static function requireRole(string ...$allowedRoles): array {
        $user = self::requireLogin();
        if (!in_array($user['role'], $allowedRoles, true)) {
            self::deny();
        }
        return $user;
    }

    /**
     * Renvoie immédiatement une page 403 et stoppe l'exécution.
     */
    public static function deny(): void {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
}

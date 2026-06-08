<?php

require_once __DIR__ . '/../models/user/User.php';
require_once __DIR__ . '/../models/user/UserDao.php';
require_once __DIR__ . '/../models/role/Role.php';

class AuthService {

    public static function login(string $email, string $password): bool {
        $user = UserDao::findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            return false;
        }

        $_SESSION['user'] = [
            'id'        => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname'  => $user->getLastname(),
            'email'     => $user->getEmail(),
            'role'      => $user->getRole()->value,
        ];

        return true;
    }

    /**
     * Inscrit un nouvel utilisateur (rôle 'member' par défaut).
     * Renvoie [bool $ok, ?string $error].
     */
    public static function register(string $firstname, string $lastname, string $email, string $password): array {
        if ($firstname === '' || $lastname === '' || $email === '' || $password === '') {
            return [false, "Tous les champs sont obligatoires."];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, "L'adresse email n'est pas valide."];
        }
        if (strlen($password) < 6) {
            return [false, "Le mot de passe doit faire au moins 6 caractères."];
        }
        if (UserDao::findByEmail($email) !== null) {
            return [false, "Un compte existe déjà avec cet email."];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $user = new User($firstname, $lastname, $email, $hash, Role::member);
        UserDao::createUser($user);

        return [true, null];
    }

    /**
     * Détruit la session courante. La session a déjà été démarrée par
     * le routeur, donc on ne la redémarre pas ici.
     */
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function getSessionUser(): ?array {
        return $_SESSION['user'] ?? null;
    }
}

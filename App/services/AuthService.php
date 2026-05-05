<?php

require_once __DIR__ . '/../models/user/User.php';
require_once __DIR__ . '/../models/user/UserDao.php';

class AuthService {

    public static function login(string $email, string $password){

        $user = UserDao::findByEmail($email);

        if (!$user) {
            return false;
        }

        if(!password_verify($password, $user->getPasswordHash())) {
            return false;
        }

        session_start();

        $_SESSION['user'] = ['id' => $user->getId(), 'firstname' => $user->getFirstname(), 
            'lastname' => $user->getLastname(), 'email' => $user->getEmail(), 
            'role' => $user->getRole()];

        return true;
    }

    public static function logout() {
        session_start();
        session_destroy();
    }

    public static function getSessionUser() {
        return $_SESSION['user'] ?? null;
    }
}
?>
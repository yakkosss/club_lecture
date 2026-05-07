<?php

require_once __DIR__ . '/../services/AuthService.php';

class AuthController {

    public function displayLoginForm(): void {
        require __DIR__ . '/../views/auth/login.php';
    }

    public function displayRegisterForm(): void {
        require __DIR__ . '/../views/auth/register.php';
    }

    public function login(): void {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (AuthService::login($email, $password)) {
            header('Location: ' . BASE_URL . 'index.php?controller=Home&action=index');
            exit;
        }

        $_SESSION['flash_error'] = "Un ou plusieurs identifiants sont incorrects.";
        header('Location: ' . BASE_URL . 'index.php?controller=Auth&action=displayLoginForm');
        exit;
    }

    public function register(): void {
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname  = trim($_POST['lastname'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';

        [$ok, $error] = AuthService::register($firstname, $lastname, $email, $password);

        if (!$ok) {
            $_SESSION['flash_error'] = $error;
            $_SESSION['flash_old'] = [
                'firstname' => $firstname,
                'lastname'  => $lastname,
                'email'     => $email,
            ];
            header('Location: ' . BASE_URL . 'index.php?controller=Auth&action=displayRegisterForm');
            exit;
        }

        // Auto-login après inscription pour fluidifier l'UX
        AuthService::login($email, $password);
        header('Location: ' . BASE_URL . 'index.php?controller=Home&action=index');
        exit;
    }

    public function logout(): void {
        AuthService::logout();
        header('Location: ' . BASE_URL . 'index.php?controller=Auth&action=displayLoginForm');
        exit;
    }
}

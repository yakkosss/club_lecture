<?php 

require_once __DIR__ . '/../services/AuthService.php';

class AuthController {

    public function displayLoginForm() {

        require_once __DIR__ . '/../views/auth/login.php';

    }

    public function login() {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if(AuthService::login($email, $password)) {
            header('Location: index.php?controller=Home&action=index');
            exit;
        }
        echo "Un ou plusieurs identifiants sont incorrects";
        exit;

    }

    public function logout() {
        AuthService::logout();
        header('Location: index.php?controller=Auth&action=displayLoginForm');
        exit;
    }
}
?>
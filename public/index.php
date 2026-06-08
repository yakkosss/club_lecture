<?php

// ========================
// CONFIGURATION
// ========================
$env = parse_ini_file(__DIR__ . '/../.env');
if (!$env) {
    http_response_code(500);
    exit('Configuration .env introuvable.');
}
define('BASE_URL', $env['BASE_URL']);

session_start();

// ========================
// AUTLOAD (simple)
// ========================
require_once __DIR__ . '/../App/controllers/UserController.php';
require_once __DIR__ . '/../App/controllers/HomeController.php';
require_once __DIR__ . '/../App/controllers/AuthController.php';
require_once __DIR__ . '/../App/controllers/BookController.php';
require_once __DIR__ . '/../App/controllers/DocumentController.php';
require_once __DIR__ . '/../App/controllers/ReviewController.php';
require_once __DIR__ . '/../App/controllers/ProgressController.php';
require_once __DIR__ . '/../App/controllers/SessionController.php';
require_once __DIR__ . '/../App/controllers/CommentsController.php';

// ========================
// LECTURE URL
// ========================
// Par défaut : si l'utilisateur est connecté on l'envoie sur l'accueil,
// sinon sur le formulaire de connexion.
if (!empty($_SESSION['user'])) {
    $defaultController = 'Home';
    $defaultAction     = 'index';
} else {
    $defaultController = 'Auth';
    $defaultAction     = 'displayLoginForm';
}

$controller = ucfirst($_GET['controller'] ?? $defaultController);
$action     = $_GET['action']             ?? $defaultAction;

// ========================
// 404 — Controller / Action introuvables
// ========================
function render404(): void {
    http_response_code(404);
    require __DIR__ . '/../App/views/errors/404.php';
    exit;
}

$controllerClass = $controller . 'Controller';
if (!class_exists($controllerClass)) {
    render404();
}

$ctrl = new $controllerClass();
if (!method_exists($ctrl, $action)) {
    render404();
}

// ========================
// EXECUTION
// ========================
$ctrl->$action();

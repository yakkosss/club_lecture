<?php

// ========================
// AUTLOAD (simple)
// ========================
require_once __DIR__ . '/../App/controllers/UserController.php';
require_once __DIR__ . '/../App/controllers/HomeController.php';
require_once __DIR__ . '/../App/controllers/AuthController.php';

// ajoute les autres controllers ici plus tard

$env = parse_ini_file(__DIR__ . '/../.env');

define('BASE_URL', $env['BASE_URL']);

// ========================
// LECTURE URL
// ========================
$controller = $_GET['controller'] ?? 'Auth';
$action = $_GET['action'] ?? 'displayLoginForm';

// ========================
// CONSTRUCTION DYNAMIQUE
// ========================

// Exemple : user → UserController
$controllerClass = $controller . 'Controller';

// Vérification du controller
if (!class_exists($controllerClass)) {
    http_response_code(404);
    echo "Controller introuvable";
    exit;
}

$ctrl = new $controllerClass();

// Vérification de la méthode
if (!method_exists($ctrl, $action)) {
    http_response_code(404);
    echo "Action introuvable";
    exit;
}

// ========================
// EXECUTION
// ========================
$ctrl->$action();
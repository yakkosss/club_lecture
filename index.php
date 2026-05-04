<?php

// ========================
// AUTLOAD (simple)
// ========================
require_once __DIR__ . '/App/controllers/UserController.php';

// ajoute les autres controllers ici plus tard

// ========================
// LECTURE URL
// ========================
$controller = $_GET['controller'] ?? 'user';
$action = $_GET['action'] ?? 'displayCreateForm';

// ========================
// CONSTRUCTION DYNAMIQUE
// ========================

// Exemple : user → UserController
$controllerClass = ucfirst($controller) . 'Controller';

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
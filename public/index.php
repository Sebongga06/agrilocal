<?php
ob_start(); // Buffer all output — prevents PHP notices from corrupting JSON responses
session_start();

// Composer autoload (required for 2FA and other packages)
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../app/controllers/Controller.php';
require_once __DIR__ . '/../config/Database.php';

// Auto-load model files
foreach (glob(__DIR__ . '/../app/models/*.php') as $modelFile) {
    require_once $modelFile;
}

// Default route
$url = $_GET['url'] ?? 'farmer';
$url = explode('/', trim($url, '/'));

// Controller and method
$controllerName = ucfirst($url[0]) . 'Controller';
$method = $url[1] ?? 'index';
$params = array_slice($url, 2);

// Controller file path
$controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    die("Controller '$controllerName' not found.");
}

require_once $controllerFile;

if (!class_exists($controllerName)) {
    die("Controller class '$controllerName' not found.");
}

$controller = new $controllerName();

if (!method_exists($controller, $method)) {
    die("Method '$method' not found in controller '$controllerName'.");
}

call_user_func_array([$controller, $method], $params);

// Flush the output buffer for normal page responses
// (JSON endpoints call ob_get_clean() + ob_start() themselves before echoing)
if (ob_get_level() > 0) {
    ob_end_flush();
}
<?php
session_start();

require_once '../app/core/Controller.php';
require_once '../app/core/Model.php';

// Get URL
$url = $_GET['url'] ?? 'home';
$url = explode('/', trim($url, '/'));

$controllerName = ucfirst($url[0]) . 'Controller';
$method = $url[1] ?? 'index';

// Load controller
require_once "../app/controllers/$controllerName.php";

$controller = new $controllerName();

// Call method
$params = array_slice($url, 2);
call_user_func_array([$controller, $method], $params);
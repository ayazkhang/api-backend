<?php

require_once 'UserController.php';


function handleRequest($method, $uri)
{
   
    $controller = new UserController();

    if ($method === 'POST' && $uri === '/login') {
        $controller->login();
    } elseif ($method === 'POST' && $uri === '/logout') {
        $controller->logout();
    }elseif ($method === 'POST' && $uri === '/register') {
        $controller->register();
    } elseif ($method === 'POST' && $uri === '/createDatabase') {
        $controller->createDatabase();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Route not found']);
    }
}

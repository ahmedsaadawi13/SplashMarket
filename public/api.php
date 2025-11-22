<?php
// FILE: /public/api.php

/**
 * SplashMarket - API Entry Point
 *
 * RESTful API for external integrations
 * PHP 7.0+ compatible
 */

// Set headers for API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-KEY, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Autoloader for classes
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load helper functions
require_once APP_PATH . '/helpers/functions.php';
require_once APP_PATH . '/helpers/security.php';

// Create router instance
$router = new Router();

// API routes
$router->get('/products', 'ApiController@products', 'api.products');
$router->get('/products/:id', 'ApiController@productDetail', 'api.products.show');
$router->post('/orders', 'ApiController@createOrder', 'api.orders.create');

// Get request URI and method
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove base path and api.php from URI
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = str_replace('/api.php', '', $scriptName);

if ($basePath && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Remove api.php from URI if present
$uri = str_replace('/api.php', '', $uri);

// Dispatch request
try {
    $router->dispatch($uri, $method);
} catch (Exception $e) {
    // Log error
    error_log('API Error: ' . $e->getMessage());

    // Return JSON error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);

    if (APP_ENV === 'development') {
        error_log($e->getTraceAsString());
    }
}

<?php
// FILE: /public/index.php

/**
 * SplashMarket - Admin Panel Entry Point
 *
 * Main entry point for admin panel
 * PHP 7.0+ compatible
 */

// Start session
session_start();

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
require_once APP_PATH . '/helpers/upload.php';

// Initialize session class
Session::start();

// Create router instance
$router = new Router();

// Load routes
require_once __DIR__ . '/../config/routes.php';

// Get request URI and method
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove base path from URI if needed
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = str_replace('/index.php', '', $scriptName);

if ($basePath && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Dispatch request
try {
    $router->dispatch($uri, $method);
} catch (Exception $e) {
    // Log error
    error_log('Application Error: ' . $e->getMessage());

    // Show error page
    http_response_code(500);

    if (APP_ENV === 'development') {
        echo '<h1>Application Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>An error occurred</h1>';
        echo '<p>Please try again later.</p>';
    }
}

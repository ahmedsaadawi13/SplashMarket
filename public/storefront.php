<?php
// FILE: /public/storefront.php

/**
 * SplashMarket - Storefront Entry Point
 *
 * Public-facing storefront
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

// Get tenant ID from parameter or session
$tenantId = isset($_GET['tenant_id']) ? (int) $_GET['tenant_id'] : Session::get('storefront_tenant_id');

if ($tenantId) {
    Session::set('storefront_tenant_id', $tenantId);
}

// Create router instance
$router = new Router();

// Storefront routes
$router->get('/', 'StorefrontController@home', 'storefront.home');
$router->get('/category/:slug', 'StorefrontController@category', 'storefront.category');
$router->get('/product/:slug', 'StorefrontController@product', 'storefront.product');
$router->post('/cart/add', 'StorefrontController@addToCart', 'storefront.cart.add');
$router->get('/cart', 'StorefrontController@cart', 'storefront.cart');
$router->post('/cart/update', 'StorefrontController@updateCart', 'storefront.cart.update');
$router->post('/cart/remove', 'StorefrontController@removeFromCart', 'storefront.cart.remove');
$router->get('/checkout', 'StorefrontController@checkout', 'storefront.checkout');
$router->post('/checkout', 'StorefrontController@processCheckout', 'storefront.checkout.process');
$router->get('/order-confirmation', 'StorefrontController@orderConfirmation', 'storefront.confirmation');

// Get request URI and method
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove base path and storefront.php from URI
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = str_replace('/storefront.php', '', $scriptName);

if ($basePath && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Remove storefront.php from URI if present
$uri = str_replace('/storefront.php', '', $uri);

// Dispatch request
try {
    $router->dispatch($uri, $method);
} catch (Exception $e) {
    // Log error
    error_log('Storefront Error: ' . $e->getMessage());

    // Show error page
    http_response_code(500);
    echo '<h1>An error occurred</h1>';
    echo '<p>Please try again later.</p>';

    if (APP_ENV === 'development') {
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
}

<?php
// FILE: /config/config.php

/**
 * SplashMarket - Application Configuration
 *
 * Main configuration file
 * PHP 7.0+ compatible
 */

// Environment mode (development, production)
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Application name
define('APP_NAME', 'SplashMarket');

// Application URL
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'splashmarket');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Timezone
define('APP_TIMEZONE', 'UTC');
date_default_timezone_set(APP_TIMEZONE);

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/error.log');
}

// Session configuration
ini_set('session.cookie_lifetime', 86400); // 24 hours
ini_set('session.gc_maxlifetime', 86400);

// Upload limits
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

// Pagination
define('PER_PAGE_DEFAULT', 20);

// Currency
define('DEFAULT_CURRENCY', 'USD');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');

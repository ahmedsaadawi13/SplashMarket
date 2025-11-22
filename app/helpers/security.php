<?php
// FILE: /app/helpers/security.php

/**
 * SplashMarket - Security Helper Functions
 *
 * Security-related utility functions
 * PHP 7.0+ compatible
 */

/**
 * Sanitize input string
 *
 * @param string $input Input string
 * @return string
 */
function sanitizeInput($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize filename
 *
 * @param string $filename Filename
 * @return string
 */
function sanitizeFilename($filename)
{
    // Remove any path components
    $filename = basename($filename);

    // Remove special characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

    return $filename;
}

/**
 * Validate email address
 *
 * @param string $email Email address
 * @return bool
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 *
 * @param string $url URL
 * @return bool
 */
function isValidUrl($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Generate random token
 *
 * @param int $length Token length
 * @return string
 */
function generateToken($length = 32)
{
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    } else {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}

/**
 * Hash password
 *
 * @param string $password Plain password
 * @return string
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 *
 * @param string $password Plain password
 * @param string $hash Password hash
 * @return bool
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Prevent directory traversal in file paths
 *
 * @param string $path File path
 * @return string
 */
function preventDirectoryTraversal($path)
{
    // Remove .. and other directory traversal attempts
    $path = str_replace(['../', '..\\', '..'], '', $path);

    return $path;
}

/**
 * Check if request is AJAX
 *
 * @return bool
 */
function isAjax()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 *
 * @return string
 */
function getClientIp()
{
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Rate limit check (simple implementation)
 *
 * @param string $key Rate limit key
 * @param int $maxAttempts Max attempts
 * @param int $decaySeconds Decay time in seconds
 * @return bool
 */
function rateLimit($key, $maxAttempts = 5, $decaySeconds = 60)
{
    $sessionKey = 'rate_limit_' . $key;

    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = [
            'attempts' => 1,
            'reset_at' => time() + $decaySeconds
        ];
        return true;
    }

    $data = $_SESSION[$sessionKey];

    // Reset if expired
    if (time() > $data['reset_at']) {
        $_SESSION[$sessionKey] = [
            'attempts' => 1,
            'reset_at' => time() + $decaySeconds
        ];
        return true;
    }

    // Check limit
    if ($data['attempts'] >= $maxAttempts) {
        return false;
    }

    // Increment attempts
    $_SESSION[$sessionKey]['attempts']++;

    return true;
}

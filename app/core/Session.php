<?php
// FILE: /app/core/Session.php

/**
 * SplashMarket - Session Manager
 *
 * Handles session operations and CSRF protection
 * PHP 7.0+ compatible
 */

class Session
{
    /**
     * Start session if not already started
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', 'Lax');

            // Start session
            session_start();

            // Generate CSRF token if not exists
            if (!isset($_SESSION['csrf_token'])) {
                self::regenerateCsrfToken();
            }
        }
    }

    /**
     * Set a session variable
     *
     * @param string $key Session key
     * @param mixed $value Session value
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable
     *
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * Check if session variable exists
     *
     * @param string $key Session key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session variable
     *
     * @param string $key Session key
     */
    public static function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy session
     */
    public static function destroy()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * Set flash message
     *
     * @param string $key Message key (e.g., 'success', 'error')
     * @param string $message Message text
     */
    public static function setFlash($key, $message)
    {
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Get and remove flash message
     *
     * @param string $key Message key
     * @return string|null
     */
    public static function getFlash($key)
    {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }

    /**
     * Get all flash messages and clear them
     *
     * @return array
     */
    public static function getAllFlash()
    {
        $messages = isset($_SESSION['flash']) ? $_SESSION['flash'] : [];
        unset($_SESSION['flash']);
        return $messages;
    }

    /**
     * Generate CSRF token
     *
     * @return string
     */
    public static function generateCsrfToken()
    {
        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(32));
        } else {
            $token = bin2hex(openssl_random_pseudo_bytes(32));
        }

        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Regenerate CSRF token
     *
     * @return string
     */
    public static function regenerateCsrfToken()
    {
        return self::generateCsrfToken();
    }

    /**
     * Get CSRF token
     *
     * @return string
     */
    public static function getCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            return self::generateCsrfToken();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     *
     * @param string $token Token to verify
     * @return bool
     */
    public static function verifyCsrfToken($token)
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

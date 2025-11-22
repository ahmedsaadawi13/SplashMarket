<?php
// FILE: /app/core/Auth.php

/**
 * SplashMarket - Authentication Manager
 *
 * Handles user authentication and session management
 * PHP 7.0+ compatible
 */

class Auth
{
    /**
     * Attempt to login user
     *
     * @param string $email User email
     * @param string $password User password
     * @return bool Success status
     */
    public static function attempt($email, $password)
    {
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return false;
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Check if user is active
        if (isset($user['is_active']) && !$user['is_active']) {
            return false;
        }

        // Store user in session
        self::login($user);

        return true;
    }

    /**
     * Login user (set session)
     *
     * @param array $user User data
     */
    public static function login($user)
    {
        // Remove password from session data
        unset($user['password']);

        Session::set('user', $user);
        Session::set('authenticated', true);

        // Update last login time
        $userModel = new User();
        $userModel->updateLastLogin($user['id']);
    }

    /**
     * Logout current user
     */
    public static function logout()
    {
        Session::destroy();
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public static function check()
    {
        return Session::get('authenticated', false) === true;
    }

    /**
     * Get current authenticated user
     *
     * @return array|null User data or null
     */
    public static function user()
    {
        return Session::get('user');
    }

    /**
     * Get current user ID
     *
     * @return int|null
     */
    public static function id()
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    /**
     * Get current user role
     *
     * @return string|null
     */
    public static function role()
    {
        $user = self::user();
        return $user ? $user['role'] : null;
    }

    /**
     * Get current user's tenant ID
     *
     * @return int|null
     */
    public static function tenantId()
    {
        $user = self::user();
        return $user && isset($user['tenant_id']) ? (int) $user['tenant_id'] : null;
    }

    /**
     * Check if user has specific role
     *
     * @param string|array $roles Role(s) to check
     * @return bool
     */
    public static function hasRole($roles)
    {
        if (!self::check()) {
            return false;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return in_array(self::role(), $roles);
    }

    /**
     * Check if user is platform admin
     *
     * @return bool
     */
    public static function isPlatformAdmin()
    {
        return self::hasRole('platform_admin');
    }

    /**
     * Check if user is tenant admin
     *
     * @return bool
     */
    public static function isTenantAdmin()
    {
        return self::hasRole('tenant_admin');
    }

    /**
     * Check if user is staff
     *
     * @return bool
     */
    public static function isStaff()
    {
        return self::hasRole('staff');
    }
}

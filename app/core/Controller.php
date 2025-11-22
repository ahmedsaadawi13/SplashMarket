<?php
// FILE: /app/core/Controller.php

/**
 * SplashMarket - Base Controller Class
 *
 * Provides common functionality for all controllers
 * PHP 7.0+ compatible
 */

class Controller
{
    protected $view;

    /**
     * Initialize controller
     */
    public function __construct()
    {
        $this->view = new View();
    }

    /**
     * Render a view with data
     *
     * @param string $viewPath Path to view file
     * @param array $data Data to pass to view
     * @param string $layout Layout file to use
     */
    protected function render($viewPath, $data = [], $layout = 'admin')
    {
        $this->view->render($viewPath, $data, $layout);
    }

    /**
     * Return JSON response
     *
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to another URL
     *
     * @param string $url Target URL
     */
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Get POST data with optional default value
     *
     * @param string $key POST key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    protected function post($key, $default = null)
    {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Get GET data with optional default value
     *
     * @param string $key GET key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    protected function get($key, $default = null)
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    protected function isAuthenticated()
    {
        return Auth::check();
    }

    /**
     * Require authentication (redirect if not authenticated)
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            Session::setFlash('error', 'Please login to continue');
            $this->redirect('/login');
        }
    }

    /**
     * Require specific role
     *
     * @param string|array $roles Required role(s)
     */
    protected function requireRole($roles)
    {
        $this->requireAuth();

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $userRole = Auth::user()['role'];

        if (!in_array($userRole, $roles)) {
            Session::setFlash('error', 'Access denied');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Get current tenant ID
     *
     * @return int|null
     */
    protected function getTenantId()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $user = Auth::user();
        return isset($user['tenant_id']) ? (int) $user['tenant_id'] : null;
    }

    /**
     * Verify CSRF token
     *
     * @return bool
     */
    protected function verifyCsrf()
    {
        $token = $this->post('csrf_token');
        return Session::verifyCsrfToken($token);
    }

    /**
     * Require valid CSRF token
     */
    protected function requireCsrf()
    {
        if (!$this->verifyCsrf()) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
}

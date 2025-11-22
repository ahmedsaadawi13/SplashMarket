<?php
// FILE: /app/core/Router.php

/**
 * SplashMarket - URL Router
 *
 * Handles routing of HTTP requests to controllers
 * PHP 7.0+ compatible
 */

class Router
{
    private $routes = [];
    private $namedRoutes = [];

    /**
     * Add a GET route
     *
     * @param string $pattern URL pattern
     * @param string $controller Controller@method
     * @param string|null $name Route name
     */
    public function get($pattern, $controller, $name = null)
    {
        $this->addRoute('GET', $pattern, $controller, $name);
    }

    /**
     * Add a POST route
     *
     * @param string $pattern URL pattern
     * @param string $controller Controller@method
     * @param string|null $name Route name
     */
    public function post($pattern, $controller, $name = null)
    {
        $this->addRoute('POST', $pattern, $controller, $name);
    }

    /**
     * Add a route for any HTTP method
     *
     * @param string $method HTTP method
     * @param string $pattern URL pattern
     * @param string $controller Controller@method
     * @param string|null $name Route name
     */
    private function addRoute($method, $pattern, $controller, $name = null)
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'name' => $name
        ];

        if ($name) {
            $this->namedRoutes[$name] = $pattern;
        }
    }

    /**
     * Dispatch the request to appropriate controller
     *
     * @param string $uri Request URI
     * @param string $method HTTP method
     */
    public function dispatch($uri, $method)
    {
        // Remove query string and trim slashes
        $uri = strtok($uri, '?');
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            // Check if method matches
            if ($route['method'] !== $method) {
                continue;
            }

            // Convert route pattern to regex
            $pattern = $this->convertPatternToRegex($route['pattern']);

            // Check if URI matches pattern
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                return $this->callController($route['controller'], $matches);
            }
        }

        // No route found - 404
        http_response_code(404);
        echo '404 - Page Not Found';
        exit;
    }

    /**
     * Convert route pattern to regex
     *
     * @param string $pattern Route pattern
     * @return string Regex pattern
     */
    private function convertPatternToRegex($pattern)
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);

        // Convert :param to named capture groups
        $pattern = preg_replace('/:\w+/', '([^\/]+)', $pattern);

        // Exact match
        return '/^' . $pattern . '$/';
    }

    /**
     * Call the controller method
     *
     * @param string $controller Controller@method string
     * @param array $params URL parameters
     */
    private function callController($controller, $params = [])
    {
        // Parse controller and method
        list($controllerName, $method) = explode('@', $controller);

        // Build controller class name
        $controllerClass = $controllerName;

        // Check if controller class exists
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller not found: $controllerClass");
        }

        // Instantiate controller
        $controllerInstance = new $controllerClass();

        // Check if method exists
        if (!method_exists($controllerInstance, $method)) {
            throw new Exception("Method not found: $controllerClass::$method");
        }

        // Call method with parameters
        return call_user_func_array([$controllerInstance, $method], $params);
    }

    /**
     * Get URL for named route
     *
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string
     */
    public function url($name, $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Named route not found: $name");
        }

        $pattern = $this->namedRoutes[$name];

        // Replace parameters
        foreach ($params as $key => $value) {
            $pattern = str_replace(":$key", $value, $pattern);
        }

        return $pattern;
    }
}

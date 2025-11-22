<?php
// FILE: /app/core/View.php

/**
 * SplashMarket - View Rendering Engine
 *
 * Handles rendering of views with layouts
 * PHP 7.0+ compatible
 */

class View
{
    private $viewsPath;
    private $layoutsPath;

    /**
     * Initialize view paths
     */
    public function __construct()
    {
        $this->viewsPath = __DIR__ . '/../views/';
        $this->layoutsPath = __DIR__ . '/../views/layouts/';
    }

    /**
     * Render a view file with optional layout
     *
     * @param string $viewPath Path to view file (relative to views directory)
     * @param array $data Data to extract for view
     * @param string|null $layout Layout file name (without .php)
     */
    public function render($viewPath, $data = [], $layout = null)
    {
        // Extract data to make variables available in view
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        $viewFile = $this->viewsPath . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: $viewFile");
        }

        include $viewFile;

        // Get view content
        $content = ob_get_clean();

        // If layout is specified, wrap content in layout
        if ($layout) {
            $layoutFile = $this->layoutsPath . $layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new Exception("Layout file not found: $layoutFile");
            }

            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Render a partial view without layout
     *
     * @param string $partialPath Path to partial view
     * @param array $data Data for the partial
     * @return string Rendered HTML
     */
    public function partial($partialPath, $data = [])
    {
        extract($data);
        ob_start();

        $partialFile = $this->viewsPath . $partialPath . '.php';
        if (file_exists($partialFile)) {
            include $partialFile;
        }

        return ob_get_clean();
    }
}

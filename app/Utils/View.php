<?php

namespace App\Utils;

/**
 * Simple View Renderer - Lightweight template system without external dependencies
 */
class View
{
    private static string $viewsPath;
    private static string $layoutPath;
    private array $data = [];
    private array $sections = [];

    public function __construct()
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(__DIR__, 2) . '/');
        }

        self::$viewsPath = ROOT_PATH . 'app/Views/';
        self::$layoutPath = self::$viewsPath . 'layout.php';
    }

    /**
     * Set view data
     */
    public function with(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Set single data variable
     */
    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Render a view with optional layout
     */
    public function render(string $view, string $layout = null): string
    {
        $viewFile = self::$viewsPath . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewFile}");
        }

        // Extract data to make variables available in view
        extract($this->data);

        // Start output buffering
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // If no layout specified, return content directly
        if ($layout === null || $layout === '') {
            return $content;
        }

        // Use specified layout or default
        $layoutFile = $layout ? self::$viewsPath . $layout . '.php' : self::$layoutPath;
        if (!file_exists($layoutFile)) {
            return $content; // Return content without layout if layout doesn't exist
        }

        // Render layout with content
        ob_start();
        include $layoutFile;
        return ob_get_clean();
    }

    /**
     * Render view and output directly
     */
    public function display(string $view, string $layout = null): void
    {
        echo $this->render($view, $layout);
    }

    /**
     * Start a section for layout
     */
    public function startSection(string $name): void
    {
        $this->sections[$name] = '';
        ob_start();
    }

    /**
     * End a section
     */
    public function endSection(string $name): void
    {
        if (isset($this->sections[$name])) {
            $this->sections[$name] = ob_get_clean();
        }
    }

    /**
     * Get section content
     */
    public function section(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Check if section exists
     */
    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]);
    }

    /**
     * Include a partial view
     */
    public function include(string $partial, array $data = []): void
    {
        $partialFile = self::$viewsPath . 'partials/' . $partial . '.php';
        if (file_exists($partialFile)) {
            extract(array_merge($this->data, $data));
            include $partialFile;
        }
    }

    /**
     * Escape HTML output
     */
    public function escape($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get asset URL
     */
    public function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }

    /**
     * Get current URL
     */
    public function url(string $path = ''): string
    {
        $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Check if current route matches
     */
    public function isActive(string $path): bool
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return $currentPath === '/' . ltrim($path, '/');
    }

    /**
     * Static helper methods
     */
    public static function make(): self
    {
        return new self();
    }

    public static function renderStatic(string $view, array $data = [], string $layout = null): string
    {
        return self::make()->with($data)->render($view, $layout);
    }

    public static function displayStatic(string $view, array $data = [], string $layout = null): void
    {
        echo self::renderStatic($view, $data, $layout);
    }
}

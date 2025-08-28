<?php
/**
 * View Helper Functions
 *
 * These functions provide a simple interface to the View system
 * without needing to instantiate the View class every time.
 */

if (!function_exists('view')) {
    /**
     * Render a view with data
     *
     * @param string $view View name (without .php extension)
     * @param array $data Data to pass to the view
     * @param string|null $layout Layout to use (null for no layout)
     * @return string
     */
    function view(string $view, array $data = [], string $layout = 'layout'): string
    {
        return \App\Utils\View::renderStatic($view, $data, $layout);
    }
}

if (!function_exists('display')) {
    /**
     * Render and display a view
     *
     * @param string $view View name (without .php extension)
     * @param array $data Data to pass to the view
     * @param string|null $layout Layout to use (null for no layout)
     */
    function display(string $view, array $data = [], string $layout = 'layout'): void
    {
        \App\Utils\View::displayStatic($view, $data, $layout);
    }
}

if (!function_exists('escape')) {
    /**
     * Escape HTML output
     *
     * @param mixed $value Value to escape
     * @return string
     */
    function escape($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    /**
     * Get asset URL
     *
     * @param string $path Asset path
     * @return string
     */
    function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('flash')) {
    /**
     * Set a flash message
     *
     * @param string $message Message to display
     * @param string $type Message type (success, error, warning, info)
     */
    function flash(string $message, string $type = 'success'): void
    {
        $flashClass = 'App\\Utils\\Flash';
        if (class_exists($flashClass)) {
            $flashClass::set($message, $type);
        }
    }
}

if (!function_exists('flash_success')) {
    /**
     * Set a success flash message
     */
    function flash_success(string $message): void
    {
        flash($message, 'success');
    }
}

if (!function_exists('flash_error')) {
    /**
     * Set an error flash message
     */
    function flash_error(string $message): void
    {
        flash($message, 'error');
    }
}

if (!function_exists('flash_warning')) {
    /**
     * Set a warning flash message
     */
    function flash_warning(string $message): void
    {
        flash($message, 'warning');
    }
}

if (!function_exists('flash_info')) {
    /**
     * Set an info flash message
     */
    function flash_info(string $message): void
    {
        flash($message, 'info');
    }
}

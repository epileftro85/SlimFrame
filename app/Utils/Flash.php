<?php
namespace App\Utils;

class Flash
{
    /**
     * Set a flash message
     */
    public static function set(string $message, string $type = 'success'): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Get and clear flash message
     */
    public static function get(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Check if there's a flash message
     */
    public static function has(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['flash']);
    }

    /**
     * Set success message
     */
    public static function success(string $message): void
    {
        self::set($message, 'success');
    }

    /**
     * Set error message
     */
    public static function error(string $message): void
    {
        self::set($message, 'error');
    }

    /**
     * Set warning message
     */
    public static function warning(string $message): void
    {
        self::set($message, 'warning');
    }

    /**
     * Set info message
     */
    public static function info(string $message): void
    {
        self::set($message, 'info');
    }
}

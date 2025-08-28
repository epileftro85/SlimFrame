<?php
namespace App\Controllers;

class Controller
{
    protected function isAuthenticated(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $uid = $_SESSION['user_id'] ?? $_SESSION['uid'] ?? null;
        return $uid !== null && (int)$uid > 0;
    }

    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: /', true, 302);
            exit;
        }
    }

    protected function redirectIfAuthenticated(): void
    {
        if ($this->isAuthenticated()) {
            header('Location: /dashboard', true, 302);
            exit;
        }
    }

    protected function out($text)
    {
        echo htmlspecialchars($text);
    }

    protected function set_csrf(int $ttlSeconds = 3600): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_expires'] = time() + $ttlSeconds;

        $escaped = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $escaped . '" />';
    }

    protected function verify_csrf(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $ok = isset($_SESSION['csrf_token'], $_SESSION['csrf_expires'])
            && is_string($token)
            && hash_equals($_SESSION['csrf_token'], $token)
            && time() <= (int)$_SESSION['csrf_expires'];

        // One-time token
        unset($_SESSION['csrf_token'], $_SESSION['csrf_expires']);
        return $ok;
    }
}

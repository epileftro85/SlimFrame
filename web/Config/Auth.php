<?php
namespace App\Config;

use App\Models\User;
use App\Models\UserSession;

final class Auth
{
    private static function secret(): string
    {
        return getenv('APP_KEY') ?: 'dev-only-change-me';
    }

    public static function login(int $userId, bool $remember = false): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id(true); // prevent session fixation
        $_SESSION['uid'] = $userId;

        // Create a persistent session record (token is hashed in DB)
        $raw = bin2hex(random_bytes(32));
        $hash = hash_hmac('sha256', $raw, self::secret());
        $ua   = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
        $exp  = (new \DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s');

        // Persist in DB via tiny ORM
        UserSession::create([
            'user_id'    => $userId,
            'token_hash' => $hash,
            'expires_at' => $exp,
            'ip'         => $ip,
            'user_agent' => substr($ua, 0, 255),
        ]);

        if ($remember) {
            self::setRememberCookie($raw, $exp);
        } else {
            self::clearRememberCookie();
        }
    }

    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        // Revoke current remember token if present
        if (!empty($_COOKIE['auth'])) {
            $hash = hash_hmac('sha256', (string)$_COOKIE['auth'], self::secret());
            $matches = UserSession::where(['token_hash' => $hash], limit: 1);
            if (!empty($matches)) {
                $matches[0]->delete();
            }
        }
        self::clearRememberCookie();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    public static function user(): ?User
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        if (!empty($_SESSION['uid'])) {
            return User::find((int)$_SESSION['uid']);
        }

        // Try remember cookie
        $raw = $_COOKIE['auth'] ?? null;
        if (!$raw) return null;

        $hash = hash_hmac('sha256', (string)$raw, self::secret());
        $sessions = UserSession::where(['token_hash' => $hash], limit: 1);
        $row = $sessions[0] ?? null;
        if (!$row) return null;

        if (strtotime((string)$row->expires_at) < time()) {
            // Expired, revoke
            $row->delete();
            self::clearRememberCookie();
            return null;
        }

        $_SESSION['uid'] = (int)$row->user_id;
        return User::find((int)$row->user_id);
    }

    private static function setRememberCookie(string $rawToken, string $expiresAt): void
    {
        $expTs = strtotime($expiresAt) ?: (time() + 60 * 60 * 24 * 30);
        setcookie('auth', $rawToken, [
            'expires'  => $expTs,
            'path'     => '/',
            'domain'   => '',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function clearRememberCookie(): void
    {
        if (isset($_COOKIE['auth'])) {
            setcookie('auth', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'domain'   => '',
                'secure'   => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            unset($_COOKIE['auth']);
        }
    }
}
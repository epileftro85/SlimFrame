<?php
namespace App\Config\Database;

final class Connection
{
    private static $ENV = null;

    private static function loadEnv()
    {
        if (self::$ENV === null) {
            self::$ENV = parse_ini_file(ROOT_PATH . '.env');
        }
    }

    public static function db(): array
    {
        self::loadEnv();
        return [
            'driver'  => self::$ENV['DB_DRIVER'] ?: 'mysql', // mysql | pgsql | sqlite
            'host'    => self::$ENV['DB_HOST'] ?: 'localhost',
            'port'    => self::$ENV['DB_PORT'] ?: '3306',
            'name'    => self::$ENV['DB_NAME'] ?: 'seoanchor',
            'user'    => self::$ENV['DB_USER'] ?: 'seoanchor',
            'pass'    => self::$ENV['DB_PASSWORD'] ?: 'seoanchor',
            'charset' => self::$ENV['DB_CHARSET'] ?: 'utf8mb4',
            'ssl'     => filter_var(self::$ENV['DB_SSL'] ?: 'false', FILTER_VALIDATE_BOOL),
            // For sqlite, set DB_NAME to absolute path or relative file (e.g., /var/www/html/var/app.db)
        ];
    }
}

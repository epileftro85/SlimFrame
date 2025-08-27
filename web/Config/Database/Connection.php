<?php
namespace App\Config\Database;

final class Connection
{
    public static function db(): array
    {
        return [
            'driver'  => getenv('DB_DRIVER') ?: 'mysql',       // mysql | pgsql | sqlite
            'host'    => getenv('DB_HOST') ?: 'mysql',
            'port'    => getenv('DB_PORT') ?: '3306',
            'name'    => getenv('DB_NAME') ?: 'seoanchor',
            'user'    => getenv('DB_USER') ?: 'seoanchor',
            'pass'    => getenv('DB_PASSWORD') ?: 'seoanchor',
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
            'ssl'     => filter_var(getenv('DB_SSL') ?: 'false', FILTER_VALIDATE_BOOL),
            // For sqlite, set DB_NAME to absolute path or relative file (e.g., /var/www/html/var/app.db)
        ];
    }
}

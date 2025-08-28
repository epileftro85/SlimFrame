<?php
namespace App\Config\Database;

use App\Utils\EnvUtil;

final class Connection
{
    public static function db(): array
    {
        return [
            'driver'  => EnvUtil::get('DB_DRIVER', 'mysql'), // mysql | pgsql | sqlite
            'host'    => EnvUtil::get('DB_HOST', 'localhost'),
            'port'    => EnvUtil::get('DB_PORT', '3306'),
            'name'    => EnvUtil::get('DB_NAME', 'seoanchor'),
            'user'    => EnvUtil::get('DB_USER', 'seoanchor'),
            'pass'    => EnvUtil::get('DB_PASSWORD', 'seoanchor'),
            'charset' => EnvUtil::get('DB_CHARSET', 'utf8mb4'),
            'ssl'     => EnvUtil::get('DB_SSL', 'false'),
            // For sqlite, set DB_NAME to absolute path or relative file (e.g., /var/www/html/var/app.db)
        ];
    }
}

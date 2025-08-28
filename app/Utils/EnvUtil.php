<?php

namespace App\Utils;

class EnvUtil
{
    private static $ENV = null;

    private static function loadEnv()
    {
        if (self::$ENV === null) {
            self::$ENV = parse_ini_file(ROOT_PATH . '.env');
        }
    }

    public static function get(string $key, $default = null)
    {
        self::loadEnv();
        return self::$ENV[$key] ?? $default;
    }
}

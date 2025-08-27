<?php
namespace App\Config\Database;

use App\Config\Database\Connection;

final class Database
{
    private static ?\PDO $pdo = null;

    public static function pdo(): \PDO
    {
        if (self::$pdo instanceof \PDO) {
            return self::$pdo;
        }

        $cfg = Connection::db();
        $driver = strtolower($cfg['driver']);

        switch ($driver) {
            case 'mysql':
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
                );
                $options = [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                // Optional SSL (if provided)
                if (!empty($cfg['ssl'])) {
                    $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                    $options[\PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA') ?: null;
                    $options[\PDO::MYSQL_ATTR_SSL_CERT] = getenv('DB_SSL_CERT') ?: null;
                    $options[\PDO::MYSQL_ATTR_SSL_KEY] = getenv('DB_SSL_KEY') ?: null;
                }
                self::$pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], $options);
                break;

            case 'pgsql':
                $dsn = sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $cfg['host'], $cfg['port'], $cfg['name']
                );
                self::$pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                // Client encoding
                if (!empty($cfg['charset'])) {
                    self::$pdo->exec("SET client_encoding TO '" . str_replace("'", "''", $cfg['charset']) . "'");
                }
                break;

            case 'sqlite':
                // DB_NAME is the file path
                $dsn = 'sqlite:' . $cfg['name'];
                self::$pdo = new \PDO($dsn, null, null, [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                break;

            default:
                throw new \RuntimeException("Unsupported DB driver: {$driver}");
        }

        return self::$pdo;
    }

    public static function begin(): void
    {
        $pdo = self::pdo();
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }
    }

    public static function commit(): void
    {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    }

    public static function rollBack(): void
    {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    public static function driver(): string
    {
        return self::pdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    public static function quoteIdent(string $ident): string
    {
        // Minimal identifier quoting (avoid reserved words)
        $driver = self::driver();
        if ($driver === 'mysql') {
            return '`' . str_replace('`', '``', $ident) . '`';
        }
        if ($driver === 'pgsql') {
            return '"' . str_replace('"', '""', $ident) . '"';
        }
        // sqlite tolerates quotes/backticks
        return '"' . str_replace('"', '""', $ident) . '"';
    }
}
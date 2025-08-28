<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Config\Database\Database;

// Simple CLI migration runner.
// Scans `migrations/` for .sql files, orders them, and runs those not yet applied.

function out(string $s): void
{
    echo $s . PHP_EOL;
}

$migrationsDir = __DIR__ . '/migrations';
if (!is_dir($migrationsDir)) {
    out("Migrations directory not found: {$migrationsDir}");
    exit(1);
}

$pdo = Database::pdo();

// Ensure migrations table exists
$createMigrationsTable = <<<'SQL'
CREATE TABLE IF NOT EXISTS migrations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL UNIQUE,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
SQL;

$pdo->exec($createMigrationsTable);

// Get applied migrations
$stmt = $pdo->query('SELECT name FROM migrations');
$applied = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

// Find sql files
$files = glob($migrationsDir . '/*.sql');
if (!$files) {
    out('No migration files found.');
    exit(0);
}

// Sort files by filename
sort($files, SORT_NATURAL);

$new = [];
foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $applied, true)) {
        continue;
    }
    $new[] = [$name, $file];
}

if (empty($new)) {
    out('All migrations already applied.');
    exit(0);
}

foreach ($new as [$name, $file]) {
    out("Applying: {$name}");
    $sql = file_get_contents($file);
    if ($sql === false) {
        out("Failed to read {$file}");
        exit(1);
    }

    // Basic safety: do not run DROP TABLE or CREATE ... WITHOUT IF NOT EXISTS that would override.
    $lower = strtolower($sql);
    if (strpos($lower, 'drop table') !== false) {
        out('Migration contains DROP TABLE; skipping for safety: ' . $name);
        continue;
    }

    // If CREATE TABLE lacks IF NOT EXISTS, we try to transform it for safety (MySQL/Postgres/SQLite differ).
    // Simple heuristic: replace "create table `name` (" with "create table if not exists `name` ("
    $sqlSafe = preg_replace('/create\s+table\s+(if\s+not\s+exists\s+)?/i', 'CREATE TABLE IF NOT EXISTS ', $sql, 1);

    try {
        Database::begin();
        $pdo->exec($sqlSafe);
        $insert = $pdo->prepare('INSERT INTO migrations (name) VALUES (:name)');
        $insert->execute([':name' => $name]);
        Database::commit();
        out('Applied: ' . $name);
    } catch (Throwable $e) {
        Database::rollBack();
        out('Failed: ' . $e->getMessage());
        exit(1);
    }
}

out('Migrations complete.');

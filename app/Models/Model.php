<?php
namespace App\Models;

use App\Config\Database\Database;

abstract class Model
{
    // Override in child classes
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];  // whitelist for mass-assign
    protected static bool $timestamps = false; // expects created_at, updated_at DATETIME

    protected array $attributes = [];

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            $this->fill($attrs); // mass-assign only allowed fields on input
        }
    }

    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public static function find($id): ?static
    {
        $pdo = Database::pdo();
        $table = Database::quoteIdent(static::table());
        $pk = Database::quoteIdent(static::$primaryKey);
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE {$pk} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? static::fromRow($row) : null;
    }

    public static function all(int $limit = 100, int $offset = 0): array
    {
        $pdo = Database::pdo();
        $table = Database::quoteIdent(static::table());
        $sql = "SELECT * FROM {$table} LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map(fn($r) => static::fromRow($r), $rows);
    }

    public static function where(array $where, array $orderBy = [], ?int $limit = null, ?int $offset = null): array
    {
        $pdo = Database::pdo();
        $table = Database::quoteIdent(static::table());

        $clauses = [];
        $params = [];
        foreach ($where as $col => $val) {
            $ph = ':w_' . preg_replace('/\W+/', '_', $col);
            $clauses[] = Database::quoteIdent($col) . " = {$ph}";
            $params[$ph] = $val;
        }
        $sql = "SELECT * FROM {$table}";
        if ($clauses) {
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }
        if ($orderBy) {
            $parts = [];
            foreach ($orderBy as $col => $dir) {
                $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
                $parts[] = Database::quoteIdent($col) . " {$dir}";
            }
            $sql .= " ORDER BY " . implode(', ', $parts);
        }
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        if ($offset !== null) {
            $sql .= " OFFSET " . (int)$offset;
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $ph => $val) {
            $stmt->bindValue($ph, $val);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map(fn($r) => static::fromRow($r), $rows);
    }

    public static function create(array $data): static
    {
        $pdo = Database::pdo();
        $table = Database::quoteIdent(static::table());
        $data = static::filterFillable($data);
        if (static::$timestamps) {
            $now = date('Y-m-d H:i:s');
            $data['created_at'] = $data['created_at'] ?? $now;
            $data['updated_at'] = $data['updated_at'] ?? $now;
        }

        $cols = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $cols);
        $colsQuoted = implode(', ', array_map([Database::class, 'quoteIdent'], $cols));
        $sql = "INSERT INTO {$table} ({$colsQuoted}) VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        foreach ($data as $c => $v) {
            $stmt->bindValue(':' . $c, $v);
        }

        Database::begin();
        try {
            $stmt->execute();
            $id = static::lastInsertId();
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }

        $pk = static::$primaryKey;
        $row = $data;
        if (!isset($row[$pk]) && $id !== null) {
            $row[$pk] = is_numeric($id) ? (int)$id : $id;
        }
        // Hydrate from DB row without filtering so PK and all columns are kept
        return static::fromRow($row);
    }

    public function update(array $data): bool
    {
        $pdo = Database::pdo();
        $table = Database::quoteIdent(static::table());
        $pk = static::$primaryKey;

        $data = static::filterFillable($data);
        if (static::$timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $sets = [];
        foreach ($data as $c => $_) {
            $sets[] = Database::quoteIdent($c) . ' = :' . $c;
        }

        if (!$sets) {
            return true;
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE " . Database::quoteIdent($pk) . " = :pk";
        $stmt = $pdo->prepare($sql);
        foreach ($data as $c => $v) {
            $stmt->bindValue(':' . $c, $v);
        }
        $stmt->bindValue(':pk', $this->attributes[$pk]);
        $ok = $stmt->execute();

        if ($ok) {
            // Merge updated values into attributes
            foreach ($data as $k => $v) {
                $this->attributes[$k] = $v;
            }
        }
        return $ok;
    }

    public function delete(): bool
    {
        $pdo = Database::pdo();
        $table = Database::quoteIdent(static::table());
        $pk = static::$primaryKey;
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE " . Database::quoteIdent($pk) . " = :pk");
        return $stmt->execute([':pk' => $this->attributes[$pk]]);
    }

    public function save(): bool
    {
        $pk = static::$primaryKey;
        if (!empty($this->attributes[$pk])) {
            return $this->update($this->attributes);
        }
        $created = static::create($this->attributes);
        $this->attributes = $created->toArray();
        return true;
    }

    protected function fill(array $attrs): void
    {
        foreach (static::filterFillable($attrs) as $k => $v) {
            $this->attributes[$k] = $v;
        }
    }

    protected static function filterFillable(array $data): array
    {
        if (empty(static::$fillable)) {
            return $data; // if not defined, allow all (or tighten as needed)
        }
        $out = [];
        foreach ($data as $k => $v) {
            if (in_array($k, static::$fillable, true)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    protected static function fromRow(array $row): static
    {
        // Hydrate raw DB row, bypassing fillable filter so PK and all columns remain
        $obj = new static();
        $obj->attributes = $row;
        return $obj;
    }

    protected static function table(): string
    {
        if (static::$table === '') {
            throw new \LogicException('Model::$table must be set on ' . static::class);
        }
        return static::$table;
    }

    protected static function lastInsertId(): ?string
    {
        $pdo = Database::pdo();
        return $pdo->lastInsertId();
    }
}
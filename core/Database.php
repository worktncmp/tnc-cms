<?php

declare(strict_types=1);

namespace Core;

final class Database
{
    private \PDO $pdo;

    /** @param array<string, mixed> $config */
    public function __construct(array $config)
    {
        $driver = (string) ($config['driver'] ?? 'sqlite');
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if ($driver === 'sqlite') {
            $path = (string) ($config['path'] ?? ':memory:');
            if ($path !== ':memory:') {
                $directory = dirname($path);
                if ($directory !== '' && $directory !== '.' && !is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
            }
            $this->pdo = new \PDO('sqlite:' . $path, null, null, $options);
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            return;
        }

        if ($driver !== 'mysql') {
            throw new \RuntimeException('Unsupported database driver: ' . $driver);
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            (string) ($config['host'] ?? '127.0.0.1'),
            (string) ($config['port'] ?? '3306'),
            (string) ($config['name'] ?? ''),
        );

        $this->pdo = new \PDO(
            $dsn,
            (string) ($config['user'] ?? ''),
            (string) ($config['pass'] ?? ''),
            $options,
        );
    }

    public function pdo(): \PDO
    {
        return $this->pdo;
    }

    /** @param array<int|string, mixed> $params */
    public function execute(string $sql, array $params = []): \PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /** @param array<int|string, mixed> $params */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        return $this->execute($sql, $params);
    }

    /**
     * @param array<int|string, mixed> $params
     * @return array<string, mixed>|null
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->execute($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @param array<int|string, mixed> $params
     * @return list<array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params)->fetchAll();
    }

    /** @param array<string, mixed> $data */
    public function insert(string $table, array $data): string
    {
        $this->assertIdentifier($table);
        $columns = array_keys($data);
        foreach ($columns as $column) {
            $this->assertIdentifier($column);
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $quoted = implode(', ', $columns);
        $this->execute(
            "INSERT INTO {$table} ({$quoted}) VALUES ({$placeholders})",
            array_values($data),
        );

        return $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int|string, mixed> $params
     */
    public function update(string $table, array $data, string $where, array $params = []): int
    {
        $this->assertIdentifier($table);
        $sets = [];
        $values = [];
        foreach ($data as $column => $value) {
            $this->assertIdentifier($column);
            $sets[] = $column . ' = ?';
            $values[] = $value;
        }

        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
        $statement = $this->execute($sql, array_merge($values, $params));

        return $statement->rowCount();
    }

    /** @param array<int|string, mixed> $params */
    public function delete(string $table, string $where, array $params = []): int
    {
        $this->assertIdentifier($table);
        $statement = $this->execute('DELETE FROM ' . $table . ' WHERE ' . $where, $params);

        return $statement->rowCount();
    }

    public function begin(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $this->begin();
        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    private function assertIdentifier(string $name): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new \InvalidArgumentException('Invalid SQL identifier.');
        }
    }
}

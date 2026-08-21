<?php

declare(strict_types=1);

namespace Core;

abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct(protected Database $db)
    {
        if ($this->table === '') {
            throw new \RuntimeException(static::class . ' must define $table.');
        }
        $this->assertIdentifier($this->table);
        $this->assertIdentifier($this->primaryKey);
    }

    /** @return array<string, mixed>|null */
    public function find(int|string $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id],
        );
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }

    private function assertIdentifier(string $name): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new \InvalidArgumentException('Invalid SQL identifier.');
        }
    }
}

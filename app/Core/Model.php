<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;

/**
 * Table gateway shared by every content model.
 *
 * Column names used in generated SQL are always intersected with $fillable (or
 * the concrete model's own whitelist), so a crafted form field can never reach
 * the query as an identifier.
 */
abstract class Model
{
    protected string $table;

    protected string $primaryKey = 'id';

    /** @var array<int, string> Columns writable through create()/update(). */
    protected array $fillable = [];

    protected ?string $orderBy = null;

    protected string $orderDirection = 'ASC';

    public function table(): string
    {
        return $this->table;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return Database::select("SELECT * FROM `{$this->table}`" . $this->orderClause());
    }

    /**
     * Rows flagged visible on the public site, in display order.
     *
     * @return array<int, array<string, mixed>>
     */
    public function published(): array
    {
        if (!$this->hasColumn('is_published')) {
            return $this->all();
        }

        return Database::select(
            "SELECT * FROM `{$this->table}` WHERE is_published = 1" . $this->orderClause()
        );
    }

    public function find(int $id): ?array
    {
        return Database::selectOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id",
            ['id' => $id]
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): int
    {
        $attributes = $this->filterFillable($attributes);

        if ($attributes === []) {
            throw new InvalidArgumentException('No writable attributes supplied.');
        }

        $columns      = array_keys($attributes);
        $columnList   = '`' . implode('`, `', $columns) . '`';
        $placeholders = ':' . implode(', :', $columns);

        return Database::insert(
            "INSERT INTO `{$this->table}` ({$columnList}) VALUES ({$placeholders})",
            $attributes
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(int $id, array $attributes): bool
    {
        $attributes = $this->filterFillable($attributes);

        if ($attributes === []) {
            return false;
        }

        $assignments = implode(', ', array_map(
            static fn (string $column): string => "`{$column}` = :{$column}",
            array_keys($attributes)
        ));

        $attributes['__id'] = $id;

        Database::execute(
            "UPDATE `{$this->table}` SET {$assignments} WHERE `{$this->primaryKey}` = :__id",
            $attributes
        );

        return true;
    }

    public function delete(int $id): bool
    {
        return Database::execute(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id",
            ['id' => $id]
        ) > 0;
    }

    public function count(): int
    {
        $row = Database::selectOne("SELECT COUNT(*) AS total FROM `{$this->table}`");

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Persist a drag-and-drop ordering.
     *
     * @param array<int, int> $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        if (!$this->hasColumn('sort_order')) {
            return;
        }

        Database::transaction(function () use ($orderedIds): void {
            foreach (array_values($orderedIds) as $position => $id) {
                Database::execute(
                    "UPDATE `{$this->table}` SET sort_order = :position WHERE `{$this->primaryKey}` = :id",
                    ['position' => $position, 'id' => (int) $id]
                );
            }
        });
    }

    /**
     * Next free position, so new records land at the end of the list.
     */
    public function nextSortOrder(): int
    {
        if (!$this->hasColumn('sort_order')) {
            return 0;
        }

        $row = Database::selectOne("SELECT COALESCE(MAX(sort_order), -1) + 1 AS next FROM `{$this->table}`");

        return (int) ($row['next'] ?? 0);
    }

    public function hasColumn(string $column): bool
    {
        return in_array($column, $this->columns(), true);
    }

    /**
     * @return array<int, string>
     */
    protected function columns(): array
    {
        return array_merge($this->fillable, [$this->primaryKey, 'created_at', 'updated_at']);
    }

    /**
     * @param  array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    protected function filterFillable(array $attributes): array
    {
        return array_intersect_key($attributes, array_flip($this->fillable));
    }

    private function orderClause(): string
    {
        if ($this->orderBy === null) {
            return '';
        }

        // $orderBy/$orderDirection are class constants in code, never user input.
        $direction = strtoupper($this->orderDirection) === 'DESC' ? 'DESC' : 'ASC';

        return " ORDER BY `{$this->orderBy}` {$direction}, `{$this->primaryKey}` ASC";
    }
}

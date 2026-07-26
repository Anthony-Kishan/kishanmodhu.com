<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;

/**
 * Read-side API over config/content_types.php.
 *
 * Wrapping the raw array here means the generic admin controller and views
 * never touch config structure directly, so the registry format can evolve in
 * one place.
 */
final class ContentType
{
    /**
     * @param array<string, mixed> $definition
     */
    private function __construct(
        public readonly string $key,
        private readonly array $definition
    ) {
    }

    public static function find(string $key): self
    {
        $types = Config::get('content_types');

        if (!isset($types[$key])) {
            throw new InvalidArgumentException("Unknown content type [{$key}].");
        }

        return new self($key, $types[$key]);
    }

    public static function exists(string $key): bool
    {
        return isset(Config::get('content_types')[$key]);
    }

    /**
     * @return array<int, self>
     */
    public static function all(): array
    {
        return array_map(
            static fn (string $key): self => self::find($key),
            array_keys(Config::get('content_types'))
        );
    }

    public function label(): string
    {
        return $this->definition['label'];
    }

    public function singular(): string
    {
        return $this->definition['singular'];
    }

    public function description(): string
    {
        return $this->definition['description'] ?? '';
    }

    public function icon(): string
    {
        return $this->definition['icon'] ?? 'file';
    }

    public function model(): Model
    {
        $class = $this->definition['model'];

        return new $class();
    }

    public function isSortable(): bool
    {
        return (bool) ($this->definition['sortable'] ?? false);
    }

    public function isPublishable(): bool
    {
        return (bool) ($this->definition['publishable'] ?? false);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function fields(): array
    {
        return $this->definition['fields'];
    }

    /**
     * @return array<int, string>
     */
    public function listColumns(): array
    {
        return $this->definition['list_columns'] ?? array_slice(array_keys($this->fields()), 0, 3);
    }

    /**
     * Validation rules keyed by field name, in Validator's format.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->fields() as $name => $field) {
            if (($field['rules'] ?? []) !== []) {
                $rules[$name] = $field['rules'];
            }
        }

        return $rules;
    }

    /**
     * Human labels keyed by field name, for validation messages.
     *
     * @return array<string, string>
     */
    public function labels(): array
    {
        return array_map(
            static fn (array $field): string => $field['label'],
            $this->fields()
        );
    }

    public function fieldType(string $name): string
    {
        return $this->fields()[$name]['type'] ?? 'text';
    }

    public function adminUrl(string $suffix = ''): string
    {
        return '/admin/content/' . $this->key . $suffix;
    }
}

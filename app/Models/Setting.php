<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Key/value store for singleton site content (headings, bios, meta tags).
 *
 * The whole table is small and read on nearly every request, so it is loaded
 * once per request and served from a static cache thereafter.
 */
final class Setting
{
    /** @var array<string, string>|null */
    private static ?array $cache = null;

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $rows  = Database::select('SELECT setting_key, setting_value FROM settings');
        $pairs = [];

        foreach ($rows as $row) {
            $pairs[$row['setting_key']] = (string) $row['setting_value'];
        }

        return self::$cache = $pairs;
    }

    public function get(string $key, string $default = ''): string
    {
        $value = $this->all()[$key] ?? $default;

        return $value === '' ? $default : $value;
    }

    /**
     * Upsert a batch of settings in one transaction.
     *
     * @param array<string, string> $values
     */
    public function setMany(array $values): void
    {
        Database::transaction(static function () use ($values): void {
            foreach ($values as $key => $value) {
                Database::execute(
                    'INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
                    ['key' => $key, 'value' => $value]
                );
            }
        });

        self::$cache = null;
    }

    public static function flushCache(): void
    {
        self::$cache = null;
    }
}

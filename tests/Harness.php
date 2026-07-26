<?php

declare(strict_types=1);

namespace Tests;

/**
 * Minimal assertion harness.
 *
 * Deliberately tiny — the suites here are smoke tests that need no database, so
 * pulling in PHPUnit would add a Composer dependency for very little gain.
 */
final class Harness
{
    private static int $passed = 0;

    /** @var array<int, string> */
    private static array $failures = [];

    private static string $group = '';

    public static function group(string $name): void
    {
        self::$group = $name;
        echo PHP_EOL . '  ' . $name . PHP_EOL;
    }

    public static function check(string $label, bool $ok, string $detail = ''): void
    {
        if ($ok) {
            self::$passed++;

            if (getenv('VERBOSE') !== false) {
                echo '    ok   ' . $label . PHP_EOL;
            }

            return;
        }

        $message = trim(self::$group . ' / ' . $label . ($detail !== '' ? " ({$detail})" : ''));
        self::$failures[] = $message;
        echo '    FAIL ' . $label . ($detail !== '' ? "  {$detail}" : '') . PHP_EOL;
    }

    public static function passed(): int
    {
        return self::$passed;
    }

    /**
     * @return array<int, string>
     */
    public static function failures(): array
    {
        return self::$failures;
    }

    /**
     * Print the tally and return the process exit code.
     */
    public static function summary(): int
    {
        $failed = count(self::$failures);

        echo PHP_EOL . str_repeat('─', 56) . PHP_EOL;

        if ($failed === 0) {
            echo sprintf('  %d passed', self::$passed) . PHP_EOL;
        } else {
            echo sprintf('  %d passed, %d FAILED', self::$passed, $failed) . PHP_EOL . PHP_EOL;

            foreach (self::$failures as $failure) {
                echo '    · ' . $failure . PHP_EOL;
            }
        }

        echo str_repeat('─', 56) . PHP_EOL;

        return $failed === 0 ? 0 : 1;
    }
}

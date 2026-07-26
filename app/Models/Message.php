<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Contact-form submissions, replacing the previous third-party form endpoint.
 */
final class Message extends Model
{
    protected string $table = 'messages';

    protected array $fillable = [
        'first_name', 'last_name', 'email', 'company_type', 'budget', 'body',
        'ip_address', 'user_agent', 'is_read',
    ];

    protected ?string $orderBy = 'created_at';

    protected string $orderDirection = 'DESC';

    public function unreadCount(): int
    {
        $row = Database::selectOne('SELECT COUNT(*) AS total FROM messages WHERE is_read = 0');

        return (int) ($row['total'] ?? 0);
    }

    public function markRead(int $id): void
    {
        Database::execute('UPDATE messages SET is_read = 1 WHERE id = :id', ['id' => $id]);
    }

    public function markAllRead(): void
    {
        Database::execute('UPDATE messages SET is_read = 1 WHERE is_read = 0');
    }

    /**
     * Count submissions from one IP within the given window, for rate limiting.
     */
    public function recentCountForIp(string $ip, int $seconds): int
    {
        // MySQL will not accept a placeholder inside INTERVAL, so the window is
        // cast to an int and inlined. It never originates from user input.
        $window = max(1, $seconds);

        $row = Database::selectOne(
            "SELECT COUNT(*) AS total FROM messages
             WHERE ip_address = :ip AND created_at > (NOW() - INTERVAL {$window} SECOND)",
            ['ip' => $ip]
        );

        return (int) ($row['total'] ?? 0);
    }
}

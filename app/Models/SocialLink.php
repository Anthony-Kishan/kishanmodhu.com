<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class SocialLink extends Model
{
    protected string $table = 'social_links';

    protected array $fillable = ['label', 'url', 'icon_path', 'show_in_about', 'sort_order', 'is_published'];

    protected ?string $orderBy = 'sort_order';

    /**
     * Links flagged for the icon row in the ABOUT section.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forAboutSection(): array
    {
        return Database::select(
            "SELECT * FROM `{$this->table}` WHERE is_published = 1 AND show_in_about = 1 ORDER BY sort_order ASC, id ASC"
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class MarqueeLogo extends Model
{
    protected string $table = 'marquee_logos';

    protected array $fillable = ['name', 'logo_path', 'sort_order', 'is_published'];

    protected ?string $orderBy = 'sort_order';
}

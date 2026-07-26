<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Work extends Model
{
    protected string $table = 'works';

    protected array $fillable = [
        'title', 'category', 'tag', 'image_path', 'image_alt', 'url', 'sort_order', 'is_published',
    ];

    protected ?string $orderBy = 'sort_order';
}

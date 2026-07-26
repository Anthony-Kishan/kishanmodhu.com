<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Experience extends Model
{
    protected string $table = 'experiences';

    protected array $fillable = [
        'company', 'position', 'description', 'date_label', 'date_label_short',
        'logo_path', 'sort_order', 'is_published',
    ];

    protected ?string $orderBy = 'sort_order';
}

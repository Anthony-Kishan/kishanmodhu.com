<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Stack extends Model
{
    protected string $table = 'stacks';

    protected array $fillable = [
        'name', 'category', 'proficiency', 'description', 'logo_path', 'sort_order', 'is_published',
    ];

    protected ?string $orderBy = 'sort_order';
}

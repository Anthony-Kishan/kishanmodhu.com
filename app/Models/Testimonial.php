<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Testimonial extends Model
{
    protected string $table = 'testimonials';

    protected array $fillable = [
        'name', 'body', 'country', 'date_label', 'avatar_path', 'source_icon', 'sort_order', 'is_published',
    ];

    protected ?string $orderBy = 'sort_order';
}

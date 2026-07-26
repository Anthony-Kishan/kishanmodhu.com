<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Certificate extends Model
{
    protected string $table = 'certificates';

    protected array $fillable = ['title', 'year', 'url', 'sort_order', 'is_published'];

    protected ?string $orderBy = 'sort_order';
}

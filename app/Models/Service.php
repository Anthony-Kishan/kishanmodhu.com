<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Service extends Model
{
    protected string $table = 'services';

    protected array $fillable = [
        'title', 'description', 'starting_cost', 'features', 'image_path', 'sort_order', 'is_published',
    ];

    protected ?string $orderBy = 'sort_order';

    /**
     * Decode the JSON `features` column so views can iterate it directly.
     *
     * @return array<int, array<string, mixed>>
     */
    public function published(): array
    {
        return array_map(
            static function (array $row): array {
                $row['features'] = json_decode((string) $row['features'], true) ?: [];

                return $row;
            },
            parent::published()
        );
    }
}

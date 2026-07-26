<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Registry of files uploaded through the admin media library.
 *
 * Images that shipped with the original site live under assets/images and are
 * listed separately by MediaLibrary, so they stay pickable without being
 * duplicated into the database.
 */
final class Media extends Model
{
    protected string $table = 'media';

    protected array $fillable = ['filename', 'path', 'mime_type', 'size_bytes', 'width', 'height', 'uploaded_by'];

    protected ?string $orderBy = 'created_at';

    protected string $orderDirection = 'DESC';

    public function findByPath(string $path): ?array
    {
        return Database::selectOne('SELECT * FROM media WHERE path = :path', ['path' => $path]);
    }
}

<?php

declare(strict_types=1);

use App\Core\Env;

$root = dirname(__DIR__);

return [
    'name'  => Env::get('APP_NAME', 'Kishan Modhu'),
    'env'   => Env::get('APP_ENV', 'production'),
    'debug' => (bool) Env::get('APP_DEBUG', false),
    'url'   => rtrim((string) Env::get('APP_URL', 'http://localhost:8000'), '/'),

    'timezone' => Env::get('APP_TIMEZONE', 'Asia/Dhaka'),

    // Absolute paths.
    'root_path'   => $root,
    'public_path' => $root . '/public',
    'upload_path' => $root . '/public/assets/uploads',
    'log_path'    => $root . '/storage/logs',

    // Log an admin out after this many seconds of inactivity. 0 disables.
    'session_idle_timeout' => (int) Env::get('SESSION_IDLE_TIMEOUT', 7200),

    'max_upload_bytes' => (int) Env::get('MAX_UPLOAD_BYTES', 5 * 1024 * 1024),

    // Public pages are cached to disk for this many seconds. 0 disables caching.
    'page_cache_ttl' => (int) Env::get('PAGE_CACHE_TTL', 300),
];

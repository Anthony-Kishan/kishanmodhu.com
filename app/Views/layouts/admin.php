<?php
/**
 * Admin shell: sidebar, flash messages, content well.
 *
 * @var string $content
 * @var string $pageTitle
 */

use App\Core\Auth;
use App\Core\ContentType;
use App\Core\View;
use App\Models\Message;

$user        = Auth::user();
$currentPath = $_SERVER['REQUEST_URI'] ?? '/admin';
$unread      = (new Message())->unreadCount();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle ?? 'Admin') ?> · Kishan CMS</title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>

<body>
    <input type="checkbox" id="nav-toggle" class="nav-toggle" hidden>

    <div class="shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <a href="/admin">Kishan CMS</a>
                <label for="nav-toggle" class="nav-close" aria-label="Close menu">&times;</label>
            </div>

            <nav class="sidebar-nav">
                <a href="/admin" class="<?= $currentPath === '/admin' ? 'is-active' : '' ?>">Dashboard</a>

                <p class="sidebar-heading">Content</p>
                <?php foreach (ContentType::all() as $type): ?>
                    <a href="<?= e($type->adminUrl()) ?>" class="<?= str_starts_with($currentPath, $type->adminUrl()) ? 'is-active' : '' ?>">
                        <?= e($type->label()) ?>
                    </a>
                <?php endforeach; ?>

                <p class="sidebar-heading">Site</p>
                <a href="/admin/settings" class="<?= str_starts_with($currentPath, '/admin/settings') ? 'is-active' : '' ?>">Settings</a>
                <a href="/admin/media" class="<?= str_starts_with($currentPath, '/admin/media') ? 'is-active' : '' ?>">Media</a>
                <a href="/admin/messages" class="<?= str_starts_with($currentPath, '/admin/messages') ? 'is-active' : '' ?>">
                    Messages<?php if ($unread > 0): ?><span class="badge"><?= $unread ?></span><?php endif; ?>
                </a>
                <?php if (Auth::isAdmin()): ?>
                    <a href="/admin/users" class="<?= str_starts_with($currentPath, '/admin/users') ? 'is-active' : '' ?>">Users</a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="/" target="_blank" rel="noopener">View site &rarr;</a>
                <form method="POST" action="/admin/logout">
                    <?= csrf_field() ?>
                    <button type="submit" class="link-button">Sign out</button>
                </form>
            </div>
        </aside>

        <div class="main">
            <header class="topbar">
                <label for="nav-toggle" class="nav-open" aria-label="Open menu">&#9776;</label>
                <h1><?= e($pageTitle ?? 'Admin') ?></h1>
                <span class="topbar-user"><?= e($user['name'] ?? '') ?> · <?= e($user['role'] ?? '') ?></span>
            </header>

            <div class="content">
                <?= View::partial('partials/flash') ?>
                <?= $content ?>
            </div>
        </div>
    </div>

    <script src="<?= e(asset('assets/js/admin.js')) ?>"></script>
</body>

</html>

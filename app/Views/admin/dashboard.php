<?php
/**
 * @var array<string, array{type: App\Core\ContentType, total: int}> $counts
 * @var int $messageTotal
 * @var int $unreadMessages
 */
?>
<div class="card-grid">
    <a class="stat-card" href="/admin/messages">
        <span class="stat-value"><?= (int) $messageTotal ?></span>
        <span class="stat-label">Messages</span>
        <?php if ($unreadMessages > 0): ?>
            <span class="stat-note"><?= (int) $unreadMessages ?> unread</span>
        <?php endif; ?>
    </a>

    <?php foreach ($counts as $entry): ?>
        <a class="stat-card" href="<?= e($entry['type']->adminUrl()) ?>">
            <span class="stat-value"><?= (int) $entry['total'] ?></span>
            <span class="stat-label"><?= e($entry['type']->label()) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<section class="panel">
    <h2>Adding a new content type</h2>
    <p>
        Content types are declared in <code>config/content_types.php</code>. Add a table to
        <code>database/schema.sql</code>, a model in <code>app/Models</code>, and an entry to that
        registry — the list screen, forms, validation, ordering and publish toggle are generated
        from it. No new controller or view is required.
    </p>
</section>

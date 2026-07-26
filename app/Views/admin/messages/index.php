<?php
/**
 * @var array<int, array> $records
 * @var int               $unread
 */
?>
<div class="page-actions">
    <p class="page-description">Submissions from the contact form.</p>
    <?php if ($unread > 0): ?>
        <form method="POST" action="/admin/messages/read-all">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost">Mark all read</button>
        </form>
    <?php endif; ?>
</div>

<?php if ($records === []): ?>
    <div class="empty-state">
        <p>No messages yet.</p>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Budget</th>
                    <th>Received</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr class="<?= (int) $record['is_read'] === 0 ? 'is-unread' : '' ?>">
                        <td>
                            <a href="/admin/messages/<?= (int) $record['id'] ?>" class="cell-link">
                                <?= e($record['first_name'] . ' ' . $record['last_name']) ?>
                            </a>
                            <?php if ((int) $record['is_read'] === 0): ?>
                                <span class="pill pill-on">New</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($record['email']) ?></td>
                        <td><?= e($record['company_type']) ?></td>
                        <td><?= e($record['budget']) ?></td>
                        <td><?= e(date('j M Y, H:i', strtotime((string) $record['created_at']))) ?></td>
                        <td class="col-actions">
                            <a href="/admin/messages/<?= (int) $record['id'] ?>" class="btn btn-ghost btn-sm">Read</a>
                            <form method="POST" action="/admin/messages/<?= (int) $record['id'] ?>/delete" data-confirm="Delete this message?">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

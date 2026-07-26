<?php
/**
 * Generic list screen for any registered content type.
 *
 * @var App\Core\ContentType    $contentType
 * @var array<int, array>       $records
 */

$columns = $contentType->listColumns();
$fields  = $contentType->fields();
?>
<div class="page-actions">
    <p class="page-description"><?= e($contentType->description()) ?></p>
    <a href="<?= e($contentType->adminUrl('/create')) ?>" class="btn btn-primary">New <?= e(strtolower($contentType->singular())) ?></a>
</div>

<?php if ($records === []): ?>
    <div class="empty-state">
        <p>No <?= e(strtolower($contentType->label())) ?> yet.</p>
        <a href="<?= e($contentType->adminUrl('/create')) ?>" class="btn btn-primary">Create the first one</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table"<?= $contentType->isSortable() ? ' data-sortable data-reorder-url="' . e($contentType->adminUrl('/reorder')) . '"' : '' ?>>
            <thead>
                <tr>
                    <?php if ($contentType->isSortable()): ?>
                        <th class="col-handle" aria-label="Reorder"></th>
                    <?php endif; ?>
                    <?php foreach ($columns as $column): ?>
                        <th><?= e($fields[$column]['label'] ?? ucfirst($column)) ?></th>
                    <?php endforeach; ?>
                    <?php if ($contentType->isPublishable()): ?>
                        <th>Visible</th>
                    <?php endif; ?>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr data-id="<?= (int) $record['id'] ?>">
                        <?php if ($contentType->isSortable()): ?>
                            <td class="col-handle"><span class="drag-handle" title="Drag to reorder">⠿</span></td>
                        <?php endif; ?>

                        <?php foreach ($columns as $index => $column): ?>
                            <td>
                                <?php if ($index === 0): ?>
                                    <a href="<?= e($contentType->adminUrl('/' . (int) $record['id'] . '/edit')) ?>" class="cell-link">
                                        <?= e((string) ($record[$column] ?? '')) ?>
                                    </a>
                                <?php else: ?>
                                    <?= e((string) ($record[$column] ?? '')) ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>

                        <?php if ($contentType->isPublishable()): ?>
                            <td>
                                <form method="POST" action="<?= e($contentType->adminUrl('/' . (int) $record['id'] . '/toggle')) ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="pill <?= (int) $record['is_published'] === 1 ? 'pill-on' : 'pill-off' ?>">
                                        <?= (int) $record['is_published'] === 1 ? 'Visible' : 'Hidden' ?>
                                    </button>
                                </form>
                            </td>
                        <?php endif; ?>

                        <td class="col-actions">
                            <a href="<?= e($contentType->adminUrl('/' . (int) $record['id'] . '/edit')) ?>" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="POST" action="<?= e($contentType->adminUrl('/' . (int) $record['id'] . '/delete')) ?>"
                                  data-confirm="Delete this <?= e(strtolower($contentType->singular())) ?>? This cannot be undone.">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($contentType->isSortable()): ?>
        <p class="table-note">Drag the handle to reorder. The new order saves automatically.</p>
    <?php endif; ?>
<?php endif; ?>

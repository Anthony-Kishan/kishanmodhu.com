<?php
/**
 * Generic create/edit form for any registered content type.
 *
 * @var App\Core\ContentType      $contentType
 * @var array<string, mixed>|null $record
 * @var array<string, array>      $mediaGroups
 * @var string                    $action
 */

use App\Core\View;
?>
<form method="POST" action="<?= e($action) ?>" class="form-panel">
    <?= csrf_field() ?>

    <?php foreach ($contentType->fields() as $name => $field): ?>
        <?php
        // Re-populate from the failed submission first, then the stored record.
        $value = old($name, $record[$name] ?? '');
        ?>
        <?= View::partial('partials/field', [
            'name'        => $name,
            'field'       => $field,
            'value'       => $value,
            'mediaGroups' => $mediaGroups,
        ]) ?>
    <?php endforeach; ?>

    <?php if ($contentType->isPublishable()): ?>
        <div class="field">
            <label class="switch">
                <input type="checkbox" name="is_published" value="1"<?= ($record === null || (int) $record['is_published'] === 1) ? ' checked' : '' ?>>
                <span>Visible on the site</span>
            </label>
        </div>
    <?php endif; ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $record === null ? 'Create' : 'Save changes' ?></button>
        <a href="<?= e($contentType->adminUrl()) ?>" class="btn btn-ghost">Cancel</a>
    </div>
</form>

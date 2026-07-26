<?php
/**
 * @var string                $groupKey
 * @var array<string, array>  $groups
 * @var array<string, mixed>  $definition
 * @var array<string, string> $values
 * @var array<string, array>  $mediaGroups
 * @var array<int, string>    $documents
 */

use App\Core\View;
?>
<nav class="tabs">
    <?php foreach ($groups as $key => $group): ?>
        <a href="/admin/settings/<?= e($key) ?>" class="<?= $key === $groupKey ? 'is-active' : '' ?>"><?= e($group['label']) ?></a>
    <?php endforeach; ?>
</nav>

<form method="POST" action="/admin/settings/<?= e($groupKey) ?>" class="form-panel">
    <?= csrf_field() ?>

    <?php foreach ($definition['fields'] as $name => $field): ?>
        <?= View::partial('partials/field', [
            'name'        => $name,
            'field'       => $field,
            'value'       => old($name, $values[$name] ?? ''),
            'mediaGroups' => $mediaGroups,
            'documents'   => $documents,
        ]) ?>
    <?php endforeach; ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save settings</button>
    </div>
</form>

<?php
/**
 * Renders one form control from a field definition.
 *
 * Shared by the generic content forms and the settings screens, so both stay in
 * step automatically when a new field type is added.
 *
 * @var string                $name
 * @var array<string, mixed>  $field       Definition from the config registry.
 * @var mixed                 $value       Current value.
 * @var array<string, array>  $mediaGroups Image paths grouped by folder.
 * @var array<int, string>    $documents   Non-image asset paths.
 */

$type      = $field['type'] ?? 'text';
$label     = $field['label'] ?? $name;
$hint      = $field['hint'] ?? null;
$required  = in_array('required', $field['rules'] ?? [], true);
$error     = error_for($name);
$inputId   = 'field-' . preg_replace('/[^a-z0-9]+/i', '-', $name);
$mediaGroups = $mediaGroups ?? [];
$documents   = $documents ?? [];
?>
<div class="field<?= $error !== null ? ' has-error' : '' ?>">
    <label for="<?= e($inputId) ?>">
        <?= e($label) ?><?= $required ? ' <span class="required">*</span>' : '' ?>
    </label>

    <?php if ($type === 'textarea'): ?>
        <textarea id="<?= e($inputId) ?>" name="<?= e($name) ?>" rows="5"<?= $required ? ' required' : '' ?>><?= e((string) $value) ?></textarea>

    <?php elseif ($type === 'boolean'): ?>
        <label class="switch">
            <input type="checkbox" id="<?= e($inputId) ?>" name="<?= e($name) ?>" value="1"<?= (int) $value === 1 ? ' checked' : '' ?>>
            <span>Enabled</span>
        </label>

    <?php elseif ($type === 'number'): ?>
        <input type="number" id="<?= e($inputId) ?>" name="<?= e($name) ?>" value="<?= e((string) $value) ?>" step="1"<?= $required ? ' required' : '' ?>>

    <?php elseif ($type === 'list'): ?>
        <?php
        $items = is_array($value) ? $value : (json_decode((string) $value, true) ?: []);
        ?>
        <div class="repeater" data-repeater data-field="<?= e($name) ?>">
            <div class="repeater-items">
                <?php foreach ($items as $item): ?>
                    <div class="repeater-row">
                        <input type="text" name="<?= e($name) ?>[]" value="<?= e((string) $item) ?>">
                        <button type="button" class="btn btn-ghost" data-repeater-remove aria-label="Remove">&times;</button>
                    </div>
                <?php endforeach; ?>
                <?php if ($items === []): ?>
                    <div class="repeater-row">
                        <input type="text" name="<?= e($name) ?>[]" value="">
                        <button type="button" class="btn btn-ghost" data-repeater-remove aria-label="Remove">&times;</button>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" data-repeater-add>+ Add row</button>
        </div>

    <?php elseif ($type === 'image'): ?>
        <?php
        // Keep the saved path selectable even if the file has since been moved
        // or removed, so opening a form never silently clears the field.
        $knownPaths = array_merge(...array_values($mediaGroups ?: [[]]));
        $isOrphan   = (string) $value !== '' && !in_array((string) $value, $knownPaths, true);
        ?>
        <div class="media-field">
            <img class="media-preview" src="<?= e(upload_url((string) $value)) ?>" alt="" data-preview-for="<?= e($inputId) ?>"<?= (string) $value === '' ? ' hidden' : '' ?>>
            <select id="<?= e($inputId) ?>" name="<?= e($name) ?>" data-media-select<?= $required ? ' required' : '' ?>>
                <option value=""<?= (string) $value === '' ? ' selected' : '' ?>>— none —</option>
                <?php if ($isOrphan): ?>
                    <optgroup label="Current (file missing)">
                        <option value="<?= e((string) $value) ?>" selected><?= e(basename((string) $value)) ?></option>
                    </optgroup>
                <?php endif; ?>
                <?php foreach ($mediaGroups as $groupLabel => $paths): ?>
                    <optgroup label="<?= e($groupLabel) ?>">
                        <?php foreach ($paths as $path): ?>
                            <option value="<?= e($path) ?>"<?= (string) $value === $path ? ' selected' : '' ?>><?= e(basename($path)) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
            <p class="field-hint"><a href="/admin/media" target="_blank" rel="noopener">Upload a new image &rarr;</a></p>
        </div>

    <?php elseif ($type === 'file'): ?>
        <select id="<?= e($inputId) ?>" name="<?= e($name) ?>"<?= $required ? ' required' : '' ?>>
            <option value="">— none —</option>
            <?php foreach ($documents as $path): ?>
                <option value="<?= e($path) ?>"<?= (string) $value === $path ? ' selected' : '' ?>><?= e(basename($path)) ?></option>
            <?php endforeach; ?>
        </select>

    <?php else: ?>
        <?php $inputType = in_array($type, ['url', 'email'], true) ? $type : 'text'; ?>
        <input type="<?= e($inputType) ?>" id="<?= e($inputId) ?>" name="<?= e($name) ?>" value="<?= e((string) $value) ?>"<?= $required ? ' required' : '' ?>>
    <?php endif; ?>

    <?php if ($hint !== null): ?>
        <p class="field-hint"><?= e($hint) ?></p>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <p class="field-error"><?= e($error) ?></p>
    <?php endif; ?>
</div>

<?php
/**
 * @var array<string, mixed>|null $record
 * @var string                    $action
 */

use App\Core\Auth;

$isSelf  = $record !== null && (int) $record['id'] === (int) (Auth::user()['id'] ?? 0);
$isNew   = $record === null;
?>
<form method="POST" action="<?= e($action) ?>" class="form-panel">
    <?= csrf_field() ?>

    <div class="field<?= error_for('name') !== null ? ' has-error' : '' ?>">
        <label for="name">Name <span class="required">*</span></label>
        <input type="text" id="name" name="name" value="<?= e(old('name', $record['name'] ?? '')) ?>" required>
        <?php if ($message = error_for('name')): ?><p class="field-error"><?= e($message) ?></p><?php endif; ?>
    </div>

    <div class="field<?= error_for('email') !== null ? ' has-error' : '' ?>">
        <label for="email">Email <span class="required">*</span></label>
        <input type="email" id="email" name="email" value="<?= e(old('email', $record['email'] ?? '')) ?>" autocomplete="off" required>
        <?php if ($message = error_for('email')): ?><p class="field-error"><?= e($message) ?></p><?php endif; ?>
    </div>

    <div class="field<?= error_for('password') !== null ? ' has-error' : '' ?>">
        <label for="password">Password <?= $isNew ? '<span class="required">*</span>' : '' ?></label>
        <input type="password" id="password" name="password" autocomplete="new-password"<?= $isNew ? ' required' : '' ?>>
        <p class="field-hint">
            <?= $isNew ? 'At least 12 characters.' : 'Leave blank to keep the current password.' ?>
        </p>
        <?php if ($message = error_for('password')): ?><p class="field-error"><?= e($message) ?></p><?php endif; ?>
    </div>

    <div class="field">
        <label for="password_confirmation">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
    </div>

    <div class="field<?= error_for('role') !== null ? ' has-error' : '' ?>">
        <label for="role">Role <span class="required">*</span></label>
        <select id="role" name="role" required<?= $isSelf ? ' disabled' : '' ?>>
            <?php $role = old('role', $record['role'] ?? 'editor'); ?>
            <option value="editor"<?= $role === 'editor' ? ' selected' : '' ?>>Editor — manage content</option>
            <option value="admin"<?= $role === 'admin' ? ' selected' : '' ?>>Admin — manage content and users</option>
        </select>
        <?php if ($isSelf): ?>
            <input type="hidden" name="role" value="admin">
            <p class="field-hint">You cannot change your own role.</p>
        <?php endif; ?>
        <?php if ($message = error_for('role')): ?><p class="field-error"><?= e($message) ?></p><?php endif; ?>
    </div>

    <div class="field">
        <label class="switch">
            <input type="checkbox" name="is_active" value="1"<?= ($isNew || (int) ($record['is_active'] ?? 1) === 1) ? ' checked' : '' ?><?= $isSelf ? ' disabled' : '' ?>>
            <span>Account is active</span>
        </label>
        <?php if ($isSelf): ?>
            <p class="field-hint">You cannot disable your own account.</p>
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isNew ? 'Create user' : 'Save changes' ?></button>
        <a href="/admin/users" class="btn btn-ghost">Cancel</a>
    </div>
</form>

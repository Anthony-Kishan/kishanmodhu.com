<h1 class="auth-title">Kishan CMS</h1>
<p class="auth-subtitle">Sign in to manage your site content.</p>

<form method="POST" action="/admin/login" class="stack">
    <?= csrf_field() ?>

    <div class="field<?= error_for('email') !== null ? ' has-error' : '' ?>">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" autocomplete="username" required autofocus>
        <?php if ($message = error_for('email')): ?>
            <p class="field-error"><?= e($message) ?></p>
        <?php endif; ?>
    </div>

    <div class="field<?= error_for('password') !== null ? ' has-error' : '' ?>">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
        <?php if ($message = error_for('password')): ?>
            <p class="field-error"><?= e($message) ?></p>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Sign in</button>
</form>

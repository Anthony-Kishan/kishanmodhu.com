<?php
/**
 * Standalone layout for the sign-in screen.
 *
 * @var string $content
 * @var string $pageTitle
 */

use App\Core\View;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle ?? 'Sign in') ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>

<body class="auth-body">
    <main class="auth-card">
        <?= View::partial('partials/flash') ?>
        <?= $content ?>
    </main>
</body>

</html>

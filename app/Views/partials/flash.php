<?php
/**
 * Flash messages. Pulled here so they are consumed exactly once per request.
 */

use App\Core\Session;

$success = Session::pullFlash('success');
$error   = Session::pullFlash('error');
?>
<?php if ($success !== null): ?>
    <div class="alert alert-success" role="status"><?= e($success) ?></div>
<?php endif; ?>

<?php if ($error !== null): ?>
    <div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

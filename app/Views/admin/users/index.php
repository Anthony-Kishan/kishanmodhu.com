<?php
/** @var array<int, array> $records */

use App\Core\Auth;

$currentUserId = (int) (Auth::user()['id'] ?? 0);
?>
<div class="page-actions">
    <p class="page-description">Accounts that can sign in to this panel.</p>
    <a href="/admin/users/create" class="btn btn-primary">New user</a>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last sign-in</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td>
                        <a href="/admin/users/<?= (int) $record['id'] ?>/edit" class="cell-link"><?= e($record['name']) ?></a>
                        <?php if ((int) $record['id'] === $currentUserId): ?>
                            <span class="pill">You</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($record['email']) ?></td>
                    <td><?= e($record['role']) ?></td>
                    <td>
                        <span class="pill <?= (int) $record['is_active'] === 1 ? 'pill-on' : 'pill-off' ?>">
                            <?= (int) $record['is_active'] === 1 ? 'Active' : 'Disabled' ?>
                        </span>
                    </td>
                    <td><?= $record['last_login_at'] !== null ? e(date('j M Y, H:i', strtotime((string) $record['last_login_at']))) : '—' ?></td>
                    <td class="col-actions">
                        <a href="/admin/users/<?= (int) $record['id'] ?>/edit" class="btn btn-ghost btn-sm">Edit</a>
                        <?php if ((int) $record['id'] !== $currentUserId): ?>
                            <form method="POST" action="/admin/users/<?= (int) $record['id'] ?>/delete" data-confirm="Delete this user account?">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

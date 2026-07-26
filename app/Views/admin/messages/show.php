<?php
/** @var array<string, mixed> $record */
?>
<div class="page-actions">
    <a href="/admin/messages" class="btn btn-ghost">&larr; Back to messages</a>
    <form method="POST" action="/admin/messages/<?= (int) $record['id'] ?>/delete" data-confirm="Delete this message?">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>
</div>

<article class="panel">
    <dl class="detail-list">
        <div><dt>From</dt><dd><?= e($record['first_name'] . ' ' . $record['last_name']) ?></dd></div>
        <div><dt>Email</dt><dd><a href="mailto:<?= e($record['email']) ?>"><?= e($record['email']) ?></a></dd></div>
        <div><dt>Company type</dt><dd><?= e($record['company_type']) ?></dd></div>
        <div><dt>Budget</dt><dd><?= e($record['budget']) ?></dd></div>
        <div><dt>Received</dt><dd><?= e(date('j M Y, H:i', strtotime((string) $record['created_at']))) ?></dd></div>
        <div><dt>IP address</dt><dd><?= e($record['ip_address']) ?></dd></div>
    </dl>

    <h2>Message</h2>
    <p class="message-body"><?= nl2br(e($record['body'])) ?></p>

    <p><a href="mailto:<?= e($record['email']) ?>" class="btn btn-primary">Reply by email</a></p>
</article>

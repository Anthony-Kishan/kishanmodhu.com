<?php
/**
 * @var array<int, array>  $uploads
 * @var array<int, string> $bundled
 */
?>
<section class="panel">
    <h2>Upload an image</h2>
    <form method="POST" action="/admin/media" enctype="multipart/form-data" class="upload-form">
        <?= csrf_field() ?>
        <input type="file" name="file" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml" required>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
    <p class="field-hint">JPG, PNG, GIF, WebP or SVG. Uploaded files become selectable in every image field.</p>
</section>

<h2 class="section-heading">Uploads</h2>
<?php if ($uploads === []): ?>
    <div class="empty-state"><p>Nothing uploaded yet.</p></div>
<?php else: ?>
    <div class="media-grid">
        <?php foreach ($uploads as $item): ?>
            <figure class="media-tile">
                <img src="<?= e(upload_url($item['path'])) ?>" alt="<?= e($item['filename']) ?>" loading="lazy">
                <figcaption>
                    <span class="media-name" title="<?= e($item['path']) ?>"><?= e($item['filename']) ?></span>
                    <span class="media-meta"><?= e(number_format($item['size_bytes'] / 1024, 0)) ?> KB</span>
                    <form method="POST" action="/admin/media/<?= (int) $item['id'] ?>/delete" data-confirm="Delete this file? Anything still using it will lose its image.">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2 class="section-heading">Bundled with the site</h2>
<p class="page-description">Shipped as part of the theme. Selectable everywhere, managed on disk rather than here.</p>
<div class="media-grid">
    <?php foreach ($bundled as $path): ?>
        <figure class="media-tile is-readonly">
            <img src="<?= e(upload_url($path)) ?>" alt="<?= e(basename($path)) ?>" loading="lazy">
            <figcaption>
                <span class="media-name" title="<?= e($path) ?>"><?= e(basename($path)) ?></span>
            </figcaption>
        </figure>
    <?php endforeach; ?>
</div>

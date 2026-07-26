<?php
/**
 * Scrolling logo strip.
 *
 * The list is emitted twice: the CSS animation translates the first copy fully
 * out of view, and the second copy keeps the strip continuous.
 *
 * @var array<int, array> $marqueeLogos
 */
?>
<!-- MARQUEE STACKS SECTION START -->
<section class="marquee-stacks">
    <div class="container">
        <div class="marquee">
            <?php for ($copy = 0; $copy < 2; $copy++): ?>
                <div class="marquee-content"<?= $copy === 1 ? ' aria-hidden="true"' : '' ?>>
                    <?php foreach ($marqueeLogos as $logo): ?>
                        <img src="<?= e(asset($logo['logo_path'])) ?>" alt="<?= e($logo['name']) ?> Logo" class="logo" loading="lazy" decoding="async" />
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>
<!-- MARQUEE STACKS SECTION END -->

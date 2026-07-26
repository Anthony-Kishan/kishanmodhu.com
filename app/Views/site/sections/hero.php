<?php
/** @var App\Models\Setting $settings */

$rotatingWords = comma_lines($settings->get('hero_rotating_words'));
?>
<!-- HOME SECTION START -->
<section class="home">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <img src="<?= e(asset($settings->get('hero_signature'))) ?>" alt="" style="width: 100%; mix-blend-mode: screen;" fetchpriority="high">
                <div class="rotating-text">
                    <h1><?= e($settings->get('hero_heading')) ?></h1>
                    <p>
                        <?php foreach ($rotatingWords as $index => $word): ?>
                            <span class="word w-<?= $index + 1 ?>"><?= e($word) ?></span>
                        <?php endforeach; ?>
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= e(asset($settings->get('hero_side_image'))) ?>" alt="" style="width: 80%;">
            </div>
        </div>
        <div class="scroll-indicator">
            <svg width="30" height="30" viewBox="0 0 30 30" aria-hidden="true">
                <path d="M15 5v20M7 17l8 8 8-8" stroke="#fff" fill="none" stroke-width="2" />
            </svg>
        </div>
    </div>
</section>
<!-- HOME SECTION END -->

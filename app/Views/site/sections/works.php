<?php
/**
 * @var App\Models\Setting $settings
 * @var array<int, array>  $works
 */
?>
<!-- WORK SECTION START -->
<section id="works" class="portfolio">
    <div class="container">
        <div class="hero-section ">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 id="text-work">WORKS</h1>
                </div>
                <div class="col-lg-6">
                    <span class="section-label"><?= e($settings->get('works_label')) ?></span><br>
                    <p class="description">
                        <?= e($settings->get('works_description')) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="row portfolio-grid">
            <?php foreach ($works as $index => $work): ?>
                <div class="col-md-6 portfolio-card">
                    <div class="media">
                        <img src="<?= e(asset($work['image_path'])) ?>" alt="<?= e($work['image_alt']) ?>" class="portfolio-image" loading="lazy" decoding="async">
                    </div>
                    <div class="card-overlay">
                        <h2 class="project-title">
                            <?php if (!empty($work['url'])): ?>
                                <a href="<?= e($work['url']) ?>" class="text-white" target="_blank" rel="noopener noreferrer"><?= e($work['title']) ?></a>
                            <?php else: ?>
                                <?= e($work['title']) ?>
                            <?php endif; ?>
                        </h2>
                        <div class="project-details">
                            <span><?= e($work['category']) ?></span>
                            <span><?= e($work['tag']) ?></span>
                            <span>[<?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?>]</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!-- WORK SECTION END -->

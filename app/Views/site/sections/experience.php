<?php
/**
 * @var App\Models\Setting $settings
 * @var array<int, array>  $experiences
 */
?>
<!-- EXPERIENCE SECTION START -->
<section class="experience">
    <div class="container experience-section">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 id="text-experience">EXPERIENCE</h1>
                <p class="subtitle"><?= e($settings->get('experience_subtitle')) ?></p>
            </div>
            <span class="section-label"><?= e($settings->get('experience_label')) ?></span>
        </div>

        <div class="experience-items">
            <div class="blob blob1"></div>
            <div class="blob blob2"></div>
            <div class="blob blob3"></div>
            <?php foreach ($experiences as $experience): ?>
                <div class="experience-item">
                    <div class="grain"></div>

                    <div class="row mb-3">
                        <div class="col-8 d-flex align-items-center">
                            <img src="<?= e(asset($experience['logo_path'])) ?>" alt="<?= e($experience['company']) ?>" class="company-logo" loading="lazy" decoding="async">
                            <div>
                                <p class="company-name"><?= e($experience['company']) ?></p>
                                <h3 class="position-title"><?= e($experience['position']) ?></h3>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <span class="date ms-auto d-none d-md-inline"><?= e($experience['date_label']) ?></span>
                            <span class="small-date d-lg-none me-2"><?= e($experience['date_label_short']) ?></span>
                        </div>
                    </div>
                    <p class="description"><?= e($experience['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!-- EXPERIENCE SECTION END -->

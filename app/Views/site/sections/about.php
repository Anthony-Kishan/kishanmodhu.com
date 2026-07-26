<?php
/**
 * @var App\Models\Setting $settings
 * @var array<int, array>  $certificates
 * @var array<int, array>  $aboutSocialLinks
 */
?>
<!-- ABOUT SECTION START -->
<section id="about" class="about">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 id="text-about">ABOUT</h1>
                <h5 class="subtitle"><?= e($settings->get('about_subtitle')) ?> <u class="underlined"><?= e($settings->get('about_subtitle_underlined')) ?></u></h5>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="section-label"><?= e($settings->get('about_label')) ?></span><br>
            </div>
        </div>

        <div class="row g-5">
            <div class="col-md-6 mb-4 mb-md-0">
                <img src="<?= e(asset($settings->get('about_profile_image'))) ?>" alt="Profile Image" class="profile-image" loading="lazy" decoding="async">
                <a href="<?= e(asset($settings->get('about_resume_path'))) ?>" class="download-resume" download="KishanModhuCV"><?= e($settings->get('about_resume_label')) ?></a>

                <span class="social-links">
                    <?php foreach ($aboutSocialLinks as $link): ?>
                        <a href="<?= e($link['url']) ?>" class="social-icon" aria-label="<?= e($link['label']) ?>" target="_blank" rel="noopener noreferrer"><img src="<?= e(asset($link['icon_path'])) ?>" alt="<?= e($link['label']) ?>" loading="lazy"></a>
                    <?php endforeach; ?>
                </span>
            </div>
            <div class="col-md-6">
                <div class="mb-5">
                    <h2 class="section-title">[ ABOUT ME ]</h2>
                    <p>
                        <?= e($settings->get('about_bio')) ?>
                    </p>
                </div>

                <?php if ($certificates !== []): ?>
                    <div class="mb-5">
                        <h2 class="section-title">[ CERTIFICATES ]</h2>
                        <?php foreach ($certificates as $certificate): ?>
                            <div class="achievement-item">
                                <?php if (!empty($certificate['url'])): ?>
                                    <a href="<?= e($certificate['url']) ?>" target="_blank" rel="noopener noreferrer" class="text-white"><?= e($certificate['title']) ?></a>
                                <?php else: ?>
                                    <span class="text-white"><?= e($certificate['title']) ?></span>
                                <?php endif; ?>
                                <span><?= e($certificate['year']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div>
                    <h2 class="section-title">[ MY MISSION ]</h2>
                    <p>
                        <?= e($settings->get('about_mission')) ?>
                    </p>
                </div>

                <div class="text-end mt-4">
                    <span><?= e($settings->get('about_since')) ?></span>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ABOUT SECTION END -->

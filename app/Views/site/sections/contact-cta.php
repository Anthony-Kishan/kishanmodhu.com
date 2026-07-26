<?php
/**
 * @var App\Models\Setting $settings
 * @var array<int, array>  $socialLinks
 */

$headingLines  = comma_lines($settings->get('contact_heading'));
$subtitleLines = comma_lines($settings->get('contact_subtitle'));
?>
<!-- CONTACT SECTION START -->
<section id="contact" class="contact">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-md-9 text-center">
                <h1 class="main-heading"><?= implode('<br>', array_map('e', $headingLines)) ?></h1>
                <h6 class="subtitle"><?= implode('<br>', array_map('e', $subtitleLines)) ?></h6>
                <a href="/contact" class="contact-btn"><?= e($settings->get('contact_button')) ?></a>
                <div class="social-container">
                    <?php foreach ($socialLinks as $link): ?>
                        <a href="<?= e($link['url']) ?>" class="social-icon" target="_blank" rel="noopener noreferrer"><?= e($link['label']) ?></a><img src="<?= e(asset('assets/images/icons/arrow.svg')) ?>" alt="">
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-3">
                <img src="<?= e(asset($settings->get('contact_side_image'))) ?>" alt="Designer Profile" class="profile-image" loading="lazy" decoding="async">
            </div>
        </div>
    </div>
</section>
<!-- CONTACT SECTION END -->

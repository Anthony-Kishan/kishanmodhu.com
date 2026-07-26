<?php
/**
 * Testimonial marquee.
 *
 * Previously built client-side by testimonials.js, which also duplicated each
 * row's markup so the CSS slide animation could loop seamlessly. That
 * duplication now happens here, server-side.
 *
 * @var App\Models\Setting $settings
 * @var array<int, array>  $testimonials
 */

// The original split was "first four in row one, the rest in row two".
$rows = [array_slice($testimonials, 0, 4), array_slice($testimonials, 4)];
?>
<!-- TESTIMONIAL SECTION START -->
<section id="testimonial" class="testimonial">
    <div class="container testimonial-section">
        <h2 id="text-testimonial" class="text-center">TESTIMONIAL</h2>
        <h1 class="section-title"><?= e($settings->get('testimonial_heading')) ?></h1>
        <?php foreach ($rows as $rowIndex => $row): ?>
            <div class="testimonial-row<?= $rowIndex === 0 ? ' mb-5' : '' ?>" id="row<?= $rowIndex + 1 ?>">
                <?php for ($copy = 0; $copy < 2; $copy++): ?>
                    <?php foreach ($row as $testimonial): ?>
                        <div class="testimonial-card"<?= $copy === 1 ? ' aria-hidden="true"' : '' ?>>
                            <div class="company-date">
                                <img src="<?= e(asset($testimonial['source_icon'])) ?>" alt="" loading="lazy" decoding="async"><div class="review-date"><?= e($testimonial['date_label']) ?></div>
                            </div>
                            <p>"<?= e($testimonial['body']) ?>"</p>
                            <div class="reviewer-img-name">
                                <img src="<?= e(asset($testimonial['avatar_path'])) ?>" alt="<?= e($testimonial['name']) ?>" loading="lazy" decoding="async"><span><h4><?= e($testimonial['name']) ?></h4><p><?= e($testimonial['country']) ?></p></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<!-- TESTIMONIAL SECTION END -->

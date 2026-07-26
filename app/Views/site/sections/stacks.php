<?php
/**
 * @var App\Models\Setting $settings
 * @var array<int, array>  $stacks
 */

$headingLines = comma_lines($settings->get('stack_heading'));
?>
<!-- STACKS SECTION START -->
<section class="stacks">
    <div class="container stack-section">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 id="text-stack" class="stack-header"><?= implode('<br>', array_map('e', $headingLines)) ?></h1>
                <p class="subtitle"><?= e($settings->get('stack_subtitle')) ?></p>
            </div>
            <span class="section-label"><?= e($settings->get('stack_label')) ?></span>

        </div>

        <div class="stack-items">
            <?php foreach ($stacks as $stack): ?>
                <div class="stack-item">
                    <div class="d-flex mb-3">
                        <img src="<?= e(asset($stack['logo_path'])) ?>" alt="<?= e($stack['name']) ?>" class="tool-logo" loading="lazy" decoding="async">
                        <div>
                            <h3 class="tool-name"><?= e($stack['name']) ?></h3>
                            <p class="tool-category"><?= e($stack['category']) ?></p>
                        </div>
                        <span class="tool-percentage ms-auto">[ <?= e((string) $stack['proficiency']) ?>% ]</span>
                    </div>
                    <p class="description"><?= e($stack['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!-- STACKS SECTION END -->

<?php
/**
 * Services accordion.
 *
 * Previously injected client-side by services.js; now rendered server-side with
 * the same Bootstrap collapse markup, so the section is present in the HTML.
 *
 * @var App\Models\Setting $settings
 * @var array<int, array>  $services
 */
?>
<!-- SERVICES SECTION START -->
<section id="service" class="service my-5">
    <div class="container">
        <div class="row">
            <div class="col-6">
                <h1 id="text-service">SERVICES</h1>
            </div>
            <div class="col-6">
                <span class="section-label"><?= e($settings->get('services_label')) ?></span>
            </div>
        </div>

        <div id="services-accordion" class="accordion">
            <?php foreach ($services as $index => $service): ?>
                <?php $isFirst = $index === 0; ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $index ?>">
                        <button class="accordion-button<?= $isFirst ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="<?= $isFirst ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                            <?= e($service['title']) ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $index ?>" class="accordion-collapse collapse<?= $isFirst ? ' show' : '' ?>" aria-labelledby="heading<?= $index ?>" data-bs-parent="#services-accordion">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><?= e($service['description']) ?></p>
                                    <p>Starts At <u>Cost® — $<?= e((string) $service['starting_cost']) ?></u></p>
                                    <p class="fw-bold">[ KEY FEATURES ]</p>
                                    <ul class="list-unstyled">
                                        <?php foreach ($service['features'] as $feature): ?>
                                            <li><i class="fas fa-check me-2"></i><?= e($feature) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <a href="#contact" class="theme-btn">Get Started</a>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <img src="<?= e(asset($service['image_path'])) ?>" alt="<?= e($service['title']) ?>" class="service-image" loading="lazy" decoding="async">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!-- SERVICES SECTION END -->

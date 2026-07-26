<?php
/**
 * Site header: logo, live clock, overlay menu.
 *
 * @var App\Models\Setting $settings
 * @var array<int, array>  $socialLinks
 * @var string             $homePrefix Empty on the home page, '/' elsewhere,
 *                                     so anchors resolve from any URL.
 */
$menu = [
    'WORKS'    => '#works',
    'ABOUT'    => '#about',
    'SERVICES' => '#service',
    'CONTACT'  => '#contact',
];
?>
<header id="header">
    <div class="container">
        <nav class="brand-time" style="display: flex; align-content: center;">
            <div class="row" style="align-items: center; align-content: center;">
                <div class="col-6 col-lg-3">
                    <a href="/"><img src="<?= e(asset($settings->get('logo_path'))) ?>" alt="<?= e($settings->get('brand_name')) ?>" style="width: 60%;"></a>
                </div>
                <div class="col-lg-3">
                    <p><?= e($settings->get('location_label')) ?>&nbsp;</p>
                    <p id="time" data-timezone="<?= e($settings->get('timezone', 'Asia/Dhaka')) ?>"></p>
                </div>
                <div class="col-lg-3" style="text-align: right;">
                    <a class="nav-item text-white" href="<?= e($homePrefix) ?>#works" style="margin-left: 4rem;">(WORKS)</a>
                    <a class="nav-item ms-4 text-white" href="<?= e($homePrefix) ?>#service">(SERVICES)</a>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="toggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="menu-items">
                        <ul class="menu-links">
                            <?php foreach ($menu as $label => $anchor): ?>
                                <li><a class="menu-item" href="<?= e($homePrefix . $anchor) ?>"><?= e($label) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="social-container">
                            <?php foreach ($socialLinks as $link): ?>
                                <a href="<?= e($link['url']) ?>" class="social-icon" target="_blank" rel="noopener noreferrer"><?= e($link['label']) ?></a><img src="<?= e(asset('assets/images/icons/arrow.svg')) ?>" alt="">
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </nav>
    </div>
</header>

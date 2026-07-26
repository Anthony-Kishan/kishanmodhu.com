<?php
/**
 * Public site layout.
 *
 * @var string                    $content
 * @var App\Models\Setting        $settings
 * @var array<int, array>         $socialLinks
 */

use App\Core\Config;

$canonical = Config::get('app.url');

// Structured data is built as an array and encoded, so it is always valid JSON.
// (The hand-written version this replaces was missing a comma and never parsed.)
$structuredData = [
    '@context' => 'https://schema.org',
    '@type'    => 'Person',
    'name'     => $settings->get('brand_name'),
    'url'      => $canonical,
    'sameAs'   => array_values(array_map(
        static fn (array $link): string => $link['url'],
        $socialLinks
    )),
    'image'    => $canonical . '/' . ltrim($settings->get('person_image'), '/'),
    'jobTitle' => $settings->get('person_job_title'),
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script type="application/ld+json"><?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) ?></script>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="msapplication-TileImage" content="<?= e($canonical . '/' . ltrim($settings->get('og_image'), '/')) ?>">
    <meta name="description" content="<?= e($settings->get('meta_description')) ?>">
    <meta name="keywords" content="<?= e($settings->get('meta_keywords')) ?>">
    <meta property="og:title" content="<?= e($settings->get('og_title')) ?>">
    <meta property="og:description" content="<?= e($settings->get('og_description')) ?>">
    <meta property="og:image" itemprop="image" content="<?= e($canonical . '/' . ltrim($settings->get('og_image'), '/')) ?>">
    <meta property="og:image:type" content="image/jpg">
    <meta property="og:url" content="<?= e($canonical) ?>">

    <title><?= e($pageTitle ?? $settings->get('meta_title')) ?></title>

    <link rel="icon" href="<?= e(asset($settings->get('logo_path'))) ?>">

    <!-- BOOTSTRAP -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"
        integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bruno+Ace+SC&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/navbar.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/responsive.css')) ?>">
</head>

<body>
    <div id="bodyTop"><img src="<?= e(asset('assets/images/icons/chevron-up.svg')) ?>" alt="Back to top" width="24" height="24"></div>
    <div class="cursor"></div>

    <?= App\Core\View::partial('partials/header', ['settings' => $settings, 'socialLinks' => $socialLinks, 'homePrefix' => $homePrefix ?? '']) ?>

    <main>
        <?= $content ?>
    </main>

    <?= App\Core\View::partial('partials/footer', ['settings' => $settings]) ?>

    <!-- BOOTSTRAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"
        integrity="sha512-ykZ1QQr0Jy/4ZkvKuqWn4iF3lqPZyij9iRv6sGqLRdTPkY69YX6+7wvVGmsdBbiIfN/8OdsI7HABjvEok6ZopQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://unpkg.com/split-type"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/ScrollTrigger.min.js"></script>

    <script src="<?= e(asset('assets/js/navbar.js')) ?>"></script>
    <script src="<?= e(asset('assets/js/main.js')) ?>"></script>
</body>

</html>

<?php
/**
 * Home page — composed of the section partials, in the order they appear.
 *
 * @var App\Models\Setting $settings
 * @var array<int, array>  $works
 * @var array<int, array>  $certificates
 * @var array<int, array>  $aboutSocialLinks
 * @var array<int, array>  $marqueeLogos
 * @var array<int, array>  $services
 * @var array<int, array>  $experiences
 * @var array<int, array>  $stacks
 * @var array<int, array>  $testimonials
 * @var array<int, array>  $socialLinks
 */

use App\Core\View;

echo View::partial('site/sections/hero', compact('settings'));
echo View::partial('site/sections/works', compact('settings', 'works'));
echo View::partial('site/sections/about', compact('settings', 'certificates', 'aboutSocialLinks'));
echo View::partial('site/sections/marquee', compact('marqueeLogos'));
echo View::partial('site/sections/services', compact('settings', 'services'));
?>
<section class="pattern1"></section>
<?php
echo View::partial('site/sections/experience', compact('settings', 'experiences'));
?>
<section class="pattern"></section>
<?php
echo View::partial('site/sections/stacks', compact('settings', 'stacks'));
?>
<section class="pattern2"></section>
<?php
echo View::partial('site/sections/testimonials', compact('settings', 'testimonials'));
echo View::partial('site/sections/contact-cta', compact('settings', 'socialLinks'));

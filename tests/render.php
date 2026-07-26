<?php

declare(strict_types=1);

/**
 * Renders the real public pages and asserts the output.
 *
 * No database: the settings cache is primed through reflection and seed-shaped
 * fixtures are passed straight into the views. This is what catches template
 * regressions — the pre-CMS page is the reference.
 *
 * Loaded by tests/run.php.
 */

use App\Core\ContentType;
use App\Core\View;
use App\Models\Setting;
use App\Services\MediaLibrary;
use Tests\Harness;

// ── Prime settings ──────────────────────────────────────────────────────────
$values = [
    'brand_name' => 'Kishan Modhu',
    'logo_path' => 'assets/images/logo-light.png',
    'location_label' => 'Dhaka, Bangladesh/',
    'timezone' => 'Asia/Dhaka',
    'footer_brand' => 'kishan',
    'footer_copyright' => '© 2024 DESIGN',
    'footer_location' => 'Dhaka, Bangladesh',
    'meta_title' => 'Anthony Kishan',
    'meta_description' => 'Kishan Modhu is a web developer.',
    'meta_keywords' => 'Kishan Modhu, web developer',
    'og_title' => 'Kishan Modhu - Web Developer',
    'og_description' => 'Explore my portfolio.',
    'og_image' => 'assets/images/thumbnail.jpg',
    'person_job_title' => 'Web Developer',
    'person_image' => 'assets/images/profile-img.jpg',
    'hero_signature' => 'assets/images/signature-gif.gif',
    'hero_side_image' => 'assets/images/icons/process-icon-2.svg',
    'hero_heading' => 'Web',
    'hero_rotating_words' => 'Designer, Developer, Enthusiast',
    'works_label' => 'explore',
    'works_description' => 'Gorgeous design. Scroll-stopping content.',
    'about_label' => "let's work together",
    'about_subtitle' => 'WEB DEV PASSIONATE ABOUT CREATING',
    'about_subtitle_underlined' => 'MODERN WEB EXPERIENCES',
    'services_label' => 'discover',
    'experience_label' => '2022 - present',
    'experience_subtitle' => 'SHOWCASING MY WEB DEVELOPMENT JOURNEY',
    'stack_label' => 'my skill',
    'stack_heading' => 'FAVOURITE,STACK',
    'stack_subtitle' => 'EXPLORE MY CURATED TOP DESIGN PICKS',
    'testimonial_heading' => 'What my Clients Say',
    'about_profile_image' => 'assets/images/profile-img.jpg',
    'about_resume_path' => 'assets/kishan-modhu-resume.pdf',
    'about_resume_label' => 'DOWNLOAD RESUME',
    'about_bio' => 'As a junior web developer.',
    'about_mission' => 'To create engaging experiences.',
    'about_since' => '[ SINCE. 2021 ]',
    'contact_heading' => "LET'S BUILD YOUR,WEB IDENTITY",
    'contact_subtitle' => '"COLLABORATE WITH ME,REFLECT YOUR UNIQUE VISION"',
    'contact_button' => 'CONTACT NOW',
    'contact_side_image' => 'assets/images/icons/process-icon-1.svg',
    'contact_page_heading' => "LET'S WORK TOGETHER",
    'contact_notify_email' => '',
];

$cache = (new ReflectionClass(Setting::class))->getProperty('cache');
$cache->setAccessible(true);
$cache->setValue(null, $values);

$settings = new Setting();

Harness::group('fixtures');
Harness::check('settings cache primed', $settings->get('brand_name') === 'Kishan Modhu');

// Every settings key the registry declares must be covered by this fixture,
// or the render assertions below silently test empty strings.
$declared = [];

foreach (App\Core\Config::get('settings') as $group) {
    $declared = array_merge($declared, array_keys($group['fields']));
}

$absent = array_diff($declared, array_keys($values));
Harness::check('fixture covers every setting', $absent === [], implode(', ', $absent));

$socialLinks = [
    ['label' => 'GITHUB',   'url' => 'https://github.com/Anthony-Kishan',            'icon_path' => 'assets/images/icons/github.svg',   'show_in_about' => 1],
    ['label' => 'LINKEDIN', 'url' => 'https://www.linkedin.com/in/anthony-kishan/',  'icon_path' => 'assets/images/icons/linkedin.svg', 'show_in_about' => 1],
    ['label' => 'FIVERR',   'url' => 'https://www.fiverr.com/anthony_kishan/',       'icon_path' => 'assets/images/icons/fiverr.png',   'show_in_about' => 1],
];

$testimonials = [];

for ($i = 1; $i <= 8; $i++) {
    $testimonials[] = [
        'id' => $i,
        'name' => 'reviewer' . $i,
        'body' => 'Review ' . $i . ' with "quotes" & <tags>',
        'country' => 'United Kingdom',
        'date_label' => '[ SEPTEMBER, 2021 ]',
        'avatar_path' => 'assets/images/testimonials/gideonmk.webp',
        'source_icon' => 'assets/images/icons/fiverr.png',
    ];
}

$data = [
    'settings'         => $settings,
    'socialLinks'      => $socialLinks,
    'aboutSocialLinks' => $socialLinks,
    'testimonials'     => $testimonials,
    'works' => [
        ['id' => 1, 'title' => 'DevPulse',    'category' => 'TECH NEWS & TUTORIALS BLOG', 'tag' => '(FULL STACK)', 'image_path' => 'assets/images/works/01-DEVPULSE.webp',    'image_alt' => 'Blog Project',      'url' => null],
        ['id' => 2, 'title' => 'EDISON',      'category' => 'ONLINE EDUCATION',           'tag' => '(BRANDING)',   'image_path' => 'assets/images/works/02-EDISON.webp',      'image_alt' => 'Education Project', 'url' => null],
        ['id' => 3, 'title' => 'LINKIN PARK', 'category' => 'MUSIC BAND',                 'tag' => '(DEMO SITE)',  'image_path' => 'assets/images/works/02-LINKIN-PARK.webp', 'image_alt' => 'Music Project',     'url' => null],
        ['id' => 4, 'title' => 'ALANZO',      'category' => 'CATERING',                   'tag' => '(DEMO SITE)',  'image_path' => 'assets/images/works/Alanzo12-min.gif',    'image_alt' => 'Catering Project',  'url' => 'https://example.com'],
    ],
    'certificates' => [
        ['id' => 1, 'title' => 'Web Design & Dev: Freelancing L3', 'year' => '2024', 'url' => 'https://drive.google.com/file/d/1'],
        ['id' => 2, 'title' => 'Python Development',               'year' => '2024', 'url' => null],
        ['id' => 3, 'title' => 'Graphics Design: Freelancing L3',  'year' => '2024', 'url' => 'https://drive.google.com/file/d/3'],
    ],
    'marqueeLogos' => [
        ['id' => 1, 'name' => 'HTML',    'logo_path' => 'assets/images/stack-logos/html-1.svg'],
        ['id' => 2, 'name' => 'LARAVEL', 'logo_path' => 'assets/images/stack-logos/laravel-2.svg'],
    ],
    'services' => [
        ['id' => 1, 'title' => 'Web Development', 'description' => 'Dynamic websites.',  'starting_cost' => 2000, 'features' => ['Custom Web Design', 'Responsive Design'], 'image_path' => 'assets/images/works/01-DEVPULSE.webp'],
        ['id' => 2, 'title' => 'Web Design',      'description' => 'Creative and sharp.', 'starting_cost' => 1500, 'features' => ['Bespoke Visual Design'],                 'image_path' => 'assets/images/works/02-EDISON.webp'],
    ],
    'experiences' => [
        ['id' => 1, 'company' => 'EUROPEAN IT INSTITUTE', 'position' => 'PYTHON DEVELOPMENT INTERN', 'description' => 'A 3-month internship.', 'date_label' => '[ JULY/2024 - SEP/2024 ]', 'date_label_short' => '[ JUL/21 - SEP/24 ]', 'logo_path' => 'assets/images/company-logos/euit-logo1.png'],
    ],
    'stacks' => [
        ['id' => 1, 'name' => 'HTML', 'category' => 'Markup Language', 'proficiency' => 95, 'description' => 'The standard markup language.', 'logo_path' => 'assets/images/stack-logos/html-1.svg'],
    ],
];

// ── Home page ───────────────────────────────────────────────────────────────
Harness::group('home page');
$home = View::render('site/home', $data, 'layouts/site');

Harness::check('starts with doctype', str_starts_with($home, '<!DOCTYPE html>'));
Harness::check('declares language', str_contains($home, '<html lang="en">'));
Harness::check('title comes from settings', str_contains($home, '<title>Anthony Kishan</title>'));
Harness::check('has a favicon', str_contains($home, 'rel="icon"'));

// The original JSON-LD was missing a comma and never parsed.
preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $home, $ld);
$structured = json_decode($ld[1] ?? '', true);
Harness::check('JSON-LD parses', json_last_error() === JSON_ERROR_NONE, json_last_error_msg());
Harness::check('JSON-LD lists every social link', ($structured['sameAs'] ?? []) === array_column($socialLinks, 'url'));
Harness::check('JSON-LD carries jobTitle', ($structured['jobTitle'] ?? '') === 'Web Developer');
Harness::check('JSON-LD type is Person', ($structured['@type'] ?? '') === 'Person');

Harness::check('every section present', str_contains($home, 'id="works"')
    && str_contains($home, 'id="about"') && str_contains($home, 'id="service"')
    && str_contains($home, 'id="testimonial"') && str_contains($home, 'id="contact"')
    && str_contains($home, 'class="marquee"') && str_contains($home, 'class="experience"')
    && str_contains($home, 'class="stacks"'));

Harness::check('pattern dividers appear once each',
    substr_count($home, 'class="pattern1"') === 1
    && substr_count($home, 'class="pattern2"') === 1
    && substr_count($home, 'class="pattern"') === 1);

Harness::check('four work cards', substr_count($home, 'class="col-md-6 portfolio-card"') === 4);
Harness::check('cards numbered [01]–[04]', str_contains($home, '<span>[01]</span>') && str_contains($home, '<span>[04]</span>'));
Harness::check('linked work becomes an anchor', str_contains($home, '>ALANZO</a>'));
Harness::check('unlinked work stays plain text', !str_contains($home, '>DevPulse</a>'));
Harness::check('external links are rel-protected',
    substr_count($home, 'target="_blank"') === substr_count($home, 'rel="noopener noreferrer"'));

Harness::check('marquee list duplicated for the loop', substr_count($home, 'class="marquee-content"') === 2);
Harness::check('duplicate marquee hidden from AT', str_contains($home, 'class="marquee-content" aria-hidden="true"'));
Harness::check('rotating words numbered', str_contains($home, 'class="word w-1"') && str_contains($home, 'class="word w-3"'));
Harness::check('stack heading line break', str_contains($home, 'FAVOURITE<br>STACK'));
Harness::check('contact heading line break', str_contains($home, 'BUILD YOUR<br>WEB IDENTITY'));

Harness::check('first accordion item open', str_contains($home, 'id="collapse0" class="accordion-collapse collapse show"'));
Harness::check('later accordion items collapsed', str_contains($home, 'accordion-button collapsed'));
Harness::check('service features listed', str_contains($home, 'Custom Web Design'));
Harness::check('service cost rendered', str_contains($home, '$2000'));

Harness::check('testimonials split across two rows', substr_count($home, 'id="row1"') === 1 && substr_count($home, 'id="row2"') === 1);
Harness::check('testimonial cards duplicated 8→16', substr_count($home, 'class="testimonial-card"') === 16);

Harness::check('stack items rendered', substr_count($home, 'class="stack-item"') === 1);
Harness::check('experience items rendered', substr_count($home, 'class="experience-item"') === 1);
Harness::check('certificates rendered', substr_count($home, 'class="achievement-item"') === 3);

Harness::check('below-fold images lazy-load', substr_count($home, 'loading="lazy"') > 10);
Harness::check('hero image prioritised', str_contains($home, 'fetchpriority="high"'));
Harness::check('assets cache-busted', preg_match('/style\.css\?v=\d+/', $home) === 1);
Harness::check('jQuery gone', stripos($home, 'jquery') === false);
Harness::check('no third-party favicon fetches', !str_contains($home, 'fiverr.com/favicon'));

Harness::check('user content escaped', str_contains($home, '&quot;quotes&quot; &amp; &lt;tags&gt;'));
Harness::check('no raw tag injected', !str_contains($home, ' <tags>'));
Harness::check('home anchors stay on-page', str_contains($home, 'href="#works"') && !str_contains($home, 'href="/#works"'));

// ── Contact page ────────────────────────────────────────────────────────────
Harness::group('contact page');
$contact = View::render('site/contact', [
    'settings'    => $settings,
    'socialLinks' => $socialLinks,
    'homePrefix'  => '/',
], 'layouts/site');

Harness::check('posts to our own endpoint', str_contains($contact, 'action="/contact" method="POST"'));
Harness::check('carries a CSRF token', preg_match('/name="_token" value="[a-f0-9]{64}"/', $contact) === 1);
Harness::check('has a honeypot', str_contains($contact, 'name="website"'));
Harness::check('honeypot hidden from AT', str_contains($contact, 'aria-hidden="true"'));

// The original form's inputs had no name attributes at all.
foreach (['first_name', 'last_name', 'email', 'company_type', 'budget', 'body'] as $field) {
    Harness::check("{$field} has a name attribute", str_contains($contact, 'name="' . $field . '"'));
}

Harness::check('company options match the controller', str_contains($contact, 'value="startup"')
    && str_contains($contact, 'value="enterprise"') && str_contains($contact, 'value="agency"')
    && str_contains($contact, 'value="other"'));
Harness::check('header anchors point back home', str_contains($contact, 'href="/#works"'));
Harness::check('no portfolio grid here', !str_contains($contact, 'portfolio-card'));

// ── Admin views ─────────────────────────────────────────────────────────────
Harness::group('admin views');

$login = View::render('admin/auth/login', ['pageTitle' => 'Sign in'], 'layouts/auth');
Harness::check('login form renders', str_contains($login, 'action="/admin/login"'));
Harness::check('login has a password field', str_contains($login, 'name="password"'));
Harness::check('login is noindex', str_contains($login, 'noindex'));
Harness::check('login carries a token', str_contains($login, 'name="_token"'));

$library = new MediaLibrary();
$bundled = $library->bundled();
Harness::check('media library finds bundled images', count($bundled) > 20, count($bundled) . ' found');
Harness::check('bundled paths are public-relative', str_starts_with($bundled[0] ?? '', 'assets/images/'));
Harness::check('bundled paths exist on disk', is_file(App\Core\Config::get('app.public_path') . '/' . ($bundled[0] ?? '')));
Harness::check('deleted assets stay deleted', !in_array('assets/images/SignatureAnimationSpeed.gif', $bundled, true));
Harness::check('resume is a listed document', in_array('assets/kishan-modhu-resume.pdf', $library->documents(), true));

$mediaGroups = ['Works' => ['assets/images/works/02-EDISON.webp', 'assets/images/works/01-DEVPULSE.webp']];

// Every content type's form and list must render — this is what proves a newly
// registered type needs no bespoke view.
foreach (ContentType::all() as $type) {
    $record = ['id' => 1, 'is_published' => 1];

    foreach ($type->fields() as $field => $definition) {
        $record[$field] = match ($definition['type']) {
            'list'    => json_encode(['Alpha', 'Beta']),
            'number'  => 42,
            'boolean' => 1,
            'image'   => 'assets/images/works/02-EDISON.webp',
            'url'     => 'https://example.com',
            default   => 'Sample ' . $field,
        };
    }

    $form = View::render('admin/resources/form', [
        'contentType' => $type,
        'record'      => $record,
        'mediaGroups' => $mediaGroups,
        'action'      => $type->adminUrl('/1'),
    ], null);

    $missing = array_filter(
        array_keys($type->fields()),
        static fn (string $f): bool => !str_contains($form, 'name="' . $f . '"')
            && !str_contains($form, 'name="' . $f . '[]"')
    );
    Harness::check("{$type->key}: form renders every field", $missing === [], implode(', ', $missing));

    $list = View::render('admin/resources/index', [
        'contentType' => $type,
        'records'     => [$record],
    ], null);

    Harness::check("{$type->key}: list screen renders", str_contains($list, 'data-id="1"'));

    if ($type->isSortable()) {
        Harness::check("{$type->key}: list is sortable", str_contains($list, 'data-reorder-url="' . $type->adminUrl('/reorder') . '"'));
    }
}

$servicesForm = View::render('admin/resources/form', [
    'contentType' => ContentType::find('services'),
    'record'      => ['id' => 1, 'title' => 'x', 'description' => 'x', 'starting_cost' => 2000,
                      'features' => json_encode(['A', 'B']), 'image_path' => 'assets/images/works/02-EDISON.webp',
                      'is_published' => 0],
    'mediaGroups' => $mediaGroups,
    'action'      => '/admin/content/services/1',
], null);

Harness::check('list field expands JSON into rows', substr_count($servicesForm, 'name="features[]"') === 2);
Harness::check('image field preselects the stored path', str_contains($servicesForm, 'value="assets/images/works/02-EDISON.webp" selected'));
Harness::check('unpublished record leaves toggle unchecked', !str_contains($servicesForm, 'name="is_published" value="1" checked'));

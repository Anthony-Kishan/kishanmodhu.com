<?php

declare(strict_types=1);

/**
 * Seeds the database with the content the site shipped with.
 *
 * Running this reproduces the pre-CMS page exactly, so the migration is
 * verifiable: seed, load the site, and nothing should have moved.
 *
 * Usage:
 *   php database/seed.php [--fresh]     insert directly into the database
 *   php database/seed.php --sql         print SQL to stdout instead
 *
 *   --fresh  Empty the content tables first (settings, works, services, …).
 *            Admin accounts and contact messages are never touched.
 *   --sql    Emit INSERT statements without connecting to any database, for
 *            importing through phpMyAdmin. This is how you seed a shared host
 *            where database/ is not deployed and there is no shell access:
 *
 *              php database/seed.php --sql > seed-data.sql
 */

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\Setting;

$fresh   = in_array('--fresh', $argv, true);
$sqlMode = in_array('--sql', $argv, true);

$contentTables = [
    'settings', 'works', 'services', 'testimonials',
    'experiences', 'stacks', 'marquee_logos', 'certificates', 'social_links',
];

/**
 * Progress goes to stderr so that `--sql > file` captures only SQL.
 */
function report(string $message): void
{
    fwrite(STDERR, $message);
}

/**
 * Column names for a table.
 *
 * In --sql mode these are parsed out of schema.sql, so the script needs no
 * database connection at all.
 *
 * @return array<int, string>
 */
function tableColumns(string $table): array
{
    global $sqlMode;

    if (!$sqlMode) {
        return array_column(Database::select("SHOW COLUMNS FROM `{$table}`"), 'Field');
    }

    static $parsed = null;

    if ($parsed === null) {
        $parsed = [];
        $schema = (string) file_get_contents(__DIR__ . '/schema.sql');

        preg_match_all(
            '/CREATE TABLE IF NOT EXISTS `(\w+)` \((.*?)\n\) ENGINE/s',
            $schema,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as [, $name, $body]) {
            preg_match_all('/^\s+`(\w+)`/m', $body, $columns);
            $parsed[$name] = $columns[1];
        }
    }

    return $parsed[$table] ?? [];
}

/**
 * Render a PHP value as a MySQL literal.
 */
function sqlLiteral(mixed $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }

    $escaped = str_replace(
        ['\\', "'", "\0", "\n", "\r", "\x1a"],
        ['\\\\', "\\'", '\\0', '\\n', '\\r', '\\Z'],
        (string) $value
    );

    return "'" . $escaped . "'";
}

if ($sqlMode) {
    echo "-- Seed data for kishanmodhu.com\n";
    echo "-- Generated " . date('Y-m-d H:i:s') . " by database/seed.php --sql\n";
    echo "--\n";
    echo "-- Import through phpMyAdmin AFTER database/schema.sql.\n";
    echo "-- Safe to re-run: existing rows are left untouched.\n\n";
    echo "SET NAMES utf8mb4;\n";
    echo "START TRANSACTION;\n\n";
} elseif ($fresh) {
    foreach ($contentTables as $table) {
        Database::execute("DELETE FROM `{$table}`");
        Database::execute("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
    }
    report("Cleared content tables.\n");
}

/**
 * Insert rows only when the target table is empty, so re-running the seeder
 * never duplicates or clobbers edited content.
 *
 * @param array<int, array<string, mixed>> $rows
 */
function seed(string $table, array $rows): void
{
    global $sqlMode;

    if (!$sqlMode) {
        $existing = Database::selectOne("SELECT COUNT(*) AS total FROM `{$table}`");

        if ((int) ($existing['total'] ?? 0) > 0) {
            report(str_pad($table, 18) . "skipped (already has rows)\n");

            return;
        }
    }

    // Read the real column list so a default display order is only added to
    // tables that actually have one — `settings`, for instance, does not.
    $tableColumns = tableColumns($table);

    if ($sqlMode) {
        echo "-- {$table} (" . count($rows) . " rows)\n";
    }

    foreach ($rows as $index => $row) {
        if (in_array('sort_order', $tableColumns, true) && !array_key_exists('sort_order', $row)) {
            $row['sort_order'] = $index;
        }

        $unknown = array_diff(array_keys($row), $tableColumns);

        if ($unknown !== []) {
            throw new RuntimeException(sprintf(
                'Seed data for `%s` references unknown column(s): %s',
                $table,
                implode(', ', $unknown)
            ));
        }

        if ($sqlMode) {
            // An explicit primary key plus INSERT IGNORE makes re-importing a
            // no-op rather than a source of duplicate rows.
            $row        = ['id' => $index + 1] + $row;
            $columnList = '`' . implode('`, `', array_keys($row)) . '`';
            $values     = implode(', ', array_map('sqlLiteral', array_values($row)));

            echo "INSERT IGNORE INTO `{$table}` ({$columnList}) VALUES ({$values});\n";

            continue;
        }

        $columns      = array_keys($row);
        $columnList   = '`' . implode('`, `', $columns) . '`';
        $placeholders = ':' . implode(', :', $columns);

        Database::execute("INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})", $row);
    }

    report(str_pad($table, 18) . count($rows) . " rows\n");
}

// ── Settings ────────────────────────────────────────────────────────────────
$settings = [
    // Site
    'brand_name'       => 'Kishan Modhu',
    'logo_path'        => 'assets/images/logo-light.png',
    'location_label'   => 'Dhaka, Bangladesh/',
    'timezone'         => 'Asia/Dhaka',
    'footer_brand'     => 'kishan',
    'footer_copyright' => '© 2024 DESIGN',
    'footer_location'  => 'Dhaka, Bangladesh',

    // SEO & sharing
    'meta_title'       => 'Anthony Kishan',
    'meta_description' => 'Kishan Modhu is a web developer with expertise in front-end and back-end development. Check out my portfolio and blog.',
    'meta_keywords'    => 'Kishan Modhu, web developer, portfolio, front-end development, back-end development, PHP developer, python developer, software developer',
    'og_title'         => 'Kishan Modhu - Web Developer & Aspiring Software Developer',
    'og_description'   => 'Explore my portfolio and learn more about my work as a web developer.',
    'og_image'         => 'assets/images/thumbnail.jpg',
    'person_job_title' => 'Web Developer',
    'person_image'     => 'assets/images/profile-img.jpg',

    // Hero
    'hero_signature'      => 'assets/images/signature-gif.gif',
    'hero_side_image'     => 'assets/images/icons/process-icon-2.svg',
    'hero_heading'        => 'Web',
    'hero_rotating_words' => 'Designer, Developer, Enthusiast',

    // Section headings
    'works_label'       => 'explore',
    'works_description' => 'Gorgeous design. Scroll-stopping content. Memorable campaigns. Development dripping with tech. The proof is in our projects.',
    'about_label'       => "let's work together",
    'about_subtitle'    => 'WEB DEV PASSIONATE ABOUT CREATING',
    'about_subtitle_underlined' => 'MODERN WEB EXPERIENCES',
    'services_label'      => 'discover',
    'experience_label'    => '2022 - present',
    'experience_subtitle' => 'SHOWCASING MY WEB DEVELOPMENT JOURNEY',
    'stack_label'         => 'my skill',
    'stack_heading'       => 'FAVOURITE,STACK',
    'stack_subtitle'      => 'EXPLORE MY CURATED TOP DESIGN PICKS',
    'testimonial_heading' => 'What my Clients Say',

    // About
    'about_profile_image' => 'assets/images/profile-img.jpg',
    'about_resume_path'   => 'assets/kishan-modhu-resume.pdf',
    'about_resume_label'  => 'DOWNLOAD RESUME',
    'about_bio'           => "As a junior web developer, I bring fresh perspectives and modern development practices to every project. With a strong foundation in HTML, CSS, and JavaScript, I create responsive and user-friendly web experiences. I'm passionate about clean code, continuous learning, and staying up-to-date with the latest web technologies.",
    'about_mission'       => "To create engaging, accessible, and performant web experiences that make a positive impact. I'm committed to writing clean, maintainable code and continuously expanding my skills in modern web development.",
    'about_since'         => '[ SINCE. 2021 ]',

    // Contact
    'contact_heading'      => "LET'S BUILD YOUR,WEB IDENTITY",
    'contact_subtitle'     => '"COLLABORATE WITH ME TO CRAFT EXCEPTIONAL DESIGNS,REFLECT YOUR UNIQUE VISION"',
    'contact_button'       => 'CONTACT NOW',
    'contact_side_image'   => 'assets/images/icons/process-icon-1.svg',
    'contact_page_heading' => "LET'S WORK TOGETHER",
    'contact_notify_email' => '',
];

seed('settings', array_map(
    static fn (string $key, string $value): array => ['setting_key' => $key, 'setting_value' => $value],
    array_keys($settings),
    array_values($settings)
));

// ── Works ───────────────────────────────────────────────────────────────────
seed('works', [
    [
        'title'      => 'DevPulse',
        'category'   => 'TECH NEWS & TUTORIALS BLOG',
        'tag'        => '(FULL STACK)',
        'image_path' => 'assets/images/works/01-DEVPULSE.webp',
        'image_alt'  => 'Blog Project',
        'url'        => null,
    ],
    [
        'title'      => 'EDISON',
        'category'   => 'ONLINE EDUCATION',
        'tag'        => '(BRANDING)',
        'image_path' => 'assets/images/works/02-EDISON.webp',
        'image_alt'  => 'Education Project',
        'url'        => null,
    ],
    [
        'title'      => 'LINKIN PARK',
        'category'   => 'MUSIC BAND',
        'tag'        => '(DEMO SITE)',
        'image_path' => 'assets/images/works/02-LINKIN-PARK.webp',
        'image_alt'  => 'Music Project',
        'url'        => null,
    ],
    [
        'title'      => 'ALANZO',
        'category'   => 'CATERING',
        'tag'        => '(DEMO SITE)',
        'image_path' => 'assets/images/works/Alanzo12-min.gif',
        'image_alt'  => 'Catering Project',
        'url'        => null,
    ],
]);

// ── Services ────────────────────────────────────────────────────────────────
seed('services', [
    [
        'title'         => 'Web Development',
        'description'   => 'Crafting Dynamic, Scalable, and Future-Ready Websites That Stand Out.',
        'starting_cost' => 2000,
        'features'      => json_encode([
            'Custom Web Design',
            'Responsive Design',
            'SEO-Friendly Structure',
            'Performance Optimization',
            'Content Management',
            'Ongoing Support',
        ], JSON_THROW_ON_ERROR),
        'image_path'    => 'assets/images/works/01-DEVPULSE.webp',
    ],
    [
        'title'         => 'Web Design',
        'description'   => 'Where Creativity Meets Functionality to Craft Visually Stunning Websites.',
        'starting_cost' => 1500,
        'features'      => json_encode([
            'Bespoke Visual Design',
            'User-Centered Design',
            'Mobile-First Design',
            'Design Prototyping',
            'High-Fidelity Mockups',
            'Cross-Browser Compatibility',
            'Branding Integration',
        ], JSON_THROW_ON_ERROR),
        'image_path'    => 'assets/images/works/02-EDISON.webp',
    ],
    [
        'title'         => 'Graphics Design',
        'description'   => 'Transforming Ideas into Visual Masterpieces That Speak to Your Audience.',
        'starting_cost' => 1000,
        'features'      => json_encode([
            'Custom Logos & Branding',
            'Marketing Materials',
            'Social Media Graphics',
            'Web & App UI Elements',
            'Illustrations & Icons',
            'Print Design',
        ], JSON_THROW_ON_ERROR),
        'image_path'    => 'assets/images/works/The-last-rope.webp',
    ],
]);

// ── Testimonials ────────────────────────────────────────────────────────────
// `source_icon` previously pointed at https://www.fiverr.com/favicon.ico, one
// external request per card. It now uses the bundled Fiverr mark.
$fiverrIcon = 'assets/images/icons/fiverr.png';

seed('testimonials', [
    [
        'name'        => 'intuitionstudio',
        'body'        => 'Great work! He did exactly what I wanted and did even more, I will definitely need his service again.',
        'country'     => 'United Kingdom',
        'date_label'  => '[ SEPTEMBER, 2021 ]',
        'avatar_path' => 'assets/images/testimonials/intuitionstudio.webp',
        'source_icon' => $fiverrIcon,
    ],
    [
        'name'        => 'abigaildarko',
        'body'        => 'Kishan is an exceptional artist!!!.. He designed what I requested to my satisfaction and absolute perfection!!!.. He deserves more than 5stars! I 100% recommend his services if needed. Will definitely order his gig again.',
        'country'     => 'Ghana',
        'date_label'  => '[ MARCH, 2022 ]',
        'avatar_path' => 'assets/images/testimonials/abigaildarko.webp',
        'source_icon' => $fiverrIcon,
    ],
    [
        'name'        => 'grandkadolz',
        'body'        => 'Quick, friendly and efficient. I will most likely use his services again.',
        'country'     => 'Austria',
        'date_label'  => '[ APRIL, 2023 ]',
        'avatar_path' => 'assets/images/testimonials/grandkadolz.png',
        'source_icon' => $fiverrIcon,
    ],
    [
        'name'        => 'abigaildarko',
        'body'        => "Second time ordering this gig and i must say Anthony always exceeds my expectations and I really love his work… Always very neat and perfect.. exactly what I wanted!!! He's actually the best and I 100% recommend his services if needed..Thanks a lot Anthony for always giving out your best.",
        'country'     => 'Ghana',
        'date_label'  => '[ MAY, 2022 ]',
        'avatar_path' => 'assets/images/testimonials/abigaildarko.webp',
        'source_icon' => $fiverrIcon,
    ],
    [
        'name'        => 'grandkadolz',
        'body'        => 'Kishan did exactly what I asked in a timely manner. I will most likely use his services again.',
        'country'     => 'Austria',
        'date_label'  => '[ JUNE, 2023 ]',
        'avatar_path' => 'assets/images/testimonials/grandkadolz.png',
        'source_icon' => $fiverrIcon,
    ],
    [
        'name'        => 'aleezadesign256',
        'body'        => 'Excellent friendly and fast professional service, would definitely recommend!',
        'country'     => 'United Kingdom',
        'date_label'  => '[ AUGUST, 2023 ]',
        'avatar_path' => 'assets/images/testimonials/aleezadesign256.png',
        'source_icon' => $fiverrIcon,
    ],
    [
        'name'        => 'gideonmk',
        'body'        => 'Great work, communicated well and made all the revisions I asked for. Will come back for future work.',
        'country'     => 'United States',
        'date_label'  => '[ JULY, 2023 ]',
        'avatar_path' => 'assets/images/testimonials/gideonmk.webp',
        'source_icon' => $fiverrIcon,
    ],
    [
        'name'        => 'walter34r',
        'body'        => 'Good skills and nice character',
        'country'     => 'United States',
        'date_label'  => '[ AUGUST, 2023 ]',
        'avatar_path' => 'assets/images/testimonials/walter34r.webp',
        'source_icon' => $fiverrIcon,
    ],
]);

// ── Experience ──────────────────────────────────────────────────────────────
seed('experiences', [
    [
        'company'          => 'EUROPEAN IT INSTITUTE',
        'position'         => 'PYTHON DEVELOPMENT INTERN',
        'description'      => 'Completed a 3-month internship focused on Python backend development using Django. Developed and deployed RESTful APIs with Docker, PostgreSQL, and Python OOP principles. Gained hands-on experience in full-stack development and collaborative team work.',
        'date_label'       => '[ JULY/2024 - SEP/2024 ]',
        'date_label_short' => '[ JUL/21 - SEP/24 ]',
        'logo_path'        => 'assets/images/company-logos/euit-logo1.png',
    ],
    [
        'company'          => 'EUROPEAN IT INSTITUTE',
        'position'         => 'WEB DESIGN & DEVELOPMENT',
        'description'      => 'Currently enrolled in a 3-month advanced course on Web Design and Development under a State-Owned Company, Ministry of Finance (NHRDF). Focused on mastering web design, front-end and back-end development using HTML, CSS, JavaScript, PHP, Bootstrap, and Laravel. Preparing for freelancing with hands-on project experience and skill development.',
        'date_label'       => '[ OCT/2024 - PRESENT ]',
        'date_label_short' => '[ OCT/21 - P/T ]',
        'logo_path'        => 'assets/images/company-logos/euit-logo1.png',
    ],
    [
        'company'          => 'FIVERR',
        'position'         => 'GRAPHIC DESIGNER',
        'description'      => 'Beginner-level graphic designer with a focus on illustration & UI designs. Proficient in Adobe Photoshop, Illustrator, and Canva for creating engaging visuals. Passionate about developing creative concepts and designs for various projects.',
        'date_label'       => '[ SEP/2021 - PRESENT ]',
        'date_label_short' => '[ SEP/21 - P/T ]',
        'logo_path'        => $fiverrIcon,
    ],
]);

// ── Favourite stack ─────────────────────────────────────────────────────────
seed('stacks', [
    [
        'name'        => 'HTML',
        'category'    => 'Markup Language',
        'proficiency' => 95,
        'description' => 'HTML is the standard language for creating and structuring content on the web, forming the foundation of web pages by defining elements like text, images, links, and forms.',
        'logo_path'   => 'assets/images/stack-logos/html-1.svg',
    ],
    [
        'name'        => 'css',
        'category'    => 'style sheet Language',
        'proficiency' => 90,
        'description' => 'CSS is a stylesheet language used to define the presentation of HTML elements, enabling the design and layout of web pages with styles like colors, fonts, and spacing.',
        'logo_path'   => 'assets/images/stack-logos/css-3.svg',
    ],
    [
        'name'        => 'Bootstrap',
        'category'    => 'CSS Framework',
        'proficiency' => 95,
        'description' => 'Bootstrap is a popular front-end framework for building responsive and mobile-first websites, providing pre-designed components and a flexible grid system for faster web development.',
        'logo_path'   => 'assets/images/stack-logos/bootstrap-5-1.svg',
    ],
    [
        'name'        => 'JavaScript',
        'category'    => 'Scripting Language',
        'proficiency' => 75,
        'description' => 'JavaScript is a versatile programming language that enables interactive and dynamic behavior on websites, allowing developers to manipulate content, control multimedia, and handle events in real-time.',
        'logo_path'   => 'assets/images/stack-logos/javascript-1.svg',
    ],
    [
        'name'        => 'Python',
        'category'    => 'Programming Language',
        'proficiency' => 92,
        'description' => 'Python is a high-level, versatile programming language known for its simplicity and readability, widely used in web development, data analysis, artificial intelligence, and automation.',
        'logo_path'   => 'assets/images/stack-logos/python-5.svg',
    ],
    [
        'name'        => 'Django',
        'category'    => 'framework',
        'proficiency' => 85,
        'description' => 'Django is a high-level Python web framework that enables rapid development of secure and maintainable web applications, providing built-in features like authentication, database integration, and admin interfaces.',
        'logo_path'   => 'assets/images/stack-logos/django.svg',
    ],
    [
        'name'        => 'postgresql',
        'category'    => 'Database',
        'proficiency' => 85,
        'description' => 'PostgreSQL is a powerful, open-source relational database management system that supports advanced data types, complex queries, and high concurrency, making it ideal for scalable and high-performance applications.',
        'logo_path'   => 'assets/images/stack-logos/postgresql.svg',
    ],
    [
        'name'        => 'Docker',
        'category'    => 'Container platform',
        'proficiency' => 65,
        'description' => 'Docker is a platform for developing, shipping, and running applications in containers, allowing developers to package software with all its dependencies for consistent deployment across different environments.',
        'logo_path'   => 'assets/images/stack-logos/docker-4.svg',
    ],
]);

// ── Marquee logos ───────────────────────────────────────────────────────────
seed('marquee_logos', [
    ['name' => 'HTML',       'logo_path' => 'assets/images/stack-logos/html-1.svg'],
    ['name' => 'CSS',        'logo_path' => 'assets/images/stack-logos/css-3.svg'],
    ['name' => 'BOOTSTRAP',  'logo_path' => 'assets/images/stack-logos/bootstrap-5-1.svg'],
    ['name' => 'JavaScript', 'logo_path' => 'assets/images/stack-logos/javascript-1.svg'],
    ['name' => 'Python',     'logo_path' => 'assets/images/stack-logos/python-5.svg'],
    ['name' => 'Django',     'logo_path' => 'assets/images/stack-logos/django.svg'],
    ['name' => 'LARAVEL',    'logo_path' => 'assets/images/stack-logos/laravel-2.svg'],
    ['name' => 'POSTGRESQL', 'logo_path' => 'assets/images/stack-logos/postgresql.svg'],
    ['name' => 'DOCKER',     'logo_path' => 'assets/images/stack-logos/docker-4.svg'],
    ['name' => 'ARDUINO',    'logo_path' => 'assets/images/stack-logos/arduino-1.svg'],
]);

// ── Certificates ────────────────────────────────────────────────────────────
seed('certificates', [
    [
        'title' => 'Web Design & Dev: Freelancing L3',
        'year'  => '2024',
        'url'   => 'https://drive.google.com/file/d/1ZZzZB0wnT6Dy5UpqSP1dKKmsvGDd7noE/view?usp=drive_link',
    ],
    [
        'title' => 'Python Development',
        'year'  => '2024',
        'url'   => 'https://drive.google.com/file/d/161n6Mb5ZUcSq377idv8XnjrdY8YRyl-b/view?usp=sharing',
    ],
    [
        'title' => 'Graphics Design: Freelancing L3',
        'year'  => '2024',
        'url'   => 'https://drive.google.com/file/d/1ZVwmXoUBUnGTg-1b72N-XB1yrfQDEVYu/view?usp=sharing',
    ],
]);

// ── Social links ────────────────────────────────────────────────────────────
seed('social_links', [
    [
        'label'         => 'GITHUB',
        'url'           => 'https://github.com/Anthony-Kishan',
        'icon_path'     => 'assets/images/icons/github.svg',
        'show_in_about' => 1,
    ],
    [
        'label'         => 'LINKEDIN',
        'url'           => 'https://www.linkedin.com/in/anthony-kishan/',
        'icon_path'     => 'assets/images/icons/linkedin.svg',
        'show_in_about' => 1,
    ],
    [
        'label'         => 'FIVERR',
        'url'           => 'https://www.fiverr.com/anthony_kishan/',
        'icon_path'     => $fiverrIcon,
        'show_in_about' => 1,
    ],
]);

if ($sqlMode) {
    echo "\nCOMMIT;\n";
    report("\nSQL written to stdout. Import it through phpMyAdmin after schema.sql.\n");
} else {
    Setting::flushCache();
    report("\nSeeding complete.\n");
}

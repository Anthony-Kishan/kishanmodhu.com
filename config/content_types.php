<?php

declare(strict_types=1);

/**
 * Declarative registry of every CRUD-managed content type.
 *
 * ── Adding a new content type ────────────────────────────────────────────────
 *   1. Add a table in database/schema.sql (include `sort_order` and
 *      `is_published` if you want ordering / visibility toggles).
 *   2. Add a model in app/Models extending App\Core\Model.
 *   3. Add an entry here.
 * The admin list, create/edit forms, validation, reordering, publish toggle and
 * routes are all generated from this file — no new controller or view needed.
 *
 * ── Field types ──────────────────────────────────────────────────────────────
 *   text · textarea · number · url · email · select · boolean · image · list
 * `list` stores a repeatable set of strings as a JSON column.
 * `rules` uses the vocabulary understood by App\Core\Validator.
 */

return [
    'works' => [
        'label'        => 'Works',
        'singular'     => 'Work',
        'model'        => App\Models\Work::class,
        'description'  => 'Portfolio projects shown in the WORKS grid.',
        'icon'         => 'grid',
        'sortable'     => true,
        'publishable'  => true,
        'list_columns' => ['title', 'category', 'tag'],
        'fields'       => [
            'title' => [
                'label' => 'Project title',
                'type'  => 'text',
                'rules' => ['required', 'max:120'],
                'hint'  => 'Displayed in uppercase over the image.',
            ],
            'category' => [
                'label' => 'Category',
                'type'  => 'text',
                'rules' => ['required', 'max:120'],
                'hint'  => 'e.g. "TECH NEWS & TUTORIALS BLOG"',
            ],
            'tag' => [
                'label' => 'Tag',
                'type'  => 'text',
                'rules' => ['required', 'max:60'],
                'hint'  => 'Shown in brackets, e.g. "(FULL STACK)"',
            ],
            'image_path' => [
                'label' => 'Cover image',
                'type'  => 'image',
                'rules' => ['required', 'max:255'],
            ],
            'image_alt' => [
                'label' => 'Image alt text',
                'type'  => 'text',
                'rules' => ['required', 'max:160'],
                'hint'  => 'Describes the image for screen readers and search engines.',
            ],
            'url' => [
                'label' => 'Project link',
                'type'  => 'url',
                'rules' => ['url', 'max:255'],
                'hint'  => 'Optional. Leave blank for a non-clickable card.',
            ],
        ],
    ],

    'services' => [
        'label'        => 'Services',
        'singular'     => 'Service',
        'model'        => App\Models\Service::class,
        'description'  => 'Accordion entries in the SERVICES section.',
        'icon'         => 'layers',
        'sortable'     => true,
        'publishable'  => true,
        'list_columns' => ['title', 'starting_cost'],
        'fields'       => [
            'title' => [
                'label' => 'Service title',
                'type'  => 'text',
                'rules' => ['required', 'max:120'],
            ],
            'description' => [
                'label' => 'Description',
                'type'  => 'textarea',
                'rules' => ['required', 'max:600'],
            ],
            'starting_cost' => [
                'label' => 'Starting cost (USD)',
                'type'  => 'number',
                'rules' => ['required', 'integer', 'between:0,1000000'],
            ],
            'features' => [
                'label' => 'Key features',
                'type'  => 'list',
                'rules' => ['required'],
                'hint'  => 'One feature per row.',
            ],
            'image_path' => [
                'label' => 'Illustration',
                'type'  => 'image',
                'rules' => ['required', 'max:255'],
            ],
        ],
    ],

    'testimonials' => [
        'label'        => 'Testimonials',
        'singular'     => 'Testimonial',
        'model'        => App\Models\Testimonial::class,
        'description'  => 'Client reviews in the scrolling marquee.',
        'icon'         => 'quote',
        'sortable'     => true,
        'publishable'  => true,
        'list_columns' => ['name', 'country', 'date_label'],
        'fields'       => [
            'name' => [
                'label' => 'Reviewer name',
                'type'  => 'text',
                'rules' => ['required', 'max:120'],
            ],
            'body' => [
                'label' => 'Review',
                'type'  => 'textarea',
                'rules' => ['required', 'max:1000'],
                'hint'  => 'Quote marks are added automatically.',
            ],
            'country' => [
                'label' => 'Country',
                'type'  => 'text',
                'rules' => ['required', 'max:80'],
            ],
            'date_label' => [
                'label' => 'Date label',
                'type'  => 'text',
                'rules' => ['required', 'max:60'],
                'hint'  => 'Shown verbatim, e.g. "[ SEPTEMBER, 2021 ]"',
            ],
            'avatar_path' => [
                'label' => 'Reviewer photo',
                'type'  => 'image',
                'rules' => ['required', 'max:255'],
            ],
            'source_icon' => [
                'label' => 'Source icon',
                'type'  => 'image',
                'rules' => ['required', 'max:255'],
                'hint'  => 'Platform the review came from, e.g. the Fiverr mark.',
            ],
        ],
    ],

    'experiences' => [
        'label'        => 'Experience',
        'singular'     => 'Experience entry',
        'model'        => App\Models\Experience::class,
        'description'  => 'Roles listed in the EXPERIENCE timeline.',
        'icon'         => 'briefcase',
        'sortable'     => true,
        'publishable'  => true,
        'list_columns' => ['company', 'position', 'date_label'],
        'fields'       => [
            'company' => [
                'label' => 'Company',
                'type'  => 'text',
                'rules' => ['required', 'max:160'],
            ],
            'position' => [
                'label' => 'Position',
                'type'  => 'text',
                'rules' => ['required', 'max:160'],
            ],
            'description' => [
                'label' => 'Description',
                'type'  => 'textarea',
                'rules' => ['required', 'max:1000'],
            ],
            'date_label' => [
                'label' => 'Date range',
                'type'  => 'text',
                'rules' => ['required', 'max:60'],
                'hint'  => 'Desktop label, e.g. "[ JULY/2024 - SEP/2024 ]"',
            ],
            'date_label_short' => [
                'label' => 'Date range (mobile)',
                'type'  => 'text',
                'rules' => ['required', 'max:40'],
                'hint'  => 'Compact label, e.g. "[ JUL/21 - SEP/24 ]"',
            ],
            'logo_path' => [
                'label' => 'Company logo',
                'type'  => 'image',
                'rules' => ['required', 'max:255'],
            ],
        ],
    ],

    'stacks' => [
        'label'        => 'Stack',
        'singular'     => 'Stack item',
        'model'        => App\Models\Stack::class,
        'description'  => 'Skills listed in the FAVOURITE STACK section.',
        'icon'         => 'stack',
        'sortable'     => true,
        'publishable'  => true,
        'list_columns' => ['name', 'category', 'proficiency'],
        'fields'       => [
            'name' => [
                'label' => 'Technology',
                'type'  => 'text',
                'rules' => ['required', 'max:80'],
            ],
            'category' => [
                'label' => 'Category',
                'type'  => 'text',
                'rules' => ['required', 'max:80'],
                'hint'  => 'e.g. "Programming Language"',
            ],
            'proficiency' => [
                'label' => 'Proficiency (%)',
                'type'  => 'number',
                'rules' => ['required', 'integer', 'between:0,100'],
            ],
            'description' => [
                'label' => 'Description',
                'type'  => 'textarea',
                'rules' => ['required', 'max:800'],
            ],
            'logo_path' => [
                'label' => 'Logo',
                'type'  => 'image',
                'rules' => ['required', 'max:255'],
            ],
        ],
    ],

    'marquee-logos' => [
        'label'        => 'Marquee logos',
        'singular'     => 'Marquee logo',
        'model'        => App\Models\MarqueeLogo::class,
        'description'  => 'Logos in the scrolling strip between ABOUT and SERVICES.',
        'icon'         => 'marquee',
        'sortable'     => true,
        'publishable'  => true,
        'list_columns' => ['name'],
        'fields'       => [
            'name' => [
                'label' => 'Technology name',
                'type'  => 'text',
                'rules' => ['required', 'max:80'],
                'hint'  => 'Used as the image alt text.',
            ],
            'logo_path' => [
                'label' => 'Logo',
                'type'  => 'image',
                'rules' => ['required', 'max:255'],
            ],
        ],
    ],

    'certificates' => [
        'label'        => 'Certificates',
        'singular'     => 'Certificate',
        'model'        => App\Models\Certificate::class,
        'description'  => 'Credentials listed in the ABOUT section.',
        'icon'         => 'award',
        'sortable'     => true,
        'publishable'  => true,
        'list_columns' => ['title', 'year'],
        'fields'       => [
            'title' => [
                'label' => 'Certificate title',
                'type'  => 'text',
                'rules' => ['required', 'max:160'],
            ],
            'year' => [
                'label' => 'Year',
                'type'  => 'text',
                'rules' => ['required', 'max:20'],
            ],
            'url' => [
                'label' => 'Certificate link',
                'type'  => 'url',
                'rules' => ['url', 'max:500'],
            ],
        ],
    ],

    'social-links' => [
        'label'        => 'Social links',
        'singular'     => 'Social link',
        'model'        => App\Models\SocialLink::class,
        'description'  => 'Links shown in the menu overlay, ABOUT and CONTACT.',
        'icon'         => 'link',
        'sortable'     => true,
        'publishable'  => true,
        'list_columns' => ['label', 'url'],
        'fields'       => [
            'label' => [
                'label' => 'Label',
                'type'  => 'text',
                'rules' => ['required', 'max:60'],
                'hint'  => 'Shown as text in the menu and contact block.',
            ],
            'url' => [
                'label' => 'URL',
                'type'  => 'url',
                'rules' => ['required', 'url', 'max:500'],
            ],
            'icon_path' => [
                'label' => 'Icon',
                'type'  => 'image',
                'rules' => ['required', 'max:255'],
                'hint'  => 'Used for the icon row in the ABOUT section.',
            ],
            'show_in_about' => [
                'label' => 'Show icon in ABOUT section',
                'type'  => 'boolean',
                'rules' => [],
            ],
        ],
    ],
];

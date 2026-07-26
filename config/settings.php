<?php

declare(strict_types=1);

/**
 * Declarative definition of the singleton settings screens.
 *
 * Each top-level key becomes a tab in Admin → Settings. Adding a field here is
 * enough to make it editable and available on the front end via
 * $settings->get('the_key').
 *
 * Field types: text · textarea · number · url · email · image · file · boolean
 */

return [
    'site' => [
        'label'  => 'Site',
        'fields' => [
            'brand_name'      => ['label' => 'Brand name', 'type' => 'text', 'rules' => ['required', 'max:80']],
            'logo_path'       => ['label' => 'Header logo', 'type' => 'image', 'rules' => ['required', 'max:255']],
            'location_label'  => ['label' => 'Header location', 'type' => 'text', 'rules' => ['required', 'max:80'], 'hint' => 'Shown beside the live clock.'],
            'timezone'        => ['label' => 'Clock timezone', 'type' => 'text', 'rules' => ['required', 'max:64'], 'hint' => 'IANA name, e.g. Asia/Dhaka.'],
            'footer_brand'    => ['label' => 'Footer wordmark', 'type' => 'text', 'rules' => ['required', 'max:40']],
            'footer_copyright'=> ['label' => 'Footer copyright', 'type' => 'text', 'rules' => ['required', 'max:120']],
            'footer_location' => ['label' => 'Footer location', 'type' => 'text', 'rules' => ['required', 'max:80']],
        ],
    ],

    'seo' => [
        'label'  => 'SEO & sharing',
        'fields' => [
            'meta_title'       => ['label' => 'Page title', 'type' => 'text', 'rules' => ['required', 'max:120']],
            'meta_description' => ['label' => 'Meta description', 'type' => 'textarea', 'rules' => ['required', 'max:320']],
            'meta_keywords'    => ['label' => 'Meta keywords', 'type' => 'textarea', 'rules' => ['max:320']],
            'og_title'         => ['label' => 'Share title', 'type' => 'text', 'rules' => ['required', 'max:160']],
            'og_description'   => ['label' => 'Share description', 'type' => 'textarea', 'rules' => ['required', 'max:320']],
            'og_image'         => ['label' => 'Share image', 'type' => 'image', 'rules' => ['required', 'max:255']],
            'person_job_title' => ['label' => 'Job title (structured data)', 'type' => 'text', 'rules' => ['required', 'max:80']],
            'person_image'     => ['label' => 'Profile image (structured data)', 'type' => 'image', 'rules' => ['required', 'max:255']],
        ],
    ],

    'hero' => [
        'label'  => 'Hero',
        'fields' => [
            'hero_signature'    => ['label' => 'Signature image', 'type' => 'image', 'rules' => ['required', 'max:255']],
            'hero_side_image'   => ['label' => 'Right-hand graphic', 'type' => 'image', 'rules' => ['required', 'max:255']],
            'hero_heading'      => ['label' => 'Heading', 'type' => 'text', 'rules' => ['required', 'max:40'], 'hint' => 'The static word above the rotating list.'],
            'hero_rotating_words' => [
                'label' => 'Rotating words',
                'type'  => 'text',
                'rules' => ['required', 'max:200'],
                'hint'  => 'Comma-separated, e.g. "Designer, Developer, Enthusiast".',
            ],
        ],
    ],

    'sections' => [
        'label'  => 'Section headings',
        'fields' => [
            'works_label'        => ['label' => 'Works — pill label', 'type' => 'text', 'rules' => ['required', 'max:40']],
            'works_description'  => ['label' => 'Works — intro', 'type' => 'textarea', 'rules' => ['required', 'max:600']],
            'about_label'        => ['label' => 'About — pill label', 'type' => 'text', 'rules' => ['required', 'max:40']],
            'about_subtitle'     => ['label' => 'About — subtitle', 'type' => 'text', 'rules' => ['required', 'max:200']],
            'about_subtitle_underlined' => [
                'label' => 'About — underlined words',
                'type'  => 'text',
                'rules' => ['required', 'max:80'],
                'hint'  => 'The trailing part of the subtitle rendered with an underline.',
            ],
            'services_label'     => ['label' => 'Services — pill label', 'type' => 'text', 'rules' => ['required', 'max:40']],
            'experience_label'   => ['label' => 'Experience — pill label', 'type' => 'text', 'rules' => ['required', 'max:40']],
            'experience_subtitle'=> ['label' => 'Experience — subtitle', 'type' => 'text', 'rules' => ['required', 'max:200']],
            'stack_label'        => ['label' => 'Stack — pill label', 'type' => 'text', 'rules' => ['required', 'max:40']],
            'stack_heading'      => ['label' => 'Stack — heading', 'type' => 'text', 'rules' => ['required', 'max:80'], 'hint' => 'Use a comma to force a line break, e.g. "FAVOURITE,STACK".'],
            'stack_subtitle'     => ['label' => 'Stack — subtitle', 'type' => 'text', 'rules' => ['required', 'max:200']],
            'testimonial_heading'=> ['label' => 'Testimonials — heading', 'type' => 'text', 'rules' => ['required', 'max:120']],
        ],
    ],

    'about' => [
        'label'  => 'About section',
        'fields' => [
            'about_profile_image' => ['label' => 'Profile photo', 'type' => 'image', 'rules' => ['required', 'max:255']],
            'about_resume_path'   => ['label' => 'Résumé file', 'type' => 'file', 'rules' => ['required', 'max:255']],
            'about_resume_label'  => ['label' => 'Résumé button text', 'type' => 'text', 'rules' => ['required', 'max:60']],
            'about_bio'           => ['label' => 'About me', 'type' => 'textarea', 'rules' => ['required', 'max:1500']],
            'about_mission'       => ['label' => 'My mission', 'type' => 'textarea', 'rules' => ['required', 'max:1500']],
            'about_since'         => ['label' => 'Since badge', 'type' => 'text', 'rules' => ['required', 'max:40']],
        ],
    ],

    'contact' => [
        'label'  => 'Contact',
        'fields' => [
            'contact_heading'    => ['label' => 'Home CTA heading', 'type' => 'text', 'rules' => ['required', 'max:120'], 'hint' => 'Use a comma to force a line break.'],
            'contact_subtitle'   => ['label' => 'Home CTA subtitle', 'type' => 'textarea', 'rules' => ['required', 'max:400'], 'hint' => 'Use a comma to force a line break.'],
            'contact_button'     => ['label' => 'Home CTA button text', 'type' => 'text', 'rules' => ['required', 'max:40']],
            'contact_side_image' => ['label' => 'Home CTA graphic', 'type' => 'image', 'rules' => ['required', 'max:255']],
            'contact_page_heading' => ['label' => 'Contact page heading', 'type' => 'text', 'rules' => ['required', 'max:120']],
            'contact_notify_email' => ['label' => 'Notification email', 'type' => 'email', 'rules' => ['email', 'max:160'], 'hint' => 'New submissions are emailed here. Leave blank to disable.'],
        ],
    ],
];

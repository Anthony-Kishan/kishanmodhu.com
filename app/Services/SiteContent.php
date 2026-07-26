<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Certificate;
use App\Models\Experience;
use App\Models\MarqueeLogo;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\Stack;
use App\Models\Testimonial;
use App\Models\Work;

/**
 * Assembles everything the public pages need in one pass.
 *
 * Centralising the reads here keeps controllers thin and makes the query count
 * per page obvious: one per section, nine in total for the home page.
 */
final class SiteContent
{
    /**
     * Shared chrome (header, footer, meta) used by every page.
     *
     * @return array<string, mixed>
     */
    public function shared(): array
    {
        $settings = new Setting();

        return [
            'settings'    => $settings,
            'socialLinks' => (new SocialLink())->published(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function home(): array
    {
        return array_merge($this->shared(), [
            'works'            => (new Work())->published(),
            'certificates'     => (new Certificate())->published(),
            'aboutSocialLinks' => (new SocialLink())->forAboutSection(),
            'marqueeLogos'     => (new MarqueeLogo())->published(),
            'services'         => (new Service())->published(),
            'experiences'      => (new Experience())->published(),
            'stacks'           => (new Stack())->published(),
            'testimonials'     => (new Testimonial())->published(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function contact(): array
    {
        return array_merge($this->shared(), [
            // The header's in-page anchors have to jump back to the home page
            // from any URL other than "/".
            'homePrefix' => '/',
        ]);
    }
}

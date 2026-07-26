<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\SiteContent;

final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('site/home', (new SiteContent())->home());
    }

    /**
     * The site used to be served as index.html; keep those URLs working.
     */
    public function legacyIndex(Request $request): Response
    {
        return Response::redirect('/', 301);
    }
}

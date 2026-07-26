<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\ContentType;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Message;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $counts = [];

        foreach (ContentType::all() as $type) {
            $counts[$type->key] = [
                'type'  => $type,
                'total' => $type->model()->count(),
            ];
        }

        $messages = new Message();

        return $this->view('admin/dashboard', [
            'pageTitle'      => 'Dashboard',
            'counts'         => $counts,
            'messageTotal'   => $messages->count(),
            'unreadMessages' => $messages->unreadCount(),
        ], 'layouts/admin');
    }
}

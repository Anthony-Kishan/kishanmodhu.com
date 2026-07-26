<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Message;

/**
 * Inbox for contact-form submissions.
 */
final class MessageController extends Controller
{
    public function index(Request $request): Response
    {
        $messages = new Message();

        return $this->view('admin/messages/index', [
            'pageTitle' => 'Messages',
            'records'   => $messages->all(),
            'unread'    => $messages->unreadCount(),
        ], 'layouts/admin');
    }

    public function show(Request $request, string $id): Response
    {
        $messages = new Message();
        $record   = $messages->find((int) $id);

        if ($record === null) {
            return Response::notFound();
        }

        // Opening a message marks it read.
        if ((int) $record['is_read'] === 0) {
            $messages->markRead((int) $id);
            $record['is_read'] = 1;
        }

        return $this->view('admin/messages/show', [
            'pageTitle' => 'Message from ' . $record['first_name'] . ' ' . $record['last_name'],
            'record'    => $record,
        ], 'layouts/admin');
    }

    public function destroy(Request $request, string $id): Response
    {
        (new Message())->delete((int) $id);

        return $this->redirectWithSuccess('/admin/messages', 'Message deleted.');
    }

    public function markAllRead(Request $request): Response
    {
        (new Message())->markAllRead();

        return $this->redirectWithSuccess('/admin/messages', 'All messages marked as read.');
    }
}

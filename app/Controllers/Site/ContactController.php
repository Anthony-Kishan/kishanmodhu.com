<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Message;
use App\Models\Setting;
use App\Services\SiteContent;

/**
 * Contact page and submission handling.
 *
 * Replaces the previous third-party form endpoint: submissions are validated,
 * throttled, stored, and surfaced in the admin inbox.
 */
final class ContactController extends Controller
{
    /** Maximum submissions allowed from one IP address per window. */
    private const RATE_LIMIT        = 5;
    private const RATE_LIMIT_WINDOW = 3600;

    /** Must stay in step with the <option> values in Views/site/contact.php. */
    private const COMPANY_TYPES = ['startup', 'enterprise', 'agency', 'other'];

    public function show(Request $request): Response
    {
        return $this->view('site/contact', (new SiteContent())->contact());
    }

    public function legacyContact(Request $request): Response
    {
        return Response::redirect('/contact', 301);
    }

    public function submit(Request $request): Response
    {
        if (!Csrf::verify((string) $request->input('_token'))) {
            Session::flash('error', 'Your session expired. Please send that again.');

            return Response::redirect('/contact#contactForm');
        }

        // Honeypot: a real browser leaves this hidden field empty. Bots that
        // fill every input get a success response but nothing is stored.
        if ((string) $request->input('website') !== '') {
            return $this->redirectWithSuccess('/contact#contactForm', 'Thanks — your message has been sent.');
        }

        $messages = new Message();

        if ($messages->recentCountForIp($request->ip(), self::RATE_LIMIT_WINDOW) >= self::RATE_LIMIT) {
            Session::flash('error', 'You have sent several messages recently. Please try again later.');
            Session::flashInput($request->all());

            return Response::redirect('/contact#contactForm');
        }

        $input = [
            'first_name'   => (string) $request->input('first_name'),
            'last_name'    => (string) $request->input('last_name'),
            'email'        => (string) $request->input('email'),
            'company_type' => (string) $request->input('company_type'),
            'budget'       => (string) $request->input('budget'),
            'body'         => (string) $request->input('body'),
        ];

        $validator = new Validator(
            $input,
            [
                'first_name'   => ['required', 'max:80'],
                'last_name'    => ['required', 'max:80'],
                'email'        => ['required', 'email', 'max:160'],
                'company_type' => ['required', 'in:' . implode(',', self::COMPANY_TYPES)],
                'budget'       => ['required', 'max:80'],
                'body'         => ['required', 'min:10', 'max:5000'],
            ],
            [
                'first_name'   => 'First name',
                'last_name'    => 'Last name',
                'email'        => 'Email',
                'company_type' => 'Company type',
                'budget'       => 'Budget',
                'body'         => 'Message',
            ]
        );

        if ($validator->fails()) {
            return $this->redirectWithErrors('/contact#contactForm', $validator->errors(), $input);
        }

        $messages->create($input + [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->notify($input);

        return $this->redirectWithSuccess('/contact#contactForm', 'Thanks — your message has been sent.');
    }

    /**
     * Email the site owner, if a notification address is configured.
     *
     * Failure here must not affect the visitor: the message is already stored.
     *
     * @param array<string, string> $input
     */
    private function notify(array $input): void
    {
        $to = (new Setting())->get('contact_notify_email');

        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $subject = sprintf('New enquiry from %s %s', $input['first_name'], $input['last_name']);

        $body = sprintf(
            "Name: %s %s\nEmail: %s\nCompany type: %s\nBudget: %s\n\n%s\n",
            $input['first_name'],
            $input['last_name'],
            $input['email'],
            $input['company_type'],
            $input['budget'],
            $input['body']
        );

        // Reply-To carries the visitor's address; From stays on our own domain
        // so the message is not rejected by SPF/DMARC.
        $headers = [
            'From'         => sprintf('no-reply@%s', $_SERVER['HTTP_HOST'] ?? 'localhost'),
            'Reply-To'     => $input['email'],
            'Content-Type' => 'text/plain; charset=utf-8',
        ];

        @mail($to, $subject, $body, $headers);
    }
}

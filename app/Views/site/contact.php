<?php
/**
 * Contact page.
 *
 * The form now posts to this application instead of a third-party endpoint.
 * Every control has a `name` (the original markup only had `id`, so nothing was
 * ever submitted), plus a CSRF token and a honeypot field.
 *
 * @var App\Models\Setting $settings
 */

use App\Core\Session;

$success = Session::pullFlash('success');
$error   = Session::pullFlash('error');
$errors  = errors();

$companyTypes = [
    'startup'    => 'Startup',
    'enterprise' => 'Enterprise',
    'agency'     => 'Agency',
    'other'      => 'Other',
];
?>
<!-- CONTACT SECTION START -->
<section class="contact-page">
    <div class="container">
        <h1 class="mb-5"><?= e($settings->get('contact_page_heading')) ?><span class="green-square"></span></h1>

        <?php if ($success !== null): ?>
            <div class="alert alert-success" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="list-unstyled mb-0">
                    <?php foreach ($errors as $message): ?>
                        <li><?= e($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="contactForm" action="/contact" method="POST" novalidate>
            <?= csrf_field() ?>

            <!-- Honeypot: hidden from people, tempting to bots. -->
            <div class="d-none" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="row mb-5 g-5">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="firstName" class="mb-4">
                        <span class="green-dot"></span>
                        FIRST NAME
                    </label>
                    <input type="text" class="form-control" id="firstName" name="first_name" placeholder="Kishan" value="<?= e(old('first_name')) ?>" maxlength="80" required>
                </div>
                <div class="col-md-6">
                    <label for="lastName" class="mb-4">
                        <span class="green-dot"></span>
                        LAST NAME
                    </label>
                    <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Modhu" value="<?= e(old('last_name')) ?>" maxlength="80" required>
                </div>
            </div>
            <div class="mb-5">
                <label for="email" class="mb-4">
                    <span class="green-dot"></span>
                    HOW CAN I REACH YOU?
                </label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="<?= e(old('email')) ?>" maxlength="160" required>
            </div>
            <div class="row mb-5 g-5">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="companyType" class="mb-4">
                        <span class="green-dot"></span>
                        WHAT'S THE TYPE OF YOUR COMPANY?
                    </label>
                    <select class="form-control" id="companyType" name="company_type" required>
                        <option value="" disabled<?= old('company_type') === '' ? ' selected' : '' ?>>Freelancer with established brand</option>
                        <?php foreach ($companyTypes as $value => $label): ?>
                            <option value="<?= e($value) ?>"<?= old('company_type') === $value ? ' selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="budget" class="mb-4">
                        <span class="green-dot"></span>
                        YOUR BUDGET
                    </label>
                    <input type="text" class="form-control" id="budget" name="budget" placeholder="YOUR BUDGET" value="<?= e(old('budget')) ?>" maxlength="80" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="message" class="mb-4">
                    <span class="green-dot"></span>
                    MESSAGE
                </label>
                <textarea class="form-control mb-5" id="message" name="body" rows="7" placeholder="MESSAGE" maxlength="5000" required><?= e(old('body')) ?></textarea>
            </div>
            <button type="submit" class="btn btn-submit">SUBMIT NOW</button>
        </form>
    </div>
</section>
<!-- CONTACT SECTION END -->

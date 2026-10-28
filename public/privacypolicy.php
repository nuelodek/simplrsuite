<?php
require_once __DIR__ . '/../app/view.php';
$privacyEmail = config('PRIVACY_CONTACT_EMAIL');
$privacyEmail = $privacyEmail !== '' ? $privacyEmail : 'NUELODEKUNLE20@GMAIL.COM';
$privacyPhone = config('PRIVACY_CONTACT_PHONE');
$privacyPhone = $privacyPhone !== '' ? $privacyPhone : '+234 702 680 6672';
$privacyAddress = config('PRIVACY_BUSINESS_ADDRESS');
$privacyAddress = $privacyAddress !== '' ? $privacyAddress : 'Shop 12 Mosadioluwa Complex, Idi Ose, Ibadan, Oyo State, Nigeria, 234000';
$privacyDate = config('PRIVACY_REVIEWED_ON');
$privacyReady = config('PRIVACY_APPROVED','false') === 'true';
page_head('Privacy Policy', ['description' => 'Learn how Simplr Suite collects, uses, and protects personal information.']);
?>
<body class="landing">
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="public-header public-container">
        <?= brand() ?>
        <nav class="public-nav" aria-label="Main navigation"><a href="/">Home</a></nav>
    </header>
    <main id="main" class="privacy-main public-container">
        <div class="privacy-heading">
            <p class="eyebrow">Privacy notice</p>
            <h1>Privacy Policy</h1>
            <p>How Simplr Suite handles personal information and the choices available to you.</p>
        </div>
        <article class="privacy-card">
    <?php if (!$privacyReady): ?><p role="status"><strong>Draft for review.</strong> Business contact details and the final data-handling notice must be confirmed before public launch.</p><?php endif; ?>

    <h2>Introduction</h2>
    <p>Welcome to Simplr Suite. We respect your privacy and are committed to protecting your personal data. This privacy policy explains how we handle personal information across the Simplr Suite platform and associated public pages, and it sets out your rights and choices.</p>

    <h2>Important Information and Who We Are</h2>

    <h3>Purpose of this Privacy Policy</h3>
    <p>This privacy policy explains how Simplr Suite handles personal data across the website, business workspace, talent profile pages, and employee work portal, including the information you provide when you create an account, request a service, or apply for a role. It also explains our use of cookies and related technologies, and the choices available to you regarding your information.</p>

    <h3>Controller</h3>
    <p>Legal business name: <?= e((config('PRIVACY_LEGAL_NAME') ?: 'Numerox Consulting')) ?></p>
    <p>Business name registration number: <?= e(config('PRIVACY_REGISTRATION_NUMBER') ?: 'BN 7228508') ?></p>
    <p>Date of registration: <?= e(config('PRIVACY_REGISTERED_ON') ?: '2023-11-17') ?></p>
    <p>Registration status: <?= e(ucfirst(config('PRIVACY_REGISTRATION_STATUS') ?: 'inactive')) ?> (reported by the business owner; confirm or update with the CAC before production launch).</p>
    <p>Business type: <?= e(config('PRIVACY_BUSINESS_TYPE') ?: 'Sole proprietor') ?></p>
    <p>Numerox Consulting is the controller and responsible for your personal data (collectively referred to as "Simplr", "we", "us" or "our" in this privacy policy). Simplr Suite is the product and service operated by Numerox Consulting. The business owner must confirm the registration and legal details and approve this notice before production launch.</p>

    <h3>Contact Details</h3>
    <p>If you have any questions about this privacy policy or our privacy practices, please don't hesitate to contact us in the following ways:</p>
    <ul>
        <li><strong>Email address:</strong> <?= filter_var($privacyEmail,FILTER_VALIDATE_EMAIL) ? e($privacyEmail) : 'Not yet configured' ?></li>
        <li><strong>Postal address:</strong> <?= e($privacyAddress) ?></li>
        <li><strong>Phone number:</strong> <?= e($privacyPhone) ?></li>
    </ul>

    <h3>Changes to the Privacy Policy and Your Duty to Inform Us of Changes</h3>
    <p class="privacy-last-updated">This version was last updated on <?= preg_match('/^\d{4}-\d{2}-\d{2}$/D',$privacyDate) ? e($privacyDate) : 'a date still awaiting review' ?>.</p>
    <p>We keep our privacy policy under regular review and may update it from time to time to reflect changes in our business practices or legal requirements. We encourage you to check this page periodically to stay informed about our privacy practices. It is important that the personal data we hold about you is accurate and current. Please keep us informed if your personal data changes during your relationship with us.</p>

    <h2>The Data We Collect About You</h2>

    <h3>Personal Data</h3>
    <p>Personal data, or personal information, means any information about an individual or company from which that entity can be identified. This includes data about our clients, their employees, and any related individuals. We may collect, use, store and transfer different kinds of personal data about you, which we have grouped together as follows:</p>
    <ul>
        <li>Identity Data: includes first name, last name, username or similar identifier, title, date of birth, and gender.</li>
        <li>Contact Data: includes billing address, delivery address, email address, and telephone numbers.</li>
        <li>Financial Data: includes bank account and payment card details.</li>
        <li>Transaction Data: includes details about payments to and from you and other details of products and services you have purchased from us.</li>
        <li>Technical Data: includes internet protocol (IP) address, your login data, browser type and version, time zone setting and location, browser plug-in types and versions, operating system and platform, and other technology on the devices you use to access this website.</li>
    </ul>

    <h2>Talent Profiles and Job Applications</h2>
    <p>Talent directory profiles start private. Listing a profile shares its name, industry and professional details with verified business accounts, without the sign-in email. Recruiters may save a listed profile to their company shortlist, invite the person to apply for a published role, or send a direct hire offer. Invitations and offers appear in the talent account, and an email notification may be sent to the account address. An offer can be declined; accepting it shares the current profile and contact email with that employer and stores a copy in its workspace.</p>
    <p>Applying for a job is a separate action: you review and confirm sharing a copy of your profile, contact email, application note and optional PDF resume with the employer. Later profile edits do not change the submitted copy.</p>
    <p>Application records are available to the applicant and authorized reviewers in the employer’s workspace. Resume files require authentication and are downloaded as attachments. Withdrawing an application closes review and disables future employer downloads of that resume through Simplr. It does not erase the submitted record or copies previously downloaded. Record retention periods and the contact process for data requests still require business review before launch.</p>
        </article>
    </main>
    <footer class="public-footer public-container">
        <?= brand() ?>
        <span>© <?= date('Y') ?> Simplr Suite. Built for better work.</span>
        <div class="footer-links"><a href="/">Home</a><a href="/users/admin/login.php">Sign in</a></div>
    </footer>
</body>
</html>
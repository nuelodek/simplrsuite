<?php
require_once __DIR__ . '/../app/bootstrap.php';
$privacyEmail = config('PRIVACY_CONTACT_EMAIL');
$privacyDate = config('PRIVACY_REVIEWED_ON');
$privacyReady = config('PRIVACY_APPROVED','false') === 'true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Simplr Business Suites</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #333;
            margin-top: 30px;
        }
        p {
            margin: 15px 0;
        }
        .last-updated {
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Privacy Policy</h1>
    <?php if (!$privacyReady): ?><p role="status"><strong>Draft for review.</strong> Business contact details and the final data-handling notice must be confirmed before public launch.</p><?php endif; ?>

    <h2>Introduction</h2>
    <p>Welcome to Simplr Business Suites. We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you about how we look after your personal data when you visit our website (regardless of where you visit it from) and tell you about your privacy rights and how the law protects you. We encourage you to read this policy carefully to understand our practices regarding your personal information and how we will treat it.</p>

    <h2>Important Information and Who We Are</h2>

    <h3>Purpose of this Privacy Policy</h3>
    <p>This privacy policy aims to give you comprehensive information on how Simplr Business Suites collects and processes your personal data through your use of this website, including any data you may provide through this website when you sign up for our services, purchase a product or service, or take part in a competition. It also explains how we use cookies and similar technologies, and your rights regarding the personal information we hold about you.</p>

    <h3>Controller</h3><p>Legal business name: <?= e((config('PRIVACY_LEGAL_NAME') ?: 'Not yet configured')) ?></p>
    <p>Simplr Business Suites is the controller and responsible for your personal data (collectively referred to as "Simplr", "we", "us" or "our" in this privacy policy). We are committed to ensuring that all personal data we process is done so in accordance with applicable data protection laws.</p>

    <h3>Contact Details</h3>
    <p>If you have any questions about this privacy policy or our privacy practices, please don't hesitate to contact us in the following ways:</p>
    <ul>
        <li><strong>Email address:</strong> <?= filter_var($privacyEmail,FILTER_VALIDATE_EMAIL) ? e($privacyEmail) : 'Not yet configured' ?></li>
        <li><strong>Postal address:</strong> <?= e((config('PRIVACY_BUSINESS_ADDRESS') ?: 'Not yet configured')) ?></li>
        <li><strong>Phone number:</strong> <?= e((config('PRIVACY_CONTACT_PHONE') ?: 'Not provided')) ?></li>
    </ul>

    <h3>Changes to the Privacy Policy and Your Duty to Inform Us of Changes</h3>
    <p class="last-updated">This version was last updated on <?= preg_match('/^\d{4}-\d{2}-\d{2}$/D',$privacyDate) ? e($privacyDate) : 'a date still awaiting review' ?>.</p>
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
    <p>Talent directory profiles start private. Listing a profile shares its professional details with verified business accounts, without the sign-in email. Applying for a job is a separate action: you review and confirm sharing a copy of your profile, contact email, application note and optional PDF resume with the employer. Later profile edits do not change the submitted copy.</p>
    <p>Application records are available to the applicant and authorized reviewers in the employer’s workspace. Resume files require authentication and are downloaded as attachments. Withdrawing an application closes review and disables future employer downloads of that resume through Simplr. It does not erase the submitted record or copies previously downloaded. Record retention periods and the contact process for data requests still require business review before launch.</p>
    
</body>
</html>
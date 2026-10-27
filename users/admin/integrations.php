<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/mail.php';
require_once __DIR__ . '/../../app/paystack.php';
require_once __DIR__ . '/../../app/github.php';
$db = database(); $user = admin_user($db);
suite_start('Integrations','integrations',$user);
$legacyIntegrations = [
    'Finance & payroll' => ['ADP','Bill.com','Chargebee','Expensify','FreshBooks','Gusto','NetSuite','Paychex','PayPal','Plaid','QuickBooks','Recurly','Sage','Square','Stripe','Tipalti','Wave','Workday','Xero','Zoho Books'],
    'Product development' => ['Asana','Azure DevOps','Confluence','Figma','GitLab','Jira','Slack','Trello','Zapier'],
    'Risk management' => ['Acuity','Archer','BWise','CAMMS','CURA','Eramba','Galvanize','LogicManager','MetricStream','OnSpring','Optial','Protecht','Resolver','Riskonnect','RiskRecon','RiskTools','RiskVision','SAP GRC','ServiceNow GRM','Sword GRC'],
];
$legacyLogos = [
    'PayPal' => 'pngimg.com - paypal_PNG7.png',
    'Plaid' => 'Plaid_(company)-Vertical-White-Dark-Background-Logo.wine.svg',
    'QuickBooks' => 'quickbooks.png',
    'Square' => 'square-logo-png-transparent.png',
    'Xero' => 'xero.jpg',
];
?>
<div class="page-heading"><div><h1>Integrations</h1><p>Services available to your workspace and their connection status.</p></div></div>
<?php $github = github_connection($db,(int)$user['company_id']); $githubRepos = (int)db_one($db,'SELECT COUNT(*) AS count FROM github_repository_links WHERE company_id = ?',[(int)$user['company_id']])['count']; ?>
<section class="integration-feature"><span class="integration-logo github"><?= icon('github') ?></span><div><div class="integration-feature-title"><h2>GitHub</h2><span class="badge <?= $github ? 'active' : '' ?>"><?= $github ? 'Connected' : 'Available' ?></span></div><p><?= $github ? 'Connected as @' . e($github['account_login']) . ' · ' . $githubRepos . ' linked ' . ($githubRepos === 1 ? 'repository' : 'repositories') . '. Issues, pull requests and commits appear on linked project pages.' : 'Link repositories to projects, see issues, pull requests and commits beside your work, and sync issues into tasks.' ?></p></div><a class="button<?= $github ? ' secondary' : '' ?>" href="/users/admin/github.php"><?= $github ? 'Manage' : icon('github') . ' Connect GitHub' ?></a></section>
<div class="integration-grid"><section class="integration-card"><div class="feature-icon"><?= icon('plug') ?></div><h2>Account email</h2><span class="badge"><?= config('APP_ENV') === 'local' && config('MAIL_TRANSPORT') === 'log' ? 'Local test delivery' : (mail_ready() ? 'Configured · delivery unverified' : 'Not connected') ?></span><p>Verification and password recovery emails help your team access their workspace.</p><small><?= config('APP_ENV') === 'local' && config('MAIL_TRANSPORT') === 'log' ? 'Emails are saved locally for testing. No real messages are sent.' : 'Your workspace administrator manages the email connection.' ?></small></section>
<section class="integration-card"><div class="provider-logo-mark paystack" aria-hidden="true">Paystack</div><h2>Paystack billing</h2><span class="badge"><?= paystack_ready() ? (config('PAYSTACK_MODE','test') === 'live' ? 'Connected' : 'Test mode') : 'Not available yet' ?></span><p>Hosted checkout for prepaid toolkit subscriptions. Server verification updates access after a successful payment.</p><a class="text-link" href="/users/admin/billing.php">View billing</a><small><?= paystack_ready() ? 'Payments are processed by Paystack. Toolkit access updates once the payment is verified.' : 'Online naira payments are not available yet. Contact Simplr support to renew your toolkits.' ?></small></section>
<section class="integration-card"><div class="provider-logo-mark flutterwave" aria-hidden="true">Flutterwave</div><h2>Flutterwave dollar billing</h2><span class="badge"><?= flutterwave_ready() ? (config('PAYSTACK_MODE','test') === 'live' ? 'Connected' : 'Test mode') : 'Not available yet' ?></span><p>Pay for the same workspace toolkits in US dollars, using Flutterwave hosted checkout.</p><a class="text-link" href="/users/admin/billing.php?currency=USD">View dollar billing</a><small><?= flutterwave_ready() ? 'Payments are processed by Flutterwave. Toolkit access updates once the payment is verified.' : 'Online dollar payments are not available yet. Contact Simplr support to renew your toolkits.' ?></small></section>
<section class="integration-card"><div class="feature-icon">in</div><h2>LinkedIn company page</h2><span class="badge"><?= job_social_sync_ready('linkedin') ? 'Configured' : 'Not connected' ?></span><p>Publish a verified role to a connected LinkedIn company page when you pick it during job creation.</p><small>Direct posting is switched on by the Simplr team. Contact support to connect your company page.</small></section>
<section class="integration-card"><div class="feature-icon">fb</div><h2>Facebook company page</h2><span class="badge"><?= job_social_sync_ready('facebook') ? 'Configured' : 'Not connected' ?></span><p>Share a published role on your Facebook business page and keep your hiring posts in one place.</p><small>Direct posting is switched on by the Simplr team. Contact support to connect your business page.</small></section>
<section class="integration-card"><div class="feature-icon">ig</div><h2>Instagram business account</h2><span class="badge"><?= job_social_sync_ready('instagram') ? 'Configured' : 'Not connected' ?></span><p>Publish a branded job-card image with the job link in the caption when your Instagram Business profile is connected.</p><small>Direct posting is switched on by the Simplr team. Contact support to connect your business profile.</small></section>
<section class="integration-card"><div class="feature-icon"><?= icon('briefcase') ?></div><h2>Payroll & banking</h2><span class="badge">Not connected</span><p>Bank accounts, payouts, and payroll processing need a connected provider before they can be used.</p><small>No bank balances or transactions are shown until a connection is verified.</small></section>
<section class="integration-card"><div class="feature-icon"><?= icon('grid') ?></div><h2>Workspace records</h2><span class="badge active">Available</span><p>Projects, tasks, CRM, finance records, and operations share your business workspace.</p><a class="text-link" href="/users/admin/modules.php">Open your tools <?= icon('arrow') ?></a></section></div>
<section class="legacy-integrations" aria-labelledby="legacy-integrations-title">
    <div class="section-heading"><div><h2 id="legacy-integrations-title">More integrations on the way</h2><p>We are building connectors for the services below. They are not connected yet; tell us which one matters most to your business.</p></div></div>
    <div class="catalog-search"><span class="search-field"><?= icon('search') ?><label class="sr-only" for="catalog-search">Search integrations</label><input id="catalog-search" class="filter-input" type="search" placeholder="Search 49 planned integrations, e.g. Slack, Xero, Jira…" autocomplete="off" data-catalog-search></span><span class="count-label" data-catalog-count aria-live="polite"></span></div>
    <p class="catalog-empty subtle" data-catalog-empty hidden>No planned integration matches that name. Let us know what you need and we will consider it.</p>
    <div class="legacy-integration-groups">
        <?php foreach ($legacyIntegrations as $category => $services): ?>
            <details class="legacy-integration-group" open>
                <summary><?= e($category) ?><span class="badge"><?= count($services) ?> planned</span></summary>
                <ul class="legacy-integration-list">
                    <?php foreach ($services as $service): ?>
                        <li class="legacy-integration-item">
                            <span class="legacy-integration-logo<?= $service === 'Plaid' ? ' plaid-mark' : '' ?>">
                                <?php if (isset($legacyLogos[$service])): ?>
                                    <img src="/public/images/<?= e(rawurlencode($legacyLogos[$service])) ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <span aria-hidden="true"><?= e(substr($service, 0, 1)) ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="legacy-integration-name"><?= e($service) ?></span>
                            <span class="badge">Planned</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endforeach; ?>
    </div>
    <small>Product names and logos belong to their respective owners. Listing a service here does not mean it is connected or endorsed.</small>
</section>
<p><a class="text-link" href="/users/developers/documentation.php">Read the workspace API guide <?= icon('arrow') ?></a></p>
<?php suite_end(); ?>

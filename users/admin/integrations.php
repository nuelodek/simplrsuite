<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/mail.php';
require_once __DIR__ . '/../../app/paystack.php';
require_once __DIR__ . '/../../app/github.php';
require_once __DIR__ . '/../../app/quickbooks.php';
require_once __DIR__ . '/../../app/xero.php';
require_once __DIR__ . '/../../app/workday.php';
require_once __DIR__ . '/../../app/zoho_books.php';
require_once __DIR__ . '/../../app/google-workspace.php';
$db = database(); $user = admin_user($db);
suite_start('Integrations','integrations',$user);
$legacyIntegrations = [
    'Finance & payroll' => ['ADP','Bill.com','Chargebee','Expensify','FreshBooks','Gusto','NetSuite','Paychex','PayPal','Plaid','Recurly','Sage','Square','Stripe','Tipalti','Wave'],
    'Product development' => ['Asana','Azure DevOps','Confluence','Figma','GitLab','Jira','Slack','Trello','Zapier'],
    'Risk management' => ['Acuity','Archer','BWise','CAMMS','CURA','Eramba','Galvanize','LogicManager','MetricStream','OnSpring','Optial','Protecht','Resolver','Riskonnect','RiskRecon','RiskTools','RiskVision','SAP GRC','ServiceNow GRM','Sword GRC'],
];
$legacyLogos = [
    'PayPal' => 'pngimg.com - paypal_PNG7.png',
    'Plaid' => 'Plaid_(company)-Vertical-White-Dark-Background-Logo.wine.svg',
    'Square' => 'square-logo-png-transparent.png',
];
?>
<div class="page-heading"><div><h1>Integrations</h1><p>Services available to your workspace and their connection status.</p></div></div>
<?php $githubAccounts = github_connections($db,(int)$user['company_id']); $github = $githubAccounts[0] ?? null; $githubRepos = (int)db_one($db,'SELECT COUNT(*) AS count FROM github_repository_links WHERE company_id = ?',[(int)$user['company_id']])['count']; ?>
<section class="integration-feature"><span class="integration-logo github"><?= icon('github') ?></span><div><div class="integration-feature-title"><h2>GitHub</h2><span class="badge <?= $github ? 'active' : '' ?>"><?= $github ? 'Connected' : 'Available' ?></span></div><p><?= $github ? 'Connected as ' . e(implode(', ',array_map(fn($account)=>'@' . $account['account_login'],$githubAccounts))) . ' · ' . $githubRepos . ' linked ' . ($githubRepos === 1 ? 'repository' : 'repositories') . '. Issues, pull requests and commits appear on linked project pages.' : 'Link repositories to projects, see issues, pull requests and commits beside your work, and sync issues into tasks.' ?></p></div><?php if ($github): ?><a class="button secondary" href="/users/admin/github.php">Manage</a><?php elseif (github_oauth_ready()): ?><a class="button github-button" href="/users/admin/github.php?oauth=start"><?= icon('github') ?> Connect GitHub</a><?php else: ?><a class="button secondary" href="/users/admin/github.php">Learn more</a><?php endif; ?></section>
<?php $quickbooks = quickbooks_connection($db,(int)$user['company_id']); $quickbooksStatus = $quickbooks ? ($quickbooks['last_error'] === '' ? 'Connected' : 'Needs attention') : (quickbooks_ready() ? 'Ready to connect' : 'Setup required'); ?>
<?php $xeroAccounts = xero_connections($db,(int)$user['company_id']); $xeroStatus = $xeroAccounts ? (count(array_filter($xeroAccounts,fn($account)=>$account['last_error'] !== '')) ? 'Needs attention' : 'Connected') : (xero_ready() ? 'Ready to connect' : 'Setup required'); ?>
<?php $workdayAccounts = workday_connections($db,(int)$user['company_id']); $workdayStatus = $workdayAccounts ? (count(array_filter($workdayAccounts,fn($account)=>$account['last_error'] !== '')) ? 'Needs attention' : 'Connected') : (workday_ready() ? 'Ready to connect' : 'Setup required'); ?>
<?php $zohoBooksAccounts = zoho_books_connections($db,(int)$user['company_id']); $zohoBooksStatus = $zohoBooksAccounts ? (count(array_filter($zohoBooksAccounts,fn($account)=>$account['last_error'] !== '')) ? 'Needs attention' : 'Connected') : (zoho_books_ready() ? 'Ready to connect' : 'Setup required'); ?>
<?php $googleWorkspaceAccounts = google_workspace_connections($db,(int)$user['company_id']); ?>
<section class="connected-integration-section" aria-labelledby="connected-integrations-title">
    <div class="section-heading"><div><h2 id="connected-integrations-title">Accounting &amp; people systems</h2><p>Connect provider accounts to this workspace. Current connectors authorize access only; accounting and worker records are not synchronized.</p></div></div>
    <div class="integration-grid connected-integration-grid">
        <section class="integration-card"><div class="provider-logo-mark quickbooks" aria-hidden="true">QuickBooks</div><h2>QuickBooks Online</h2><span class="badge <?= $quickbooks && $quickbooks['last_error'] === '' ? 'active' : '' ?>"><?= e($quickbooksStatus) ?></span><p>Authorize a QuickBooks company and verify its identity with Intuit's read-only Company Info endpoint.</p><a class="text-link" href="/users/admin/quickbooks.php"><?= $quickbooks ? 'Manage connection' : 'Set up connection' ?></a><small>Company verification only; no accounting records are synchronized.</small></section>
        <section class="integration-card"><div class="provider-logo-mark xero" aria-hidden="true">Xero</div><h2>Xero</h2><span class="badge <?= $xeroAccounts && $xeroStatus === 'Connected' ? 'active' : '' ?>"><?= e($xeroStatus) ?></span><p>Authorize a Xero account, choose an organization, and verify it with the read-only Organisation endpoint.</p><a class="text-link" href="/users/admin/xero.php"><?= $xeroAccounts ? 'Manage connection' : 'Set up connection' ?></a><small>Organization verification only; no accounting records are synchronized.</small></section>
        <section class="integration-card"><div class="provider-logo-mark workday" aria-hidden="true">Workday</div><h2>Workday</h2><span class="badge <?= $workdayAccounts && $workdayStatus === 'Connected' ? 'active' : '' ?>"><?= e($workdayStatus) ?></span><p>Authorize the configured Workday tenant with its tenant-specific OAuth client.</p><a class="text-link" href="/users/admin/workday.php"><?= $workdayAccounts ? 'Manage connection' : 'Set up connection' ?></a><small>Authorization only; no worker data is read or synchronized.</small></section>
        <section class="integration-card"><div class="provider-logo-mark zoho-books" aria-hidden="true">Zoho Books</div><h2>Zoho Books</h2><span class="badge <?= $zohoBooksAccounts && $zohoBooksStatus === 'Connected' ? 'active' : '' ?>"><?= e($zohoBooksStatus) ?></span><p>Connect an organization and verify it through Zoho Books' read-only organization settings API.</p><a class="text-link" href="/users/admin/zoho-books.php"><?= $zohoBooksAccounts ? 'Manage connection' : 'Set up connection' ?></a><small>Organization verification only; no accounting records are synchronized.</small></section>
        <section class="integration-card"><div class="provider-logo-mark google-workspace" aria-hidden="true">Google</div><h2>Google Workspace</h2><span class="badge <?= $googleWorkspaceAccounts ? 'active' : '' ?>"><?= $googleWorkspaceAccounts ? count($googleWorkspaceAccounts) . ' connected' : (google_workspace_ready() ? 'Ready to connect' : 'Setup required') ?></span><p>Connect individual Google accounts and verify their identity using basic OpenID Connect scopes.</p><a class="text-link" href="/users/admin/google-workspace.php"><?= $googleWorkspaceAccounts ? 'Manage connection' : 'Set up connection' ?></a><small>Identity verification only; no Calendar, Drive, Gmail or directory data is accessed.</small></section>
    </div>
</section>
<div class="integration-grid"><section class="integration-card"><div class="feature-icon"><?= icon('mail') ?></div><h2>Business contact</h2><span class="badge active">Configured</span><p>Primary support email, phone and address for the workspace owner.</p><small><?= e(config('PRIVACY_CONTACT_EMAIL', 'NUELODEKUNLE20@GMAIL.COM')) ?> · <?= e(config('PRIVACY_CONTACT_PHONE', '+2347026806672')) ?><br><?= e(config('PRIVACY_BUSINESS_ADDRESS', '17, Lekki Gardens, Canaan Estate, Ajah, Lagos State, Nigeria 10127')) ?></small></section>
<section class="integration-card"><div class="feature-icon"><?= icon('plug') ?></div><h2>Account email</h2><span class="badge"><?= config('APP_ENV') === 'local' && config('MAIL_TRANSPORT') === 'log' ? 'Local test delivery' : (mail_ready() ? 'Configured · delivery unverified' : 'Not connected') ?></span><p>Verification and password recovery emails help your team access their workspace.</p><small><?= config('APP_ENV') === 'local' && config('MAIL_TRANSPORT') === 'log' ? 'Emails are saved locally for testing. No real messages are sent.' : 'Your workspace administrator manages the email connection.' ?></small></section>
<section class="integration-card"><div class="provider-logo-mark paystack" aria-hidden="true">Paystack</div><h2>Paystack billing</h2><span class="badge"><?= paystack_ready() ? (config('PAYSTACK_MODE','test') === 'live' ? 'Connected' : 'Test mode') : 'Not available yet' ?></span><p>Hosted checkout for prepaid toolkit subscriptions. Server verification updates access after a successful payment.</p><a class="text-link" href="/users/admin/billing.php">View billing</a><small><?= paystack_ready() ? 'Payments are processed by Paystack. Toolkit access updates once the payment is verified.' : 'Online naira payments are not available yet. Contact Simplr support to renew your toolkits.' ?></small></section>
<section class="integration-card"><div class="provider-logo-mark flutterwave" aria-hidden="true">Flutterwave</div><h2>Flutterwave dollar billing</h2><span class="badge"><?= flutterwave_ready() ? (config('PAYSTACK_MODE','test') === 'live' ? 'Connected' : 'Test mode') : 'Not available yet' ?></span><p>Pay for the same workspace toolkits in US dollars, using Flutterwave hosted checkout.</p><a class="text-link" href="/users/admin/billing.php?currency=USD">View dollar billing</a><small><?= flutterwave_ready() ? 'Payments are processed by Flutterwave. Toolkit access updates once the payment is verified.' : 'Online dollar payments are not available yet. Contact Simplr support to renew your toolkits.' ?></small></section>
<section class="integration-card"><div class="feature-icon">in</div><h2>LinkedIn company page</h2><span class="badge"><?= job_social_sync_ready('linkedin') ? 'Ready to connect' : 'Awaiting setup' ?></span><p>Publish a verified role to a connected LinkedIn company page when you pick it during job creation.</p><small><?= job_social_sync_ready('linkedin') ? 'Your LinkedIn page is configured for posting once the workspace owner approves the company page.' : 'Direct posting is available after the workspace admin adds the company-page credentials and approves the connection.' ?></small></section>
<section class="integration-card"><div class="feature-icon">fb</div><h2>Facebook company page</h2><span class="badge"><?= job_social_sync_ready('facebook') ? 'Ready to connect' : 'Awaiting setup' ?></span><p>Share a published role on your Facebook business page and keep your hiring posts in one place.</p><small><?= job_social_sync_ready('facebook') ? 'Your Facebook page is configured for posting once the workspace owner authorizes it.' : 'Direct posting is available after the workspace admin adds the business-page credentials and approves the connection.' ?></small></section>
<section class="integration-card"><div class="feature-icon">ig</div><h2>Instagram business account</h2><span class="badge"><?= job_social_sync_ready('instagram') ? 'Ready to connect' : 'Awaiting setup' ?></span><p>Publish a branded job-card image with the job link in the caption when your Instagram Business profile is connected.</p><small><?= job_social_sync_ready('instagram') ? 'Your Instagram business profile is configured for posting once the workspace owner approves it.' : 'Direct posting is available after the workspace admin adds the business-profile credentials and approves the connection.' ?></small></section>
<section class="integration-card"><div class="feature-icon"><?= icon('briefcase') ?></div><h2>Payroll & banking</h2><span class="badge"><?= config('PAYROLL_PAYSTACK_TRANSFERS_ENABLED','false') === 'true' && paystack_ready() ? 'Paystack transfers enabled' : 'Payroll available · transfers disabled' ?></span><p>Prepare NGN pay runs, approve payslips, and export bank transfers. Paystack transfers are an explicit, per-person action after owner configuration.</p><a class="text-link" href="/users/admin/payroll.php">Open payroll</a><small>Statutory amounts are manually configured for each pay run and must be checked by a qualified payroll professional. No bank feeds or balances are read.</small></section>
<section class="integration-card"><div class="feature-icon"><?= icon('grid') ?></div><h2>Workspace records</h2><span class="badge active">Available</span><p>Projects, tasks, CRM, finance records, and operations share your business workspace.</p><a class="text-link" href="/users/admin/modules.php">Open your tools <?= icon('arrow') ?></a></section></div>
<section class="legacy-integrations" aria-labelledby="legacy-integrations-title">
    <div class="section-heading"><div><h2 id="legacy-integrations-title">Future integrations</h2><p>Browse planned services below. Their Connect buttons are placeholders only; these providers cannot be connected from Simplr yet.</p></div></div>
    <div class="catalog-search"><span class="search-field"><?= icon('search') ?><label class="sr-only" for="catalog-search">Search integrations</label><input id="catalog-search" class="filter-input" type="search" placeholder="Search future integrations, e.g. Slack, Jira…" autocomplete="off" data-catalog-search></span><span class="count-label" data-catalog-count aria-live="polite"></span></div>
    <p class="catalog-empty subtle" data-catalog-empty hidden>No future integration matches that name. Let us know what you need and we will consider it.</p>
    <div class="legacy-integration-groups">
        <?php foreach ($legacyIntegrations as $category => $services): ?>
            <details class="legacy-integration-group" open>
                <summary><?= e($category) ?><span class="badge"><?= count($services) ?> future</span></summary>
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
                            <button class="button secondary small" type="button" disabled aria-label="Connect <?= e($service) ?> (coming soon)" title="Connection setup is not available yet">Connect <?= e($service) ?></button>
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

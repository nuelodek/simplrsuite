<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/quickbooks.php';

$db = database();
$user = admin_user($db);
$companyId = (int)$user['company_id'];
$oauth = is_string($_GET['oauth'] ?? null) ? $_GET['oauth'] : '';

if ($oauth === 'start') {
    if (!quickbooks_ready()) fail('QuickBooks is not configured for this environment. Add the client ID and secret to the server environment first.',409);
    $state = bin2hex(random_bytes(32));
    $_SESSION['quickbooks_oauth'] = ['state'=>$state,'expires'=>time()+600];
    redirect(quickbooks_authorize_url($state));
}

if ($oauth === 'callback' || (isset($_GET['state']) && (isset($_GET['code']) || isset($_GET['error'])))) {
    $expected = $_SESSION['quickbooks_oauth'] ?? null;
    unset($_SESSION['quickbooks_oauth']);
    $state = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';
    if (!is_array($expected) || !is_string($expected['state'] ?? null) || ($expected['expires'] ?? 0) < time()
        || !hash_equals($expected['state'],$state)) {
        flash('The QuickBooks authorization expired or did not match this session. Please try again.','error');
        redirect('/users/admin/quickbooks.php');
    }
    if (isset($_GET['error'])) {
        flash('QuickBooks authorization was not granted. No connection was saved.','warning');
        redirect('/users/admin/quickbooks.php');
    }
    $code = is_string($_GET['code'] ?? null) ? $_GET['code'] : '';
    $realmId = is_string($_GET['realmId'] ?? null) ? $_GET['realmId'] : '';
    if ($code === '' || !preg_match('/^[0-9]{1,32}$/D',$realmId)) {
        flash('QuickBooks did not return a valid authorization code and company ID.','error');
        redirect('/users/admin/quickbooks.php');
    }
    try {
        $tokens = quickbooks_token_request('authorization_code',['code'=>$code,'redirect_uri'=>quickbooks_redirect_uri()]);
        $company = quickbooks_company_info($realmId,$tokens['access_token']);
        quickbooks_save_connection($db,$user,$realmId,$company,$tokens);
        audit_event($db,$companyId,'integration.connected','integration','quickbooks',['company'=>$company['CompanyName']]);
        flash('QuickBooks company ' . $company['CompanyName'] . ' connected and verified.');
    } catch (QuickBooksUnavailable $error) {
        flash($error->getMessage(),'error');
    }
    redirect('/users/admin/quickbooks.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $connection = quickbooks_connection($db,$companyId);
    if (!$connection) fail('Connect a QuickBooks company before managing the connection.',404);
    $action = input('action');
    if ($action === 'verify') {
        try {
            $company = quickbooks_verify_connection($db,$connection);
            audit_event($db,$companyId,'integration.verified','integration','quickbooks',['company'=>$company['CompanyName']]);
            flash('QuickBooks connection verified for ' . $company['CompanyName'] . '.');
        } catch (QuickBooksUnavailable $error) {
            flash($error->getMessage(),'error');
        }
    } elseif ($action === 'disconnect') {
        db_run($db,"DELETE FROM integration_connections WHERE id = ? AND company_id = ? AND provider = 'quickbooks'",[(int)$connection['id'],$companyId]);
        audit_event($db,$companyId,'integration.disconnected','integration','quickbooks',['company'=>$connection['account_name']]);
        $revoked = false;
        try {
            $revoked = quickbooks_revoke_tokens(quickbooks_decrypt_tokens($connection['token_ciphertext']));
        } catch (RuntimeException|LogicException $error) {
            error_log('Simplr: QuickBooks provider revocation could not be confirmed (' . get_class($error) . ').');
        }
        flash($revoked
            ? 'QuickBooks was disconnected and its provider authorization was revoked.'
            : 'QuickBooks was disconnected and local tokens were removed. Revoke Simplr from your Intuit connected-app settings to remove its provider authorization.',$revoked ? 'success' : 'warning');
    } else {
        fail('Choose a valid QuickBooks action.',422);
    }
    redirect('/users/admin/quickbooks.php');
}

$connection = quickbooks_connection($db,$companyId);
$ready = quickbooks_ready();
$connectionStatus = $connection ? ($connection['last_error'] === '' ? 'Connected' : 'Needs attention') : ($ready ? 'Ready to connect' : 'Setup required');
suite_start('QuickBooks connection','integrations',$user);
?>
<a class="text-link back-crumb" href="/users/admin/integrations.php">← Integrations</a>
<div class="page-heading"><div><h1>QuickBooks</h1><p>Connect a QuickBooks Online company to Simplr Suite.</p></div><span class="badge <?= $connection && $connection['last_error'] === '' ? 'active' : '' ?>"><?= e($connectionStatus) ?></span></div>
<?php if (!$ready): ?>
    <div class="notice warning"><strong>Finish the Intuit app setup to enable Connect.</strong> Set <code>QUICKBOOKS_CLIENT_ID</code>, <code>QUICKBOOKS_CLIENT_SECRET</code> and <code>QUICKBOOKS_ENVIRONMENT</code> in the server environment. Use <code>sandbox</code> while testing and <code>production</code> only after deployment over HTTPS.</div>
<?php endif; ?>
<section class="panel"><div class="panel-body">
    <h2>Connection</h2>
    <?php if ($connection): ?>
        <p><strong><?= e($connection['account_name']) ?></strong> · QuickBooks company ID <?= e($connection['account_login']) ?> · <?= e(ucfirst(quickbooks_environment())) ?> environment</p>
        <p class="subtle">Last verified: <?= $connection['last_checked_at'] ? e($connection['last_checked_at']) . ' UTC' : 'Not yet verified' ?></p>
        <?php if ($connection['last_error'] !== ''): ?><div class="notice error" role="alert"><?= e($connection['last_error']) ?></div><?php endif; ?>
        <div class="button-row">
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="verify"><button class="button" type="submit">Verify connection</button></form>
            <form method="post" data-confirm="Disconnect QuickBooks from this workspace?"><?= csrf_field() ?><input type="hidden" name="action" value="disconnect"><button class="button secondary danger-text" type="submit">Disconnect</button></form>
        </div>
    <?php else: ?>
        <p>Authorize access to the QuickBooks company you want associated with this workspace. Simplr will verify the company name before saving the connection.</p>
        <?php if ($ready): ?><a class="button" href="/users/admin/quickbooks.php?oauth=start">Connect QuickBooks</a><?php endif; ?>
    <?php endif; ?>
    <hr>
    <h3>Intuit app callback URL</h3>
    <p>Register this exact URL as a redirect URI in your Intuit developer app:</p>
    <p><code><?= e(quickbooks_redirect_uri()) ?></code></p>
    <p class="subtle">This connection currently authorizes and verifies the company and securely stores rotating OAuth tokens. It does not import, export, or modify accounting records.</p>
</div></section>
<?php suite_end(); ?>

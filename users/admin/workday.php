<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/workday.php';

$db = database();
$user = admin_user($db);
$companyId = (int)$user['company_id'];
$oauth = is_string($_GET['oauth'] ?? null) ? $_GET['oauth'] : '';

if ($oauth === 'start') {
    if (!workday_ready()) fail('Workday is not configured for this environment. Add the tenant host, tenant name, OAuth client ID and secret to the server environment first.',409);
    $state = bin2hex(random_bytes(32));
    $_SESSION['workday_oauth'] = ['state'=>$state,'expires'=>time()+600];
    redirect(workday_authorize_url($state));
}

if ($oauth === 'callback' || (isset($_GET['state']) && (isset($_GET['code']) || isset($_GET['error'])))) {
    $expected = $_SESSION['workday_oauth'] ?? null;
    unset($_SESSION['workday_oauth']);
    $state = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';
    if (!is_array($expected) || !is_string($expected['state'] ?? null) || !is_int($expected['expires'] ?? null)
        || $expected['expires'] < time() || !hash_equals($expected['state'],$state)) {
        flash('The Workday authorization expired or did not match this session. Please try again.','error');
        redirect('/users/admin/workday.php');
    }
    if (isset($_GET['error'])) {
        flash('Workday authorization was not granted. No connection was saved.','warning');
        redirect('/users/admin/workday.php');
    }
    $code = is_string($_GET['code'] ?? null) ? $_GET['code'] : '';
    if ($code === '' || strlen($code)>2048) {
        flash('Workday did not return a valid authorization code.','error');
        redirect('/users/admin/workday.php');
    }
    try {
        $tokens = workday_token_request('authorization_code',['code'=>$code,'redirect_uri'=>workday_redirect_uri()]);
        workday_save_connection($db,$user,$tokens);
        audit_event($db,$companyId,'integration.connected','integration','workday',['tenant'=>workday_tenant()]);
        flash('Workday OAuth authorization completed for tenant ' . workday_tenant() . '.');
    } catch (WorkdayUnavailable $error) {
        flash($error->getMessage(),'error');
    }
    redirect('/users/admin/workday.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $connection = workday_connection($db,$companyId,(int)input('connection_id'));
    if (!$connection) fail('This Workday connection could not be found in your workspace.',404);
    $action = input('action');
    if ($action === 'refresh') {
        try {
            workday_refresh_connection($db,$connection);
            audit_event($db,$companyId,'integration.verified','integration','workday',['tenant'=>$connection['account_login']]);
            flash('Workday authorization refreshed for tenant ' . $connection['account_login'] . '.');
        } catch (WorkdayUnavailable $error) {
            db_run($db,"UPDATE integration_connections SET last_error = ?, last_checked_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND provider = 'workday'",[
                mb_substr($error->getMessage(),0,255),(int)$connection['id'],$companyId,
            ]);
            flash($error->getMessage(),'error');
        }
    } elseif ($action === 'disconnect') {
        db_run($db,"DELETE FROM integration_connections WHERE id = ? AND company_id = ? AND provider = 'workday'",[(int)$connection['id'],$companyId]);
        audit_event($db,$companyId,'integration.disconnected','integration','workday',['tenant'=>$connection['account_login']]);
        flash('Workday was disconnected and local tokens were removed. Revoke Simplr from the tenant’s Workday OAuth client settings to end provider-side access.','warning');
    } else {
        fail('Choose a valid Workday action.',422);
    }
    redirect('/users/admin/workday.php');
}

$connections = workday_connections($db,$companyId);
$ready = workday_ready();
suite_start('Workday connection','integrations',$user);
?>
<a class="text-link back-crumb" href="/users/admin/integrations.php">← Integrations</a>
<div class="page-heading"><div><h1>Workday</h1><p>Authorize the Workday tenant configured for this workspace.</p></div><span class="badge <?= $connections ? 'active' : '' ?>"><?= $connections ? count($connections) . ' connected' : ($ready ? 'Ready to connect' : 'Setup required') ?></span></div>
<?php if (!$ready): ?>
    <div class="notice warning"><strong>Finish Workday OAuth setup to enable Connect.</strong> Set <code>WORKDAY_HOST</code>, <code>WORKDAY_TENANT</code>, <code>WORKDAY_CLIENT_ID</code> and <code>WORKDAY_CLIENT_SECRET</code> in the server environment.</div>
<?php endif; ?>
<section class="panel workday-setup"><div class="panel-body">
    <h2>Tenant authorization</h2>
    <?php if (!$connections): ?>
        <p>Connect uses the OAuth client registered for the configured Workday tenant. The callback completes an authorization-code exchange and stores rotating tokens encrypted. No worker records are imported, read or modified by this connection-only setup.</p>
        <?php if ($ready): ?><a class="button" href="/users/admin/workday.php?oauth=start">Connect Workday</a><?php endif; ?>
    <?php else: ?>
        <?php foreach ($connections as $connection): ?>
            <article class="workday-connection">
                <div><h3><?= e($connection['account_name']) ?></h3><span class="badge <?= $connection['last_error'] === '' ? 'active' : '' ?>"><?= $connection['last_error'] === '' ? 'Authorized' : 'Needs attention' ?></span></div>
                <p class="subtle">Last authorization update: <?= $connection['last_checked_at'] ? e($connection['last_checked_at']) . ' UTC' : 'Not recorded' ?></p>
                <?php if ($connection['last_error'] !== ''): ?><div class="notice error" role="alert"><?= e($connection['last_error']) ?></div><?php endif; ?>
                <div class="button-row">
                    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="refresh"><input type="hidden" name="connection_id" value="<?= (int)$connection['id'] ?>"><button class="button" type="submit">Refresh authorization</button></form>
                    <form method="post" data-confirm="Disconnect Workday from this workspace?"><?= csrf_field() ?><input type="hidden" name="action" value="disconnect"><input type="hidden" name="connection_id" value="<?= (int)$connection['id'] ?>"><button class="button secondary danger-text" type="submit">Disconnect</button></form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if ($ready): ?><a class="button secondary" href="/users/admin/workday.php?oauth=start">Authorize this tenant again</a><?php endif; ?>
    <?php endif; ?>
    <hr>
    <h3>OAuth callback URL</h3>
    <p>Register this exact redirect URI in the Workday OAuth client:</p>
    <p><code><?= e(workday_redirect_uri()) ?></code></p>
    <p class="subtle">The OAuth host is restricted to a Workday service domain and the tenant/path come only from server configuration. Worker data synchronization is not included in this connection flow.</p>
</div></section>
<?php suite_end(); ?>

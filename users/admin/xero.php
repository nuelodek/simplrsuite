<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/xero.php';

$db = database();
$user = admin_user($db);
$companyId = (int)$user['company_id'];
$oauth = is_string($_GET['oauth'] ?? null) ? $_GET['oauth'] : '';

if ($oauth === 'start') {
    if (!xero_ready()) fail('Xero is not configured for this environment. Add the client ID and secret to the server environment first.',409);
    $state = bin2hex(random_bytes(32));
    $_SESSION['xero_oauth'] = ['state'=>$state,'expires'=>time()+600];
    redirect(xero_authorize_url($state));
}

if ($oauth === 'callback' || (isset($_GET['state']) && (isset($_GET['code']) || isset($_GET['error'])))) {
    $expected = $_SESSION['xero_oauth'] ?? null;
    unset($_SESSION['xero_oauth']);
    $state = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';
    if (!is_array($expected) || !is_string($expected['state'] ?? null) || !is_int($expected['expires'] ?? null)
        || $expected['expires'] < time() || !hash_equals($expected['state'],$state)) {
        flash('The Xero authorization expired or did not match this session. Please try again.','error');
        redirect('/users/admin/xero.php');
    }
    if (isset($_GET['error'])) {
        flash('Xero authorization was not granted. No connection was saved.','warning');
        redirect('/users/admin/xero.php');
    }
    $code = is_string($_GET['code'] ?? null) ? $_GET['code'] : '';
    if ($code === '' || strlen($code)>2048) {
        flash('Xero did not return a valid authorization code.','error');
        redirect('/users/admin/xero.php');
    }
    try {
        $tokens = xero_token_request('authorization_code',['code'=>$code,'redirect_uri'=>xero_redirect_uri()]);
        $tenants = xero_tenants($tokens['access_token']);
        $_SESSION['xero_pending'] = [
            'token_ciphertext'=>xero_token_ciphertext($tokens),
            'tenants'=>$tenants,
            'expires'=>time()+600,
        ];
        flash('Xero access authorized. Choose the organization to connect to this workspace.');
    } catch (XeroUnavailable $error) {
        flash($error->getMessage(),'error');
    }
    redirect('/users/admin/xero.php');
}

$pending = $_SESSION['xero_pending'] ?? null;
if (is_array($pending) && (!is_int($pending['expires'] ?? null) || $pending['expires'] < time())) {
    unset($_SESSION['xero_pending']);
    $pending = null;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $action = input('action');
    if ($action === 'connect_organization') {
        if (!is_array($pending) || !is_string($pending['token_ciphertext'] ?? null) || !is_array($pending['tenants'] ?? null)) {
            fail('The Xero authorization expired. Start the connection again.',409);
        }
        $tenantId = input('tenant_id');
        $selected = null;
        foreach ($pending['tenants'] as $tenant) {
            if (is_array($tenant) && is_string($tenant['tenant_id'] ?? null) && hash_equals($tenant['tenant_id'],$tenantId)) {
                $selected = $tenant;
                break;
            }
        }
        if (!$selected) fail('Choose an organization returned by Xero.',422);
        try {
            $tokens = xero_decrypt_tokens($pending['token_ciphertext']);
            $organisation = xero_organisation_info($tenantId,$tokens['access_token']);
            xero_save_connection($db,$user,$tenantId,$organisation,$tokens);
            unset($_SESSION['xero_pending']);
            audit_event($db,$companyId,'integration.connected','integration','xero',['organization'=>$organisation['Name']]);
            flash('Xero organization ' . $organisation['Name'] . ' connected and verified.');
        } catch (XeroUnavailable $error) {
            flash($error->getMessage(),'error');
        }
        redirect('/users/admin/xero.php');
    }

    $connection = xero_connection($db,$companyId,(int)input('connection_id'));
    if (!$connection) fail('This Xero connection could not be found in your workspace.',404);
    if ($action === 'verify') {
        try {
            $organisation = xero_verify_connection($db,$connection);
            audit_event($db,$companyId,'integration.verified','integration','xero',['organization'=>$organisation['Name']]);
            flash('Xero connection verified for ' . $organisation['Name'] . '.');
        } catch (XeroUnavailable $error) {
            flash($error->getMessage(),'error');
        }
    } elseif ($action === 'disconnect') {
        $tokens = null;
        try {
            $tokens = xero_decrypt_tokens($connection['token_ciphertext']);
        } catch (RuntimeException $error) {
            error_log('Simplr: saved Xero token could not be decrypted before disconnect (' . get_class($error) . ').');
        }
        db_run($db,"DELETE FROM integration_connections WHERE id = ? AND company_id = ? AND provider = 'xero'",[(int)$connection['id'],$companyId]);
        audit_event($db,$companyId,'integration.disconnected','integration','xero',['organization'=>$connection['account_name']]);
        $revoked = false;
        if ($tokens !== null) {
            try {
                $revoked = xero_revoke_tokens($tokens);
            } catch (RuntimeException|LogicException $error) {
                error_log('Simplr: Xero provider revocation could not be confirmed (' . get_class($error) . ').');
            }
        }
        flash($revoked
            ? 'Xero was disconnected and its provider authorization was revoked.'
            : 'Xero was disconnected and local tokens were removed. Revoke Simplr from your Xero connected-app settings to remove its provider authorization.',$revoked ? 'success' : 'warning');
    } else {
        fail('Choose a valid Xero action.',422);
    }
    redirect('/users/admin/xero.php');
}

$connections = xero_connections($db,$companyId);
$ready = xero_ready();
suite_start('Xero connection','integrations',$user);
?>
<a class="text-link back-crumb" href="/users/admin/integrations.php">← Integrations</a>
<div class="page-heading"><div><h1>Xero</h1><p>Connect and verify a Xero organization for this workspace.</p></div><span class="badge <?= $connections ? 'active' : '' ?>"><?= $connections ? count($connections) . ' connected' : ($ready ? 'Ready to connect' : 'Setup required') ?></span></div>
<?php if (!$ready): ?>
    <div class="notice warning"><strong>Finish the Xero app setup to enable Connect.</strong> Set <code>XERO_CLIENT_ID</code> and <code>XERO_CLIENT_SECRET</code> in the server environment.</div>
<?php endif; ?>
<?php if (is_array($pending) && is_array($pending['tenants'] ?? null)): ?>
    <section class="panel"><div class="panel-body">
        <h2>Choose a Xero organization</h2>
        <p>Select the organization you want associated with this workspace.</p>
        <?php foreach ($pending['tenants'] as $tenant): if (!is_array($tenant) || !is_string($tenant['tenant_id'] ?? null) || !is_string($tenant['tenant_name'] ?? null)) continue; ?>
            <form method="post" class="button-row"><?= csrf_field() ?><input type="hidden" name="action" value="connect_organization"><input type="hidden" name="tenant_id" value="<?= e($tenant['tenant_id']) ?>"><button class="button" type="submit">Connect <?= e($tenant['tenant_name']) ?></button></form>
        <?php endforeach; ?>
    </div></section>
<?php endif; ?>
<section class="panel"><div class="panel-body">
    <h2>Connected organizations</h2>
    <?php if (!$connections): ?>
        <p>Authorize access to a Xero account, then choose the organization to associate with this workspace. Simplr verifies its identity before saving the connection.</p>
        <?php if ($ready && !is_array($pending)): ?><a class="button" href="/users/admin/xero.php?oauth=start">Connect Xero</a><?php endif; ?>
    <?php else: ?>
        <?php foreach ($connections as $connection): ?>
            <article class="panel">
                <h3><?= e($connection['account_name']) ?></h3>
                <p class="subtle">Organization ID <?= e($connection['account_login']) ?> · Last verified: <?= $connection['last_checked_at'] ? e($connection['last_checked_at']) . ' UTC' : 'Not yet verified' ?></p>
                <?php if ($connection['last_error'] !== ''): ?><div class="notice error" role="alert"><?= e($connection['last_error']) ?></div><?php endif; ?>
                <div class="button-row">
                    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="verify"><input type="hidden" name="connection_id" value="<?= (int)$connection['id'] ?>"><button class="button" type="submit">Verify connection</button></form>
                    <form method="post" data-confirm="Disconnect this Xero organization from the workspace?"><?= csrf_field() ?><input type="hidden" name="action" value="disconnect"><input type="hidden" name="connection_id" value="<?= (int)$connection['id'] ?>"><button class="button secondary danger-text" type="submit">Disconnect</button></form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if ($ready && !is_array($pending)): ?><a class="button secondary" href="/users/admin/xero.php?oauth=start">Connect another organization</a><?php endif; ?>
    <?php endif; ?>
    <hr>
    <h3>Xero app callback URL</h3>
    <p>Register this exact URL as a redirect URI in your Xero developer app:</p>
    <p><code><?= e(xero_redirect_uri()) ?></code></p>
    <p class="subtle">This connection uses read-only organization settings access to verify the selected organization. It does not import, export, or modify accounting records.</p>
</div></section>
<?php suite_end(); ?>

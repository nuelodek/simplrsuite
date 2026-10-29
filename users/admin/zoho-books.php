<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/zoho_books.php';

$db = database();
$user = admin_user($db);
$companyId = (int)$user['company_id'];
$oauth = is_string($_GET['oauth'] ?? null) ? $_GET['oauth'] : '';

if ($oauth === 'start') {
    if (!zoho_books_ready()) fail('Zoho Books is not configured for this environment. Add the client ID and secret and set the Zoho data center first.',409);
    $state = bin2hex(random_bytes(32));
    $_SESSION['zoho_books_oauth'] = ['state'=>$state,'expires'=>time()+600];
    redirect(zoho_books_authorize_url($state));
}

if ($oauth === 'callback' || (isset($_GET['state']) && (isset($_GET['code']) || isset($_GET['error'])))) {
    $expected = $_SESSION['zoho_books_oauth'] ?? null;
    unset($_SESSION['zoho_books_oauth']);
    $state = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';
    if (!is_array($expected) || !is_string($expected['state'] ?? null) || !is_int($expected['expires'] ?? null)
        || $expected['expires'] < time() || !hash_equals($expected['state'],$state)) {
        flash('The Zoho Books authorization expired or did not match this session. Please try again.','error');
        redirect('/users/admin/zoho-books.php');
    }
    if (isset($_GET['error'])) {
        flash('Zoho Books authorization was not granted. No connection was saved.','warning');
        redirect('/users/admin/zoho-books.php');
    }
    $code = is_string($_GET['code'] ?? null) ? $_GET['code'] : '';
    if ($code === '' || strlen($code)>2048) {
        flash('Zoho Books did not return a valid authorization code.','error');
        redirect('/users/admin/zoho-books.php');
    }
    try {
        $tokens = zoho_books_token_request('authorization_code',['code'=>$code,'redirect_uri'=>zoho_books_redirect_uri()]);
        $organizations = zoho_books_organizations($tokens['access_token'],$tokens['region']);
        $_SESSION['zoho_books_pending'] = [
            'token_ciphertext'=>zoho_books_token_ciphertext($tokens),
            'organizations'=>$organizations,
            'expires'=>time()+600,
        ];
        flash('Zoho Books access authorized. Choose the organization to connect to this workspace.');
    } catch (ZohoBooksUnavailable $error) {
        flash($error->getMessage(),'error');
    }
    redirect('/users/admin/zoho-books.php');
}

$pending = $_SESSION['zoho_books_pending'] ?? null;
if (is_array($pending) && (!is_int($pending['expires'] ?? null) || $pending['expires'] < time())) {
    unset($_SESSION['zoho_books_pending']);
    $pending = null;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $action = input('action');
    if ($action === 'connect_organization') {
        if (!is_array($pending) || !is_string($pending['token_ciphertext'] ?? null) || !is_array($pending['organizations'] ?? null)) {
            fail('The Zoho Books authorization expired. Start the connection again.',409);
        }
        $organizationId = input('organization_id');
        $selected = null;
        foreach ($pending['organizations'] as $organization) {
            if (is_array($organization) && is_string($organization['organization_id'] ?? null)
                && hash_equals($organization['organization_id'],$organizationId)) {
                $selected = $organization;
                break;
            }
        }
        if (!$selected) fail('Choose an organization returned by Zoho Books.',422);
        try {
            $tokens = zoho_books_decrypt_tokens($pending['token_ciphertext']);
            $organization = zoho_books_organization_info($organizationId,$tokens['access_token'],$tokens['region']);
            zoho_books_save_connection($db,$user,$organizationId,$organization,$tokens);
            unset($_SESSION['zoho_books_pending']);
            audit_event($db,$companyId,'integration.connected','integration','zoho_books',['organization'=>$organization['name']]);
            flash('Zoho Books organization ' . $organization['name'] . ' connected and verified.');
        } catch (ZohoBooksUnavailable $error) {
            flash($error->getMessage(),'error');
        }
        redirect('/users/admin/zoho-books.php');
    }

    $connection = zoho_books_connection($db,$companyId,(int)input('connection_id'));
    if (!$connection) fail('This Zoho Books connection could not be found in your workspace.',404);
    if ($action === 'verify') {
        try {
            $organization = zoho_books_verify_connection($db,$connection);
            audit_event($db,$companyId,'integration.verified','integration','zoho_books',['organization'=>$organization['name']]);
            flash('Zoho Books connection verified for ' . $organization['name'] . '.');
        } catch (ZohoBooksUnavailable $error) {
            flash($error->getMessage(),'error');
        }
    } elseif ($action === 'disconnect') {
        $tokens = null;
        try {
            $tokens = zoho_books_decrypt_tokens($connection['token_ciphertext']);
        } catch (RuntimeException $error) {
            error_log('Simplr: saved Zoho Books token could not be decrypted before disconnect (' . get_class($error) . ').');
        }
        db_run($db,"DELETE FROM integration_connections WHERE id = ? AND company_id = ? AND provider = 'zoho_books'",[(int)$connection['id'],$companyId]);
        audit_event($db,$companyId,'integration.disconnected','integration','zoho_books',['organization'=>$connection['account_name']]);
        $revoked = false;
        if ($tokens !== null) {
            try {
                $revoked = zoho_books_revoke_token($tokens);
            } catch (RuntimeException|LogicException $error) {
                error_log('Simplr: Zoho Books provider revocation could not be confirmed (' . get_class($error) . ').');
            }
        }
        flash($revoked
            ? 'Zoho Books was disconnected and its provider authorization was revoked.'
            : 'Zoho Books was disconnected and local tokens were removed. Revoke Simplr from your Zoho connected-app settings to remove its provider authorization.',$revoked ? 'success' : 'warning');
    } else {
        fail('Choose a valid Zoho Books action.',422);
    }
    redirect('/users/admin/zoho-books.php');
}

$connections = zoho_books_connections($db,$companyId);
$ready = zoho_books_ready();
suite_start('Zoho Books connection','integrations',$user);
?>
<a class="text-link back-crumb" href="/users/admin/integrations.php">← Integrations</a>
<div class="page-heading"><div><h1>Zoho Books</h1><p>Connect and verify a Zoho Books organization for this workspace.</p></div><span class="badge <?= $connections ? 'active' : '' ?>"><?= $connections ? count($connections) . ' connected' : ($ready ? 'Ready to connect' : 'Setup required') ?></span></div>
<?php if (!$ready): ?>
    <div class="notice warning"><strong>Finish Zoho Books app setup to enable Connect.</strong> Set <code>ZOHO_BOOKS_CLIENT_ID</code>, <code>ZOHO_BOOKS_CLIENT_SECRET</code> and the correct <code>ZOHO_BOOKS_REGION</code> in the server environment.</div>
<?php endif; ?>
<?php if (is_array($pending) && is_array($pending['organizations'] ?? null)): ?>
    <section class="panel"><div class="panel-body">
        <h2>Choose a Zoho Books organization</h2>
        <p>Select the organization you want associated with this workspace.</p>
        <?php foreach ($pending['organizations'] as $organization): if (!is_array($organization) || !is_string($organization['organization_id'] ?? null) || !is_string($organization['name'] ?? null)) continue; ?>
            <form method="post" class="button-row"><?= csrf_field() ?><input type="hidden" name="action" value="connect_organization"><input type="hidden" name="organization_id" value="<?= e($organization['organization_id']) ?>"><button class="button" type="submit">Connect <?= e($organization['name']) ?></button></form>
        <?php endforeach; ?>
    </div></section>
<?php endif; ?>
<section class="panel"><div class="panel-body">
    <h2>Connected organizations</h2>
    <?php if (!$connections): ?>
        <p>Authorize access to the Zoho Books account, then choose the organization to associate with this workspace. Simplr verifies its identity before saving the connection.</p>
        <?php if ($ready && !is_array($pending)): ?><a class="button" href="/users/admin/zoho-books.php?oauth=start">Connect Zoho Books</a><?php endif; ?>
    <?php else: ?>
        <?php foreach ($connections as $connection): ?>
            <article class="panel">
                <h3><?= e($connection['account_name']) ?></h3>
                <p class="subtle">Zoho organization key <?= e($connection['account_login']) ?> · Last verified: <?= $connection['last_checked_at'] ? e($connection['last_checked_at']) . ' UTC' : 'Not yet verified' ?></p>
                <?php if ($connection['last_error'] !== ''): ?><div class="notice error" role="alert"><?= e($connection['last_error']) ?></div><?php endif; ?>
                <div class="button-row">
                    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="verify"><input type="hidden" name="connection_id" value="<?= (int)$connection['id'] ?>"><button class="button" type="submit">Verify connection</button></form>
                    <form method="post" data-confirm="Disconnect Zoho Books from this workspace?"><?= csrf_field() ?><input type="hidden" name="action" value="disconnect"><input type="hidden" name="connection_id" value="<?= (int)$connection['id'] ?>"><button class="button secondary danger-text" type="submit">Disconnect</button></form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if ($ready && !is_array($pending)): ?><a class="button secondary" href="/users/admin/zoho-books.php?oauth=start">Connect another organization</a><?php endif; ?>
    <?php endif; ?>
    <hr>
    <h3>Zoho app callback URL</h3>
    <p>Register this exact redirect URI in your Zoho API Console client:</p>
    <p><code><?= e(zoho_books_redirect_uri()) ?></code></p>
    <p class="subtle">The connector uses read-only settings access to verify organizations. It does not import, export, or modify accounting records.</p>
</div></section>
<?php suite_end(); ?>

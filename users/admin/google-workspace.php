<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/google-workspace.php';

$db = database();
$user = admin_user($db);
$companyId = (int)$user['company_id'];
$oauth = is_string($_GET['oauth'] ?? null) ? $_GET['oauth'] : '';

if ($oauth === 'start') {
    if (!google_workspace_ready()) fail('Google Workspace is not configured for this environment. Add the OAuth client ID and secret to the server environment first.',409);
    $state = bin2hex(random_bytes(32));
    $_SESSION['google_workspace_oauth'] = [
        'state'=>$state,
        'expires'=>time()+600,
        'company_id'=>$companyId,
        'user_id'=>(int)$user['id'],
    ];
    redirect(google_workspace_authorize_url($state));
}

if (isset($_GET['state']) || isset($_GET['code']) || isset($_GET['error'])) {
    $expected = $_SESSION['google_workspace_oauth'] ?? null;
    unset($_SESSION['google_workspace_oauth']);
    $state = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';
    if (!is_array($expected) || !is_string($expected['state'] ?? null) || !is_int($expected['expires'] ?? null)
        || !is_int($expected['company_id'] ?? null) || !is_int($expected['user_id'] ?? null)
        || $expected['expires'] < time() || $expected['company_id'] !== $companyId
        || $expected['user_id'] !== (int)$user['id'] || !hash_equals($expected['state'],$state)) {
        flash('The Google authorization expired or did not match this session. Please try again.','error');
        redirect('/users/admin/google-workspace.php');
    }
    if (isset($_GET['error'])) {
        flash('Google authorization was not granted. No account was connected.','warning');
        redirect('/users/admin/google-workspace.php');
    }
    $code = is_string($_GET['code'] ?? null) ? $_GET['code'] : '';
    try {
        $accessToken = google_workspace_exchange_code($code);
        $profile = google_workspace_userinfo($accessToken);
        google_workspace_save_connection($db,$user,$profile);
        audit_event($db,$companyId,'integration.connected','integration','google-workspace',['email'=>$profile['email']]);
        flash('Google account ' . $profile['email'] . ' connected and verified.');
    } catch (GoogleWorkspaceUnavailable $error) {
        flash($error->getMessage(),'error');
    }
    redirect('/users/admin/google-workspace.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $connectionId = filter_var($_POST['connection_id'] ?? null,FILTER_VALIDATE_INT);
    if (!is_int($connectionId) || $connectionId < 1) fail('Choose a valid Google account connection.',422);
    $connection = google_workspace_connection($db,$companyId,$connectionId);
    if (!$connection) fail('This Google account could not be found in your workspace.',404);
    if (input('action') !== 'disconnect') fail('Choose a valid Google Workspace action.',422);
    db_run($db,"DELETE FROM integration_connections WHERE id = ? AND company_id = ? AND provider = 'google-workspace'",[
        $connectionId,$companyId,
    ]);
    audit_event($db,$companyId,'integration.disconnected','integration','google-workspace',['email'=>$connection['account_name']]);
    flash('The Google account was disconnected from this workspace. Remove Simplr from the Google Account connections page to revoke Google-side access if needed.','warning');
    redirect('/users/admin/google-workspace.php');
}

$connections = google_workspace_connections($db,$companyId);
$ready = google_workspace_ready();
suite_start('Google Workspace connection','integrations',$user);
?>
<a class="text-link back-crumb" href="/users/admin/integrations.php">← Integrations</a>
<div class="page-heading"><div><h1>Google Workspace</h1><p>Connect an individual Google account to this workspace.</p></div><span class="badge <?= $connections ? 'active' : '' ?>"><?= $connections ? count($connections) . ' connected' : ($ready ? 'Ready to connect' : 'Setup required') ?></span></div>
<?php if (!$ready): ?>
    <div class="notice warning"><strong>Finish Google OAuth setup to enable Connect.</strong> Set <code>GOOGLE_WORKSPACE_CLIENT_ID</code> and <code>GOOGLE_WORKSPACE_CLIENT_SECRET</code> in the server environment.</div>
<?php endif; ?>
<section class="panel"><div class="panel-body">
    <h2>Connected accounts</h2>
    <?php if ($connections): ?>
        <?php foreach ($connections as $connection): ?>
            <article class="google-workspace-connection">
                <h3><?= e($connection['account_name']) ?></h3>
                <p class="subtle">Identity verified <?= $connection['last_checked_at'] ? e($connection['last_checked_at']) . ' UTC' : '' ?></p>
                <form method="post" data-confirm="Disconnect this Google account from the workspace?"><?= csrf_field() ?><input type="hidden" name="action" value="disconnect"><input type="hidden" name="connection_id" value="<?= (int)$connection['id'] ?>"><button class="button secondary danger-text" type="submit">Disconnect</button></form>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Connect an individual Google account and verify its email address. Simplr does not request Calendar, Drive, Gmail or Workspace directory access.</p>
    <?php endif; ?>
    <?php if ($ready): ?><a class="button <?= $connections ? 'secondary' : '' ?>" href="/users/admin/google-workspace.php?oauth=start"><?= $connections ? 'Connect another Google account' : 'Connect Google' ?></a><?php endif; ?>
    <hr>
    <h3>OAuth callback URL</h3>
    <p>Register this exact redirect URI in your Google Cloud OAuth client:</p>
    <p><code><?= e(google_workspace_redirect_uri()) ?></code></p>
    <p class="subtle">Only <code>openid</code>, <code>email</code> and <code>profile</code> are requested. OAuth access tokens are used only to verify the account and are not stored. No Google Workspace-wide delegation or data synchronization is enabled. To revoke Google-side access immediately, remove Simplr from the Google Account connections page.</p>
</div></section>
<?php suite_end(); ?>

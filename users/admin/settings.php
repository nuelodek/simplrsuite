<?php
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/settings.php';
require_once __DIR__ . '/../../app/view.php';
$db = database(); $user = admin_user($db); $companyId = (int)$user['company_id'];
$settings = workspace_settings($db,$companyId); $version = record_version([$user['company_name'],$settings]); $errors = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); $name = input('company_name'); $currency = input('currency'); $timezone = input('timezone');
    if ($name === '' || mb_strlen($name)>255) $errors[] = 'Enter a business name of up to 255 characters.';
    if (!isset(supported_currencies()[$currency])) $errors[] = 'Choose a supported currency.';
    if (!in_array($timezone,DateTimeZone::listIdentifiers(),true)) $errors[] = 'Choose a valid timezone.';
    if (!$errors) {
        $db->begin_transaction();
        try {
            $current = db_one($db,'SELECT company_name FROM users WHERE id = ? AND company_id = ? FOR UPDATE',[$user['id'],$companyId]);
            $currentSettings = workspace_settings($db,$companyId);
            if (!$current || !hash_equals(record_version([$current['company_name'],$currentSettings]),input('version'))) { $db->rollback(); fail('Workspace settings changed. Reload and review them before saving.',409); }
            db_run($db,'UPDATE users SET company_name = ? WHERE id = ? AND company_id = ?',[$name,$user['id'],$companyId]);
            db_run($db,'INSERT INTO workspace_settings (company_id,currency,timezone) VALUES (?,?,?) ON DUPLICATE KEY UPDATE currency = VALUES(currency), timezone = VALUES(timezone)',[$companyId,$currency,$timezone]);
            audit_event($db,$companyId,'settings.updated','workspace',(string)$companyId,['currency'=>$currency,'timezone'=>$timezone]);
            $db->commit();
        } catch (Throwable $error) { $db->rollback(); throw $error; }
        flash('Workspace settings saved.'); redirect('/users/admin/settings.php');
    }
    http_response_code(422); $user['company_name'] = $name; $settings = compact('currency','timezone');
}
suite_start('Settings','settings',$user);
?>
<div class="page-heading"><div><h1>Workspace settings</h1><p>Business details and defaults used across the workspace.</p></div></div>
<section class="panel"><div class="panel-body"><form method="post" class="form-stack record-form" data-submit><?= csrf_field() ?><input type="hidden" name="version" value="<?= e($version) ?>">
<?php if ($errors): ?><div class="notice error" role="alert"><?= e(implode(' ',$errors)) ?></div><?php endif; ?>
<div class="field"><label for="company-name">Business name</label><input id="company-name" name="company_name" value="<?= e($user['company_name']) ?>" maxlength="255" required></div>
<div class="field"><label for="currency">Record currency</label><select id="currency" name="currency" required><option value="">Choose your currency</option><?php foreach (supported_currencies() as $code=>$label): ?><option value="<?= e($code) ?>" <?= $settings['currency'] === $code ? 'selected' : '' ?>><?= e($code . ' · ' . $label) ?></option><?php endforeach; ?></select><small>Amounts use this currency. Changing it does not convert existing amounts. Review older records before changing currency.</small></div>
<div class="field"><label for="timezone">Default scheduling timezone</label><select id="timezone" name="timezone" required><?php foreach (DateTimeZone::listIdentifiers() as $zone): ?><option value="<?= e($zone) ?>" <?= $settings['timezone'] === $zone ? 'selected' : '' ?>><?= e(str_replace('_',' ',$zone)) ?></option><?php endforeach; ?></select><small>New events and job closing dates start with this timezone. Existing records keep their saved timezone when these settings change.</small></div>
<div class="form-actions"><button type="submit" class="button">Save settings</button></div></form></div></section>
<?php suite_end(); ?>

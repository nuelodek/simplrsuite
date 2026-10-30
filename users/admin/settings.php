<?php
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/settings.php';
require_once __DIR__ . '/../../app/view.php';
$db = database(); $user = admin_user($db); $companyId = (int)$user['company_id'];
$settings = workspace_settings($db,$companyId);
$disciplineSettings = db_one($db,'SELECT query_response_minutes, query_response_hours, warning_expiry_months FROM workspace_settings WHERE company_id = ?',[$companyId]) ?: ['query_response_minutes'=>4320,'query_response_hours'=>72,'warning_expiry_months'=>12];
$settings['query_response_minutes'] = (int)$disciplineSettings['query_response_minutes'] ?: ((int)$disciplineSettings['query_response_hours'] ?: 72) * 60;
$settings['warning_expiry_months'] = (int)$disciplineSettings['warning_expiry_months'];
$version = record_version([$user['company_name'],$settings]); $errors = []; $newLogoPath = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); $name = input('company_name'); $currency = input('currency'); $timezone = input('timezone');
    $logoFile = $_FILES['company_logo'] ?? ['error'=>UPLOAD_ERR_NO_FILE];
    $removeLogo = input('remove_company_logo') === '1';
    $queryResponseMinutes = filter_var(input('query_response_minutes'),FILTER_VALIDATE_INT);
    $warningExpiryMonths = filter_var(input('warning_expiry_months'),FILTER_VALIDATE_INT);
    if ($name === '' || mb_strlen($name)>255) $errors[] = 'Enter a business name of up to 255 characters.';
    if (!isset(supported_currencies()[$currency])) $errors[] = 'Choose a supported currency.';
    if (!in_array($timezone,DateTimeZone::listIdentifiers(),true)) $errors[] = 'Choose a valid timezone.';
    if (!in_array($queryResponseMinutes,[1,5,10,15,30,45,60,120,240,480,1440,4320,10080,20160],true)) $errors[] = 'Choose a valid query response window.';
    if (!in_array($warningExpiryMonths,[1,3,6,12,24,36],true)) $errors[] = 'Choose a valid warning expiry period.';
    $logoError = workspace_logo_upload_error($logoFile);
    if ($logoError !== null) $errors[] = $logoError;
    if (($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && $removeLogo) $errors[] = 'Upload a replacement logo or choose remove, not both.';
    if (!$errors) {
        $db->begin_transaction();
        try {
            $current = db_one($db,'SELECT company_name FROM users WHERE id = ? AND company_id = ? FOR UPDATE',[$user['id'],$companyId]);
            $currentSettings = workspace_settings($db,$companyId);
            $currentDisciplineSettings = db_one($db,'SELECT query_response_minutes, query_response_hours, warning_expiry_months FROM workspace_settings WHERE company_id = ?',[$companyId]) ?: ['query_response_minutes'=>4320,'query_response_hours'=>72,'warning_expiry_months'=>12];
            $currentSettings['query_response_minutes'] = (int)$currentDisciplineSettings['query_response_minutes'] ?: ((int)$currentDisciplineSettings['query_response_hours'] ?: 72) * 60;
            $currentSettings['warning_expiry_months'] = (int)$currentDisciplineSettings['warning_expiry_months'];
            if (!$current || !hash_equals(record_version([$current['company_name'],$currentSettings]),input('version'))) { $db->rollback(); fail('Workspace settings changed. Reload and review them before saving.',409); }
            $oldLogoPath = (string)($currentSettings['company_logo_path'] ?? '');
            if (($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) $newLogoPath = store_workspace_logo_upload($logoFile);
            elseif ($removeLogo) $newLogoPath = '';
            else $newLogoPath = $oldLogoPath;
            db_run($db,'UPDATE users SET company_name = ? WHERE id = ? AND company_id = ?',[$name,$user['id'],$companyId]);
            db_run($db,'INSERT INTO workspace_settings (company_id,currency,timezone,company_logo_path,query_response_hours,query_response_minutes,warning_expiry_months) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE currency = VALUES(currency), timezone = VALUES(timezone), company_logo_path = VALUES(company_logo_path), query_response_hours = VALUES(query_response_hours), query_response_minutes = VALUES(query_response_minutes), warning_expiry_months = VALUES(warning_expiry_months)',[$companyId,$currency,$timezone,$newLogoPath,max(1,(int)ceil($queryResponseMinutes / 60)),$queryResponseMinutes,$warningExpiryMonths]);
            audit_event($db,$companyId,'settings.updated','workspace',(string)$companyId,['currency'=>$currency,'timezone'=>$timezone,'query_response_minutes'=>$queryResponseMinutes,'warning_expiry_months'=>$warningExpiryMonths]);
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            if ($newLogoPath !== '' && $newLogoPath !== ($oldLogoPath ?? '')) delete_workspace_logo($newLogoPath);
            throw $error;
        }
        if (isset($oldLogoPath) && $oldLogoPath !== '' && $oldLogoPath !== $newLogoPath) delete_workspace_logo($oldLogoPath);
        flash('Workspace settings saved.'); redirect('/users/admin/settings.php');
    }
    http_response_code(422); $user['company_name'] = $name; $settings = ['currency'=>$currency,'timezone'=>$timezone,'company_logo_path'=>$settings['company_logo_path'] ?? '','query_response_minutes'=>$queryResponseMinutes ?: 4320,'warning_expiry_months'=>$warningExpiryMonths ?: 12];
}
if ($errors && $newLogoPath !== '') delete_workspace_logo($newLogoPath);
suite_start('Settings','settings',$user);
?>
<div class="page-heading"><div><h1>Workspace settings</h1><p>Business details and defaults used across the workspace.</p></div></div>
<section class="panel"><div class="panel-body"><form method="post" enctype="multipart/form-data" class="form-stack record-form" data-submit><?= csrf_field() ?><input type="hidden" name="version" value="<?= e($version) ?>">
<?php if ($errors): ?><div class="notice error" role="alert"><?= e(implode(' ',$errors)) ?></div><?php endif; ?>
<div class="field"><label for="company-name">Business name</label><input id="company-name" name="company_name" value="<?= e($user['company_name']) ?>" maxlength="255" required></div>
<div class="field"><label for="company-logo">Company logo</label><?php if ($settings['company_logo_path'] !== ''): ?><img class="settings-company-logo" src="/users/workspace-logo.php" alt="Current company logo"><label class="checkbox-label"><input type="checkbox" name="remove_company_logo" value="1"> Remove current logo</label><?php endif; ?><input id="company-logo" name="company_logo" type="file" accept="image/png,image/jpeg,image/webp"><small>Upload a PNG, JPEG or WebP image up to 2 MB. The logo appears in the workspace header.</small></div>
<div class="field"><label for="currency">Record currency</label><select id="currency" name="currency" required><option value="">Choose your currency</option><?php foreach (supported_currencies() as $code=>$label): ?><option value="<?= e($code) ?>" <?= $settings['currency'] === $code ? 'selected' : '' ?>><?= e($code . ' · ' . $label) ?></option><?php endforeach; ?></select><small>Amounts use this currency. Changing it does not convert existing amounts. Review older records before changing currency.</small></div>
<div class="field"><label for="timezone">Default scheduling timezone</label><select id="timezone" name="timezone" required><?php foreach (DateTimeZone::listIdentifiers() as $zone): ?><option value="<?= e($zone) ?>" <?= $settings['timezone'] === $zone ? 'selected' : '' ?>><?= e(str_replace('_',' ',$zone)) ?></option><?php endforeach; ?></select><small>New events and job closing dates start with this timezone. Existing records keep their saved timezone when these settings change.</small></div>
<div class="field"><label for="query-response-minutes">Default query response window</label><select id="query-response-minutes" name="query_response_minutes" required><?php foreach ([1=>'1 minute',5=>'5 minutes',10=>'10 minutes',15=>'15 minutes',30=>'30 minutes',45=>'45 minutes',60=>'1 hour',120=>'2 hours',240=>'4 hours',480=>'8 hours',1440=>'1 day',4320=>'3 days',10080=>'7 days',20160=>'14 days'] as $minutes=>$label): ?><option value="<?= $minutes ?>"<?= $settings['query_response_minutes'] === $minutes ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select><small>People can respond later, but the query will be marked no-response for review when this deadline passes. No penalty is automatic.</small></div>
<div class="field"><label for="warning-expiry-months">Warning expiry period</label><select id="warning-expiry-months" name="warning_expiry_months" required><?php foreach ([1,3,6,12,24,36] as $months): ?><option value="<?= $months ?>"<?= $settings['warning_expiry_months'] === $months ? ' selected' : '' ?>><?= $months . ' ' . ($months === 1 ? 'month' : 'months') ?></option><?php endforeach; ?></select><small>Warnings expire from the record after this period. Financial or payroll deductions are never applied by Simplr.</small></div>
<div class="form-actions"><button type="submit" class="button">Save settings</button></div></form></div></section>
<?php suite_end(); ?>

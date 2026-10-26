<?php
require_once __DIR__ . '/talent.php';
$db = database(); $user = talent_user($db);
$defaults = ['headline'=>'','location'=>'','experience'=>'','skills'=>'','bio'=>'','portfolio_url'=>'','availability'=>'','visibility'=>'private'];
$profile = db_one($db, 'SELECT * FROM talent_portfolios WHERE account_id = ?', [$user['id']]) ?? $defaults;
$returnJob = is_string($_GET['job'] ?? null) && preg_match('/^[a-f0-9]{32}$/D',$_GET['job']) ? $_GET['job'] : '';
$errors = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); rate_limit($db, 'talent-profile', (string)$user['id'], 40);
    if (!empty($_FILES)) $errors[] = 'File uploads are not available on your directory profile. Attach a PDF resume when applying for a role.';
    $fields = ['headline'=>160,'location'=>160,'experience'=>255,'skills'=>3000,'bio'=>5000,'portfolio_url'=>1000,'availability'=>80];
    $values = [];
    foreach ($fields as $field=>$max) {
        $values[$field] = input($field);
        if (mb_strlen($values[$field]) > $max) $errors[] = ucfirst(str_replace('_',' ',$field)) . ' is too long.';
    }
    if ($values['headline'] === '') $errors[] = 'Add a headline so people understand what you do.';
    if ($values['portfolio_url'] !== '' && (!filter_var($values['portfolio_url'], FILTER_VALIDATE_URL) || strtolower(parse_url($values['portfolio_url'], PHP_URL_SCHEME) ?? '') !== 'https' || parse_url($values['portfolio_url'], PHP_URL_USER) !== null || parse_url($values['portfolio_url'], PHP_URL_PASS) !== null)) $errors[] = 'Use a complete HTTPS portfolio link without login details.';
    $values['visibility'] = input('visibility') === 'listed' ? 'listed' : 'private';
    if (!$errors) {
        $db->begin_transaction();
        db_one($db, 'SELECT id FROM talentprofile WHERE id = ? FOR UPDATE', [$user['id']]);
        $current = db_one($db, 'SELECT * FROM talent_portfolios WHERE account_id = ?', [$user['id']]);
        $version = hash('sha256', json_encode($current ?? $defaults));
        if (!hash_equals($version, input('version'))) {
            $db->rollback(); http_response_code(409); $errors[] = 'Your profile changed in another tab. Reload this page before saving again.';
        } else {
            db_run($db, 'INSERT INTO talent_portfolios (account_id, headline, location, experience, skills, bio, portfolio_url, availability, visibility) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE headline = VALUES(headline), location = VALUES(location), experience = VALUES(experience), skills = VALUES(skills), bio = VALUES(bio), portfolio_url = VALUES(portfolio_url), availability = VALUES(availability), visibility = VALUES(visibility)', [$user['id'], ...array_values($values)]);
            $db->commit(); flash('Profile saved. ' . ($values['visibility'] === 'listed' ? 'Your professional profile is visible in the employer directory.' : 'Your profile is private.'));
            redirect($returnJob ? '/users/talents/apply.php?job='.$returnJob : '/users/talents/talentpool.php');
        }
    } else http_response_code(422);
    $profile = [...$profile, ...$values];
}
$version = hash('sha256', json_encode(db_one($db, 'SELECT * FROM talent_portfolios WHERE account_id = ?', [$user['id']]) ?? $defaults));
if (http_response_code() === 409) $version = input('version');
suite_start('My talent profile','profile', ['company_name'=>$user['full_name'],'email'=>$user['email']]);
?>
<div class="page-heading"><div><h1>My talent profile</h1><p>Manage the professional details employers can see when your profile is listed.</p></div><span class="badge"><?= $profile['visibility'] === 'listed' ? 'Listed for employers' : 'Private profile' ?></span></div>
<?php if ($errors): ?><div class="notice error" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<div class="notice info">You control your visibility. Employers can see only the professional details below when you list your profile. Your sign-in email stays private in the directory. Applying for a role shares a separate copy of your profile and contact email with that employer, with your consent. You can attach a PDF resume when applying. Direct messaging is not available yet.</div>
<section class="panel record-form"><div class="panel-heading"><div><h2><?= e($user['full_name']) ?></h2><p class="subtle"><?= e($user['industry']) ?> · <?= e($user['email']) ?></p></div></div>
<form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="version" value="<?= e($version) ?>">
<div class="form-grid">
<?php foreach (['headline'=>['Professional headline',160,'e.g. Product designer focused on useful everyday tools'],'location'=>['Location (optional)',160,'e.g. Lagos, Nigeria'],'experience'=>['Experience (optional)',255,'e.g. Five years in product design'],'availability'=>['Availability (optional)',80,'e.g. Open to full-time roles']] as $field=>[$label,$max,$placeholder]): ?>
<div class="field"><label for="<?= e($field) ?>"><?= e($label) ?></label><input id="<?= e($field) ?>" name="<?= e($field) ?>" maxlength="<?= $max ?>" value="<?= e($profile[$field]) ?>" placeholder="<?= e($placeholder) ?>" <?= $field === 'headline' ? 'required' : '' ?>></div>
<?php endforeach; ?></div>
<?php foreach (['skills'=>['Skills',3000],'bio'=>['About your work',5000]] as $field=>[$label,$max]): ?><div class="field"><label for="<?= e($field) ?>"><?= e($label) ?> <span class="subtle">(optional)</span></label><textarea id="<?= e($field) ?>" name="<?= e($field) ?>" rows="4" maxlength="<?= $max ?>"><?= e($profile[$field]) ?></textarea><small>Include professional information only. Anything here is shared when you list your profile.</small></div><?php endforeach; ?>
<div class="field"><label for="portfolio_url">Portfolio link <span class="subtle">(optional)</span></label><input type="url" id="portfolio_url" name="portfolio_url" maxlength="1000" value="<?= e($profile['portfolio_url']) ?>" placeholder="https://"></div>
<div class="field"><label for="visibility">Profile visibility</label><select id="visibility" name="visibility"><option value="private" <?= $profile['visibility'] === 'private' ? 'selected' : '' ?>>Private — hidden from the directory</option><option value="listed" <?= $profile['visibility'] === 'listed' ? 'selected' : '' ?>>List my profile for verified business accounts</option></select><small>Listing shares your name, industry, and the professional details above. You can make your profile private again at any time. Submitted applications are separate and remain with their employer.</small></div>
<div class="form-actions"><button class="button" type="submit">Save profile <?= icon('check') ?></button><a class="text-link" href="/users/talents/resetpassword.php">Change password</a></div>
</form></section>
<?php suite_end(); ?>

<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/attachments.php';
$db = database(); $user = workspace_user($db); $companyId = (int)$user['company_id'];
$available = visible_modules($db,$user);
$documents = module_config('documents');
$canUpload = isset($available['documents']) && module_access($db,$user,'documents') === 'edit';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    require_module_access($db,$user,'documents',true);
    [$upload,$error] = validate_attachment_upload($_FILES['file'] ?? null);
    if ($error) { flash($error,'error'); redirect('/users/admin/files.php#upload'); }
    $title = input('title') !== '' ? input('title') : (string)pathinfo($upload['name'],PATHINFO_FILENAME);
    [$values,$errors] = validate_record($db,$companyId,$documents,['title'=>mb_substr($title,0,160),'category'=>input('category'),'notes'=>input('notes')]);
    if ($errors) { flash(reset($errors),'error'); redirect('/users/admin/files.php#upload'); }
    $documentId = save_record($db,$companyId,'documents',$values,null);
    [$fileId,$error] = store_record_attachment($db,$user,'documents',$documentId,$upload);
    if ($error) {
        db_run($db,'DELETE FROM documents WHERE id = ? AND company_id = ?',[$documentId,$companyId]);
        flash($error,'error'); redirect('/users/admin/files.php#upload');
    }
    flash('“' . $values['title'] . '” uploaded to Documents.');
    redirect('/users/admin/files.php');
}
$kinds = ['pdf'=>['PDF',"f.media_type = 'application/pdf'"],'image'=>['Images',"f.media_type LIKE 'image/%'"],'office'=>['Office documents',"f.media_type LIKE 'application/vnd.openxmlformats%'"],'csv'=>['CSV',"f.media_type = 'text/csv'"]];
$query = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']),0,100) : '';
$tool = is_string($_GET['tool'] ?? null) && isset($available[$_GET['tool']]) ? $_GET['tool'] : '';
$kind = is_string($_GET['kind'] ?? null) && isset($kinds[$_GET['kind']]) ? $_GET['kind'] : '';
$keys = array_keys($available);
$rows = []; $count = 0; $pages = 1; $page = 1;
if ($keys) {
    $where = 'f.company_id = ? AND f.module_key IN (' . implode(',',array_fill(0,count($keys),'?')) . ') AND (s.application_id IS NULL OR s.withdrawn_at IS NULL)';
    $params = [$companyId,...$keys];
    if ($query !== '') { $where .= " AND f.original_name LIKE ? ESCAPE '!'"; $params[] = '%' . str_replace(['!','%','_'],['!!','!%','!_'],$query) . '%'; }
    if ($tool !== '') { $where .= ' AND f.module_key = ?'; $params[] = $tool; }
    if ($kind !== '') $where .= ' AND ' . $kinds[$kind][1];
    $from = 'FROM record_attachments f LEFT JOIN application_submissions s ON s.resume_attachment_id = f.id AND s.company_id = f.company_id';
    $count = (int)db_one($db,"SELECT COUNT(*) AS count $from WHERE $where",$params)['count'];
    $pages = max(1,(int)ceil($count/30)); $page = min($pages,max(1,(int)($_GET['page'] ?? 1)));
    $rows = db_rows($db,"SELECT f.id, f.module_key, f.record_id, f.original_name, f.media_type, f.size_bytes, f.created_at $from WHERE $where ORDER BY f.id DESC LIMIT 30 OFFSET " . (($page-1)*30),$params);
}
$titles = []; $editable = [];
foreach (array_unique(array_column($rows,'module_key')) as $key) {
    $module = $available[$key];
    $ids = array_values(array_unique(array_map('intval',array_column(array_filter($rows,fn($row)=>$row['module_key'] === $key),'record_id'))));
    foreach (db_rows($db,"SELECT * FROM `{$module['table']}` WHERE company_id = ? AND `{$module['pk']}` IN (" . implode(',',array_fill(0,count($ids),'?')) . ')',[$companyId,...$ids]) as $record) $titles[$key][(int)$record[$module['pk']]] = record_title($module,$record);
    $editable[$key] = module_access($db,$user,$key) === 'edit' && $key !== 'applications';
}
$usage = db_one($db,'SELECT COUNT(*) AS files, COALESCE(SUM(size_bytes),0) AS bytes FROM record_attachments WHERE company_id = ?',[$companyId]);
$percent = min(100,(int)round((int)$usage['bytes'] / (100*1024*1024) * 100));
$link = fn(array $changes) => '?' . http_build_query(array_filter(array_merge(['q'=>$query,'tool'=>$tool,'kind'=>$kind],$changes),fn($value)=>$value !== '' && $value !== null));
suite_start('Files','files',$user);
?>
<div class="page-heading"><div><h1>Files</h1><p>Every file across your workspace in one place. Upload documents, find attachments and download what you need.</p></div><?php if ($canUpload): ?><a class="button" href="#upload"><?= icon('upload') ?> Upload file</a><?php endif; ?></div>
<div class="files-summary">
<section class="stat-card storage-card"><div class="stat-label"><span>Workspace storage</span><span class="kpi-icon"><?= icon('box') ?></span></div><div class="stat-value"><?= e(attachment_size((int)$usage['bytes'])) ?> <small>of 100 MB</small></div><div class="storage-meter<?= $percent >= 90 ? ' high' : '' ?>"><span style="width:<?= max(1,$percent) ?>%"></span></div><div class="stat-foot"><?= number_format((int)$usage['files']) ?> <?= (int)$usage['files'] === 1 ? 'file' : 'files' ?> · <?= $percent ?>% used</div></section>
<?php if ($canUpload): ?><section class="panel upload-panel" id="upload"><form method="post" enctype="multipart/form-data" class="upload-form" data-submit><?= csrf_field() ?><input type="hidden" name="MAX_FILE_SIZE" value="<?= attachment_limit() ?>"><label class="dropzone" data-dropzone><input type="file" name="file" accept="<?= e(attachment_accept()) ?>" required data-title-target="file-title" aria-describedby="file-help"><span class="dropzone-icon"><?= icon('upload') ?></span><strong data-dropzone-label>Drop a file here or browse</strong><small id="file-help"><?= e(attachment_types_label()) ?> · up to <?= number_format(attachment_limit()/1048576,1) ?> MB</small></label><div class="upload-fields"><div class="field"><label for="file-title">Title (optional)</label><input id="file-title" name="title" maxlength="160" placeholder="Uses the file name if left empty"></div><div class="field"><label for="file-category">Category</label><select id="file-category" name="category"><?php foreach ($documents['fields']['category']['options'] as $option): ?><option value="<?= e($option) ?>"><?= e($option) ?></option><?php endforeach; ?></select></div><button class="button" type="submit"><?= icon('upload') ?> Upload to Documents</button></div></form></section><?php endif; ?>
</div>
<section class="panel"><form method="get" class="table-toolbar files-filters" role="search"><span class="search-field"><?= icon('search') ?><label class="sr-only" for="file-search">Search files</label><input class="filter-input" id="file-search" type="search" name="q" value="<?= e($query) ?>" maxlength="100" placeholder="Search file names…"></span><label class="sr-only" for="file-tool">Tool</label><select id="file-tool" name="tool"><option value="">All tools</option><?php foreach ($available as $key=>$module): ?><option value="<?= e($key) ?>"<?= $tool === $key ? ' selected' : '' ?>><?= e($module['title']) ?></option><?php endforeach; ?></select><label class="sr-only" for="file-kind">File type</label><select id="file-kind" name="kind"><option value="">All types</option><?php foreach ($kinds as $value=>[$label]): ?><option value="<?= e($value) ?>"<?= $kind === $value ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select><button class="button small secondary" type="submit">Filter</button><span class="count-label"><?= number_format($count) ?> <?= $count === 1 ? 'file' : 'files' ?></span></form>
<?php if (!$rows): ?><div class="empty-state"><?= icon('file') ?><h3><?= $query !== '' || $tool !== '' || $kind !== '' ? 'No matching files.' : 'No files yet' ?></h3><p><?= $query !== '' || $tool !== '' || $kind !== '' ? 'Try a different search or filter.' : 'Upload a document above, or attach files to projects, tasks, deals and other records.' ?></p><?php if ($query !== '' || $tool !== '' || $kind !== ''): ?><a class="button small secondary" href="/users/admin/files.php">Clear filters</a><?php endif; ?></div>
<?php else: ?><ul class="file-list"><?php foreach ($rows as $file): $module = $available[$file['module_key']]; $fileKind = attachment_kind($file['media_type']); $recordTitle = $titles[$file['module_key']][(int)$file['record_id']] ?? ucfirst($module['singular']); $recordUrl = $file['module_key'] === 'applications' ? '/users/admin/application.php?id=' . (int)$file['record_id'] : '/users/admin/workspace.php?module=' . rawurlencode($file['module_key']) . '&action=view&id=' . (int)$file['record_id']; ?>
<li><span class="file-type <?= e(strtolower($fileKind)) ?>"><?= e(match ($fileKind) { 'PowerPoint' => 'PPT', 'Excel' => 'XLS', 'Word' => 'DOC', 'Image' => 'IMG', default => strtoupper($fileKind) }) ?></span><div class="file-main"><a href="/api/attachments.php?id=<?= (int)$file['id'] ?>" class="file-name"><?= e($file['original_name']) ?></a><small><?= e($fileKind) ?> · <?= e(attachment_size((int)$file['size_bytes'])) ?> · <?= e(date('j M Y',strtotime($file['created_at'] . ' UTC'))) ?></small></div><a class="file-record" href="<?= e($recordUrl) ?>"><?= icon($module['icon']) ?><span><?= e($recordTitle) ?><small><?= e($module['title']) ?></small></span></a><div class="file-actions"><a class="icon-button" href="/api/attachments.php?id=<?= (int)$file['id'] ?>" aria-label="Download <?= e($file['original_name']) ?>" title="Download"><?= icon('download') ?></a><?php if ($editable[$file['module_key']] ?? false): ?><form method="post" action="/api/attachments.php" data-confirm="Remove <?= e($file['original_name']) ?>? This cannot be undone."><?= csrf_field() ?><input type="hidden" name="module" value="<?= e($file['module_key']) ?>"><input type="hidden" name="record_id" value="<?= (int)$file['record_id'] ?>"><input type="hidden" name="id" value="<?= (int)$file['id'] ?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="return_to_files" value="1"><button class="icon-button danger" type="submit" aria-label="Remove <?= e($file['original_name']) ?>" title="Remove"><?= icon('trash') ?></button></form><?php endif; ?></div></li>
<?php endforeach; ?></ul><?php endif; ?>
<?php if ($pages > 1): ?><div class="table-toolbar"><span class="count-label">Page <?= $page ?> of <?= $pages ?></span><div class="form-actions"><?php if ($page > 1): ?><a class="button small secondary" href="<?= e($link(['page'=>$page-1])) ?>">Previous</a><?php endif; ?><?php if ($page < $pages): ?><a class="button small secondary" href="<?= e($link(['page'=>$page+1])) ?>">Next</a><?php endif; ?></div></div><?php endif; ?>
</section>
<p class="subtle">Files are private to people with access to the tool they belong to. Downloads are not scanned for viruses; open only files you trust.</p>
<?php suite_end(); ?>

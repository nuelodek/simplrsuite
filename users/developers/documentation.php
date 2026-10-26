<?php
// Consolidated documentation route from simplr24, describing the implemented API.
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/view.php';
$user = admin_user(database());
suite_start('API documentation','integrations',$user);
?>
<div class="page-heading"><div><h1>Workspace API guide</h1><p>The record API used by this application, with its current capabilities and limits.</p></div><a class="button secondary" href="/users/admin/integrations.php">Back to integrations</a></div>
<div class="notice info">This is a same-origin browser-session API for verified business administrators and staff with explicitly assigned tool access. API keys, OAuth, third-party access, and payment endpoints are not available.</div>
<section class="panel record-form"><h2>Authentication</h2><p>Sign in to your business workspace. Requests use its session cookie, and the server selects the company from that session. A submitted company ID never grants access to another workspace.</p><p>For POST requests, use the CSRF token from a form in the current session as <code>csrf_token</code>, or send it in an <code>X-CSRF-Token</code> header. Never put passwords or session tokens in a URL.</p>
<h2>Read records</h2><p><code>GET /api/records.php?module=projects&amp;page=1</code></p><p>Responses contain <code>ok</code>, <code>data</code>, <code>page</code>, <code>per_page</code> (25), and <code>total</code>. Records belong to your workspace and are ordered by descending record ID.</p>
<pre class="api-code"><code>const response = await fetch('/api/records.php?module=projects', {
  credentials: 'same-origin',
  headers: { Accept: 'application/json' }
});
const result = await response.json();
if (!response.ok) throw new Error(result.error);
console.log(result.data);</code></pre>
<h2>Create a record</h2><p><code>POST /api/records.php?module=departments</code> accepts URL-encoded form fields. The example below creates a record when executed. JSON request bodies and API deletion are not supported. Staff require edit access to create or update records; directory changes remain administrator-only.</p>
<pre class="api-code"><code>const form = document.querySelector('form');
const csrf = form.querySelector('[name="csrf_token"]').value;
const response = await fetch('/api/records.php?module=departments', {
  method: 'POST',
  credentials: 'same-origin',
  headers: { Accept: 'application/json' },
  body: new URLSearchParams({
    csrf_token: csrf,
    department_name: 'Operations',
    location: 'Lagos'
  })
});
const result = await response.json();
if (!response.ok) throw new Error(result.error);</code></pre>
<p>Successful creation returns HTTP 201 with <code>{"ok":true,"id":123}</code>. The ID above is illustrative. Related-record fields must use values offered by the corresponding workspace form; values from another company are rejected.</p>
<h2>Update a record</h2><p>POST to the same module URL with <code>action=update</code>, the numeric <code>id</code>, the <code>version</code> returned when reading that record, and every field from its form. Successful updates return HTTP 200. A stale or missing version returns 409 so changes cannot silently overwrite another edit. Permission revocation applies to forms already open.</p>
<h2>Submitted job applications</h2><p>Candidate-submitted applications appear in the existing <code>applications</code> record area. Their identity and profile snapshot are preserved: generic updates return 409. Use the application review page to update their stage. Candidate resumes cannot be removed through the generic attachment API. After withdrawal, employer resume access ends; the applicant can still download their own submitted file.</p>
<h2>Errors</h2><ul><li>401: sign-in required or session no longer valid.</li><li>402: toolkit subscription expired; reading and exporting remain available.</li><li>403: tool access is missing or does not allow editing.</li><li>404: unknown module or record outside your workspace.</li><li>405: unsupported request method.</li><li>409: conflicting unique record or stale edit version.</li><li>419: missing or expired CSRF token.</li><li>422: invalid fields, with a <code>fields</code> object.</li><li>503: service temporarily unavailable.</li></ul></section>
<section class="panel record-form"><h2>Private record attachments</h2><p>Use the View action on a record to upload or download its attachments. The attachment endpoint shares the same company and module permissions as the record API.</p><p><code>POST /api/attachments.php</code> accepts multipart form data with <code>module</code>, <code>record_id</code>, <code>csrf_token</code> and the <code>attachment</code> file. Successful uploads return 201 with the attachment ID. Only PDF, PNG and JPEG are accepted; the record form displays the effective server size limit. Up to 20 files per record and 100 MB per company are allowed.</p><p><code>GET /api/attachments.php?id=123</code> downloads an authorized attachment. Removal uses POST with <code>action=remove</code>, <code>id</code>, <code>module</code>, <code>record_id</code> and CSRF. Edit access is required for both upload and removal; view access permits downloading. No public storage URL or inline rendering is provided.</p></section>
<section class="panel record-form"><h2>Available record areas</h2><p>These definitions come from the same field configuration as the workspace forms.</p>
<?php foreach (modules() as $key=>$module): ?><details class="api-module"><summary><?= e($module['title']) ?> <code><?= e($key) ?></code></summary><div class="table-scroll"><table class="data-table"><thead><tr><th>Field</th><th>Format</th><th>Required</th><th>Allowed values</th></tr></thead><tbody><?php foreach ($module['fields'] as $name=>$field): ?><tr><td><code><?= e($name) ?></code></td><td><?= e($field['type']) ?></td><td><?= $field['required'] ? 'Yes' : 'No' ?></td><td><?= e(implode(', ', $field['options'])) ?></td></tr><?php endforeach; ?></tbody></table></div><p><a class="text-link" href="/users/admin/workspace.php?module=<?= e($key) ?>&amp;action=new">Open the <?= e($module['singular']) ?> form <?= icon('arrow') ?></a></p></details><?php endforeach; ?></section>
<?php suite_end(); ?>

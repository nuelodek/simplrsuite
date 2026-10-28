"""Duplicate and concurrent requests produce one durable outcome.

Synthetic example.test workspace only, local server, MAIL_TRANSPORT=log (emails are
files in storage/mail). Covers:
  - six processes delivering the same queued alert email at once: exactly one email;
  - a fresh send claim blocks a second send; a claim older than ten minutes is retried;
  - five browsers accepting the same staff invitation at once: one account, one success.
GitHub sync concurrency is covered in github_smoke_test.py; payment settlement races in
paystack_http_test.py and paystack_concurrency_worker.php.
"""
from access_smoke_test import ROOT, Client, expect, checks, rows
import json
import re
import subprocess
import threading
import time
import uuid

MAIL = ROOT / 'storage' / 'mail'


def php(code):
    return subprocess.run(['php', '-r', 'require "app/notifications.php"; $db = database(); ' + code], cwd=ROOT, capture_output=True, text=True, check=True).stdout.strip()


def mails(token):
    found = []
    for path in MAIL.glob('*.json'):
        try:
            data = json.loads(path.read_text(encoding='utf-8'))
        except (OSError, ValueError):
            continue
        if token in data.get('subject', '') or token in data.get('text', ''):
            found.append(data)
    return found


fixture = json.loads((ROOT / '.local/browser-fixture.json').read_text())
staff = json.loads((ROOT / '.local/recovery-browser-fixture.json').read_text())
assert re.fullmatch(r'alpha-\d+@example\.test', fixture['email'])
company = php('echo db_one($db, "SELECT company_id FROM users WHERE email = ?", [' + json.dumps(fixture['email']) + '])["company_id"];')
owner = php('echo db_one($db, "SELECT id FROM users WHERE email = ?", [' + json.dumps(fixture['email']) + '])["id"];')

# 1. Six processes deliver one queued formal alert (formal notices always email).
token = 'Idempotency ' + uuid.uuid4().hex[:10]
alert = php('echo notify($db, ' + company + ', "admin", ' + owner + ', "system", ' + json.dumps(token) + ', "Synthetic race check.", "/users/admin/notifications.php", true, ' + json.dumps('race:' + token) + ');')
expect(alert.isdigit() and php('echo db_one($db, "SELECT email_status FROM notifications WHERE id = ?", [' + alert + '])["email_status"];') == 'pending', 'CLI notify queues the email for the scheduler')
start = time.time() + 2
racer = 'require "app/notifications.php"; $db = database(); while (microtime(true) < ' + repr(start) + ') usleep(200); echo notification_deliver($db, ' + alert + ');'
processes = [subprocess.Popen(['php', '-r', racer], cwd=ROOT, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True) for _ in range(6)]
results = [p.communicate(timeout=60)[0].strip() for p in processes]
row = json.loads(php('echo json_encode(db_one($db, "SELECT email_status, email_attempts FROM notifications WHERE id = ?", [' + alert + ']));'))
expect(results.count('sent') == 1, 'Exactly one of six concurrent deliveries sends (' + ','.join(results) + ')')
expect(len(mails(token)) == 1, 'Exactly one email was written for the alert')
expect(row['email_status'] == 'sent' and int(row['email_attempts']) == 1, 'The alert records one successful attempt')

# 2. A fresh claim blocks a resend; a stale claim (sender died) is retried once.
token2 = 'Idempotency claim ' + uuid.uuid4().hex[:10]
alert2 = php('echo notify($db, ' + company + ', "admin", ' + owner + ', "system", ' + json.dumps(token2) + ', "Synthetic claim check.", "/users/admin/notifications.php", true, ' + json.dumps('claim:' + token2) + ');')
php('db_run($db, "UPDATE notifications SET email_status = \'sending\', email_claimed_at = UTC_TIMESTAMP() WHERE id = ?", [' + alert2 + ']); echo "ok";')
expect(php('echo notification_deliver($db, ' + alert2 + ');') == 'sending' and not mails(token2), 'A message another process is sending is not sent again')
flushed = json.loads(php('echo json_encode(notification_flush_outbox($db));'))
expect(not mails(token2), 'The outbox flush leaves a freshly claimed message alone')
php('db_run($db, "UPDATE notifications SET email_claimed_at = UTC_TIMESTAMP() - INTERVAL 11 MINUTE WHERE id = ?", [' + alert2 + ']); echo "ok";')
expect(php('echo notification_deliver($db, ' + alert2 + ');') == 'sent' and len(mails(token2)) == 1, 'A claim older than ten minutes is retried and sends once')

# 3. Five browsers accept one invitation at the same moment.
admin = Client()
admin.login('admin', fixture['email'], fixture['password'])
template = next(x for x in rows(admin, 'employees') if x['email'] == staff['email'])
email = 'race-' + uuid.uuid4().hex[:12] + '@example.test'
token_form, _ = admin.form('/users/admin/workspace.php?module=employees&action=new')
status, body, _ = admin.request('/api/records.php?module=employees', {'csrf_token': token_form, 'first_name': 'Race', 'last_name': 'Check', 'email': email, 'hire_date': '2026-10-28', 'job_title': 'Synthetic test', 'department_id': template['department_id'], 'manager_id': template['manager_id']})
expect(status == 201, 'Owner creates a synthetic employee to invite')
employee = str(json.loads(body)['id'])
token_form, _ = admin.form('/users/admin/workspace.php?module=employees')
admin.request('/users/admin/invite.php', {'csrf_token': token_form, 'role': 'employee', 'staff_id': employee}, False)
invitation = next((m for m in mails('') if m.get('to') == email), None)
link = re.search(r'accept-invitation\.php\?token=([a-f0-9]{64})', (invitation or {}).get('text', ''))
expect(link is not None, 'Invitation email (log transport) carries a single-use link')
path = '/users/accept-invitation.php?token=' + link.group(1)
browsers = []
for _ in range(5):
    browser = Client()
    browsers.append((browser, browser.form(path)[0]))
barrier = threading.Barrier(len(browsers))
outcomes = []


def accept(browser, csrf):
    barrier.wait()
    status, _, url = browser.request(path, {'csrf_token': csrf, 'password': 'Race-condition-passphrase-2026!', 'confirm_password': 'Race-condition-passphrase-2026!'}, False)
    outcomes.append('ok' if status == 200 and '/users/employees/login.php' in url else str(status))


threads = [threading.Thread(target=accept, args=pair) for pair in browsers]
for thread in threads:
    thread.start()
for thread in threads:
    thread.join(60)
accounts = php('echo db_one($db, "SELECT COUNT(*) AS n FROM employees_auth WHERE company_id = ? AND (employee_id = (SELECT unique_id FROM employees WHERE id = ?) OR email = ?)", [' + company + ', ' + employee + ', ' + json.dumps(email) + '])["n"];')
expect(outcomes.count('ok') == 1, 'Exactly one of five concurrent acceptances succeeds (' + ','.join(sorted(outcomes)) + ')')
expect(all(o in ('ok', '410') for o in outcomes), 'The others are told the invitation is no longer available')
expect(accounts == '1', 'One sign-in record exists for the invited employee')
expect(php('echo db_one($db, "SELECT COUNT(*) AS n FROM staff_invitations WHERE staff_id = ? AND company_id = ? AND used_at IS NULL", [' + employee + ', ' + company + '])["n"];') == '0', 'The invitation is marked used')

print(json.dumps({'checks': len(checks), 'passed': sum(c['passed'] for c in checks), 'failures': [c['name'] for c in checks if not c['passed']]}))

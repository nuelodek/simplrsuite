"""GitHub integration journey against the local GitHub stub. Never contacts github.com.

Start the stub and a second app server pointed at it, then run this script:
  php -S 127.0.0.1:8091 scripts/github_stub_server.php
  APP_ENV=local MAIL_TRANSPORT=log APP_URL=http://127.0.0.1:8002 GITHUB_API_URL=http://127.0.0.1:8091 \
    GITHUB_OAUTH_URL=http://127.0.0.1:8091 GITHUB_CLIENT_ID=stub-client GITHUB_CLIENT_SECRET=stub-secret \
    php -S 127.0.0.1:8002 -t . router.php
Requires .local/browser-fixture.json from scripts/smoke_test.py (a synthetic workspace).
"""
from pathlib import Path
from urllib.request import Request, build_opener, HTTPCookieProcessor
from urllib.parse import urlencode
from urllib.error import HTTPError
from http.cookiejar import CookieJar
import html
import json
import os
import re
import subprocess
import sys
import time

ROOT = Path(__file__).resolve().parent.parent
BASE = os.environ.get('SIMPLR_GITHUB_BASE', 'http://127.0.0.1:8002')
STUB = 'http://127.0.0.1:8091'
if not re.search(r'^APP_ENV=local$', (ROOT / '.env').read_text(), re.M) or not BASE.startswith('http://127.0.0.1:'):
    sys.exit('Refusing to test outside a local environment.')
checks = []
TOKEN = 'ghp_stubToken0123456789abcdefABCDEF'
TOKEN_B = 'ghp_stubSecondAccount0123456789abcd'


def expect(condition, label):
    checks.append({'name': label, 'passed': bool(condition)})
    if not condition:
        raise AssertionError(label)


class Client:
    def __init__(self):
        self.opener = build_opener(HTTPCookieProcessor(CookieJar()))

    def request(self, path, data=None, api=False):
        body = urlencode(data, doseq=True).encode() if data is not None else None
        request = Request(path if path.startswith('http') else BASE + path, data=body, headers={'Accept': 'application/json' if api else 'text/html'})
        try:
            response = self.opener.open(request, timeout=30)
        except HTTPError as error:
            response = error
        return response.status, response.read().decode(), response.url

    def token(self, path='/users/admin/github.php'):
        code, body, _ = self.request(path)
        match = re.search(r'name="csrf_token" value="([a-f0-9]+)"', body)
        expect(code == 200 and match, 'CSRF token available on ' + path.split('?')[0])
        return match.group(1)

    def post(self, data, path='/users/admin/github.php'):
        return self.request(path, {'csrf_token': self.token(path), **data})


def php(code):
    return subprocess.run(['php', '-r', 'require "app/database.php"; $db = database(); ' + code], cwd=ROOT, capture_output=True, text=True, check=True).stdout.strip()


fixture = json.loads((ROOT / '.local/browser-fixture.json').read_text())
admin = Client()
admin.request(STUB + '/__test/reset')
code, body, _ = admin.request('/users/admin/login.php')
csrf = re.search(r'name="csrf_token" value="([a-f0-9]+)"', body).group(1)
code, body, url = admin.request('/users/admin/login_process.php', {'csrf_token': csrf, 'email': fixture['email'], 'password': fixture['password']})
expect('/newdashboard.php' in url, 'Fixture owner signs in')
company = php('echo db_one($db, "SELECT company_id FROM users WHERE email = ?", [' + json.dumps(fixture['email']) + '])["company_id"];')

anonymous = Client()
expect('/login.php' in anonymous.request('/users/admin/github.php')[2], 'GitHub page requires sign-in')
def connection_id(login):
    return php('$row = db_one($db, "SELECT id FROM integration_connections WHERE company_id = ? AND provider = \'github\' AND account_login = ?", [' + company + ', ' + json.dumps(login) + ']); echo $row["id"] ?? "";')


def disconnect_all():
    for cid in json.loads(php('echo json_encode(array_column(db_rows($db, "SELECT id FROM integration_connections WHERE company_id = ? AND provider = \'github\'", [' + company + ']), "id"));')):
        admin.post({'action': 'disconnect', 'connection_id': cid})


disconnect_all()
for stale in json.loads(php('echo json_encode(array_column(db_rows($db, "SELECT id FROM github_repository_links WHERE company_id = ?", [' + company + ']), "id"));')):
    admin.post({'action': 'unlink', 'link_id': stale})
php('db_run($db, "UPDATE employees SET github_login = NULL WHERE company_id = ?", [' + company + ']); echo "ok";')
code, body, _ = admin.request('/users/admin/github.php')
expect(code == 200 and 'Not connected' in body and 'href="?oauth=start"' in body and 'Other ways to connect' in body, 'Owner sees one-click connect with a token fallback')
code, body, _ = admin.post({'action': 'connect_token', 'token': 'short'})
expect('Paste the full GitHub token' in body, 'Malformed token is rejected before contacting GitHub')
code, body, _ = admin.post({'action': 'connect_token', 'token': 'ghp_' + 'x' * 36})
expect('GitHub rejected the saved access' in body, 'Token GitHub rejects is not stored')
code, body, _ = admin.post({'action': 'connect_token', 'token': TOKEN})
expect('GitHub account @simplr-demo connected' in body and 'Connected accounts' in body, 'Valid token connects the first account')
cipher = php('echo db_one($db, "SELECT token_ciphertext FROM integration_connections WHERE company_id = ? AND provider = \'github\' AND account_login = \'simplr-demo\'", [' + company + '])["token_ciphertext"];')
expect(cipher.startswith('v1:') and TOKEN not in cipher, 'Token is encrypted at rest')
expect(TOKEN not in body, 'Token is never echoed back to the browser')
expect('simplr-demo/website' in body and 'simplr-demo/mobile-app' in body, 'Repository picker lists accessible repositories')
code, body, _ = admin.post({'action': 'connect_token', 'token': TOKEN})
count = php('echo db_one($db, "SELECT COUNT(*) AS count FROM integration_connections WHERE company_id = ? AND provider = \'github\'", [' + company + '])["count"];')
expect('was already connected; its token was updated' in body and count == '1', 'Connecting the same account again refreshes it instead of duplicating it')
code, body, _ = admin.post({'action': 'connect_token', 'token': TOKEN_B})
expect('GitHub account @acme-labs connected' in body and '2 accounts connected' in body, 'A second GitHub account can be added')
expect('acme-labs/api' in body and 'via @acme-labs' in body and 'via @simplr-demo' in body and 'Through account' in body, 'Picker combines repositories from both accounts and names each account')
expect(body.count('Check organisation access') == 0 and body.count('name="connection_id"') >= 2, 'Each account has its own disconnect control')

created = admin.request('/api/records.php?module=projects', {'csrf_token': admin.token(), 'projectName': 'GitHub sync ' + str(int(time.time())), 'startDate': '2026-10-01', 'endDate': '2026-12-31', 'status': 'ongoing'}, api=True)
project = json.loads(created[1])['id']
code, body, _ = admin.post({'action': 'link', 'project_id': project, 'repository': '../etc/passwd'})
expect('Choose a repository in owner/name form' in body, 'Malformed repository names are rejected')
code, body, _ = admin.post({'action': 'link', 'project_id': '999999999', 'repository': 'simplr-demo/website'})
expect('Choose a project from your workspace' in body, 'Projects outside the workspace cannot be linked')
code, body, _ = admin.post({'action': 'link', 'project_id': project, 'repository': 'simplr-demo/website'})
expect('simplr-demo/website linked to the project through @simplr-demo' in body, 'Repository links through the account that can see it')
code, body, _ = admin.post({'action': 'link', 'project_id': project, 'repository': 'acme-labs/api', 'connection_id': connection_id('simplr-demo')})
expect('GitHub could not find this repository' in body, 'A chosen account that cannot see the repository is refused')
code, body, _ = admin.post({'action': 'link', 'project_id': project, 'repository': 'acme-labs/api'})
expect('acme-labs/api linked to the project through @acme-labs' in body, 'Second account links its own repository automatically')
code, body, _ = admin.post({'action': 'link', 'project_id': project, 'repository': 'nobody/secret'})
expect('None of your connected GitHub accounts can see nobody/secret' in body, 'A repository no account can see is explained')

code, body, _ = admin.request('/users/admin/workspace.php?module=projects&action=view&id=%d' % project)
expect('Write onboarding guide' in body and 'Ship invoice export' in body and 'Draft' in body, 'Project page shows the first account repository activity')
expect('Rate limit the public API' in body and 'Add API keys page' in body and 'via @acme-labs' in body, 'Project page shows the second account repository through its own access')
expect('&lt;script&gt;alert(1)&lt;/script&gt;' in body and '<script>alert(1)' not in body, 'GitHub text is escaped on the project page')
website_block = body.split('simplr-demo/website', 1)[1]
expect('Add dark mode' not in website_block.split('Open issues', 1)[1].split('Pull requests', 1)[0], 'Pull requests are not listed as issues')

link = php('echo db_one($db, "SELECT id FROM github_repository_links WHERE company_id = ? AND project_id = ? AND repo_full_name = \'simplr-demo/website\'", [' + company + ', ' + str(project) + '])["id"];')
acme_link = php('echo db_one($db, "SELECT id FROM github_repository_links WHERE company_id = ? AND project_id = ? AND repo_full_name = \'acme-labs/api\'", [' + company + ', ' + str(project) + '])["id"];')
stamp = str(int(time.time()))
department = php('$row = db_one($db, "SELECT id FROM departments WHERE company_id = ? ORDER BY id LIMIT 1", [' + company + ']); echo $row["id"] ?? "";')
if not department:
    department = str(json.loads(admin.request('/api/records.php?module=departments', {'csrf_token': admin.token(), 'department_name': 'Engineering ' + stamp}, api=True)[1])['id'])


def employee(first):
    created = admin.request('/api/records.php?module=employees', {'csrf_token': admin.token(), 'first_name': first, 'last_name': 'Builder' + stamp, 'email': first.lower() + '-gh-' + stamp + '@example.test', 'hire_date': '2026-01-05', 'department_id': department}, api=True)
    return json.loads(created[1])['id']


def task_rows():
    return json.loads(php('echo json_encode(db_rows($db, "SELECT t.id, t.title, t.priority, t.status, t.due_date, t.assignee FROM github_issue_tasks g JOIN tasks t ON t.id = g.task_id WHERE g.company_id = ? AND g.link_id = ? ORDER BY g.issue_number", [' + company + ', ' + link + ']));'))


ada, grace = employee('Ada'), employee('Grace')
code, body, _ = admin.post({'action': 'people', 'login[]': ['-bad-'], 'employee[]': [str(ada)]})
expect('is not a GitHub username' in body, 'Invalid GitHub usernames are rejected')
code, body, _ = admin.post({'action': 'people', 'login[]': ['@ada'], 'employee[]': [str(ada)]})
expect('GitHub matches saved' in body and 'Match GitHub people to employees' in body, 'Owner matches a GitHub username to an employee')
admin.request(STUB + '/__test/assign?issue=2&login=grace')
code, body, _ = admin.post({'action': 'sync', 'link_id': link})
expect('2 new tasks added from GitHub issues and 0 tasks completed. 1 assigned to employees.' in body and 'Match @grace to an employee' in body, 'Sync assigns matched people and names unmatched ones')
tasks = task_rows()
expect(len(tasks) == 2 and tasks[0]['priority'] == 'high' and tasks[0]['due_date'] == '2026-12-01' and tasks[0]['status'] == 'ongoing', 'Urgent label, milestone date and assignee map onto the task')
expect(tasks[1]['priority'] == 'low' and tasks[1]['due_date'] == '2026-12-31', 'Low label and project end date map onto the task')
expect(tasks[0]['assignee'] == str(ada) and tasks[1]['assignee'] == '', 'Issue assigned to @ada becomes Ada’s task; unmatched stays unassigned')
alerted = php('echo db_one($db, "SELECT COUNT(*) AS count FROM notifications WHERE company_id = ? AND recipient_role = \'employee\' AND recipient_id = ? AND kind = \'task.assigned\'", [' + company + ', ' + str(ada) + '])["count"];')
expect(alerted == '1', 'Ada gets a “new task from GitHub” alert')
code, body, _ = admin.request('/users/admin/github.php')
expect('@grace' in body and '@ada' in body, 'GitHub page lists usernames seen on open issues')
code, body, _ = admin.post({'action': 'people', 'login[]': ['ada', 'grace'], 'employee[]': [str(ada), str(grace)]})
code, body, _ = admin.post({'action': 'sync', 'link_id': link})
expect('1 assigned to employees' in body and task_rows()[1]['assignee'] == str(grace), 'Matching later fills in unassigned synced tasks')
row = next(r for r in json.loads(admin.request('/api/records.php?module=tasks', api=True)[1])['data'] if r['id'] == tasks[0]['id'])
update = {'csrf_token': admin.token(), 'action': 'update', 'id': row['id'], 'version': row['version'], 'title': row['title'], 'project_id': row['project_id'], 'description': row['description'] or '', 'due_date': row['due_date'], 'priority': row['priority'], 'status': row['status'], 'assignee': str(grace)}
expect(admin.request('/api/records.php?module=tasks', update, api=True)[0] == 200, 'Task can be reassigned in Simplr')
code, body, _ = admin.post({'action': 'sync', 'link_id': link})
expect('Tasks are already up to date with GitHub' in body and task_rows()[0]['assignee'] == str(grace), 'A manual reassignment in Simplr is kept while GitHub is unchanged')
admin.request(STUB + '/__test/assign?issue=1&login=grace')
admin.post({'action': 'sync', 'link_id': link})
admin.request(STUB + '/__test/assign?issue=1&login=ada')
code, body, _ = admin.post({'action': 'sync', 'link_id': link})
expect('1 assigned to employees' in body and task_rows()[0]['assignee'] == str(ada), 'Reassigning the issue on GitHub reassigns the task')
code, body, _ = admin.post({'action': 'sync', 'link_id': acme_link})
expect('1 new task added from GitHub issues and 0 tasks completed' in body, 'Second account repository syncs through its own access')
code, body, _ = admin.post({'action': 'sync', 'link_id': link})
expect('Tasks are already up to date with GitHub' in body, 'Repeat sync does not duplicate tasks')
admin.request(STUB + '/__test/close?issue=1')
code, body, _ = admin.post({'action': 'sync', 'link_id': link, 'return_to': 'project', 'project_id': project})
expect('0 new tasks added from GitHub issues and 1 task completed' in body and '/workspace.php?module=projects' in _, 'Closing an issue completes its task and returns to the project')
status = php('echo db_one($db, "SELECT status FROM tasks WHERE id = ? AND company_id = ?", [' + str(tasks[0]['id']) + ', ' + company + '])["status"];')
expect(status == 'completed', 'Completed task is saved')
code, body, _ = admin.request('/users/admin/workspace.php?module=tasks&action=view&id=%d' % tasks[0]['id'])
expect('Created from GitHub issue #1' in body and 'simplr-demo/website' in body, 'Task shows its GitHub issue')

code, body, _ = admin.post({'action': 'auto_sync', 'link_id': acme_link, 'enabled': '0'})
expect('Automatic sync turned off for acme-labs/api' in body and 'manual sync only' in body, 'Auto-sync can be turned off per repository')
php('db_run($db, "UPDATE github_repository_links SET last_synced_at = UTC_TIMESTAMP() - INTERVAL 1 HOUR WHERE company_id = ? AND id IN (?, ?)", [' + company + ', ' + link + ', ' + acme_link + ']); echo "ok";')
admin.request(STUB + '/__test/close?issue=2')
job_env = {**os.environ, 'APP_ENV': 'local', 'MAIL_TRANSPORT': 'log', 'GITHUB_API_URL': STUB, 'GITHUB_OAUTH_URL': STUB}
run = subprocess.run(['php', 'jobs/github-sync.php', '--company=' + company], cwd=ROOT, capture_output=True, text=True, env=job_env, timeout=120)
summary = json.loads(run.stdout.strip().splitlines()[-1])
expect(summary['links'] == 1 and summary['completed'] == 1 and summary['failed'] == 0, 'Scheduled job syncs repositories with auto-sync on')
expect(task_rows()[1]['status'] == 'completed', 'Scheduled sync completes tasks whose issues closed')
stale = php('echo db_one($db, "SELECT last_synced_at < UTC_TIMESTAMP() - INTERVAL 30 MINUTE AS stale FROM github_repository_links WHERE id = ?", [' + acme_link + '])["stale"];')
expect(stale == '1', 'Scheduled job skips repositories with auto-sync off')
rerun = json.loads(subprocess.run(['php', 'jobs/github-sync.php', '--company=' + company], cwd=ROOT, capture_output=True, text=True, env=job_env, timeout=120).stdout.strip().splitlines()[-1])
expect(rerun['links'] == 0, 'Scheduled job waits before syncing the same repository again')
code, body, _ = admin.request('/users/admin/activity.php')
expect('Simplr (automatically) synced GitHub issues' in html.unescape(body) and 'matched 2 GitHub usernames to employees' in html.unescape(body), 'Activity shows automatic syncs and people matching')

# A second synthetic company cannot read or change company A's GitHub setup by guessing IDs.
def github_state():
    return php('echo json_encode([db_rows($db, "SELECT id, connection_id, auto_sync, last_synced_at FROM github_repository_links WHERE company_id = ? ORDER BY id", [' + company + ']), db_rows($db, "SELECT id, account_login FROM integration_connections WHERE company_id = ? ORDER BY id", [' + company + ']), db_rows($db, "SELECT id, github_login FROM employees WHERE company_id = ? AND github_login IS NOT NULL ORDER BY id", [' + company + ']), db_one($db, "SELECT COUNT(*) AS count FROM github_issue_tasks WHERE company_id = ?", [' + company + '])]);')


beta = Client()
code, body, _ = beta.request('/users/admin/login.php')
csrf = re.search(r'name="csrf_token" value="([a-f0-9]+)"', body).group(1)
code, body, url = beta.request('/users/admin/login_process.php', {'csrf_token': csrf, 'email': fixture['email'].replace('alpha-', 'beta-'), 'password': 'Synthetic-workspace-passphrase-2026!'})
expect('/newdashboard.php' in url, 'Second synthetic company owner signs in')
before = github_state()
for action, extra in (('sync', {'link_id': link}), ('auto_sync', {'link_id': link, 'enabled': '0'}), ('unlink', {'link_id': acme_link}), ('disconnect', {'connection_id': connection_id('simplr-demo')})):
    code, body, _ = beta.post({'action': action, **extra})
    expect(code == 404 or 'This repository link could not be found' in body, 'Other company cannot ' + action.replace('_', '-') + ' company A GitHub records')
code, body, _ = beta.post({'action': 'people', 'login[]': ['intruder'], 'employee[]': [str(ada)]})
expect('Choose employees from your workspace' in body, 'Other company cannot map company A employees')
code, body, _ = beta.post({'action': 'link', 'project_id': project, 'repository': 'simplr-demo/website'})
expect('linked to the project' not in body, 'Other company cannot link a repository to company A project')
code, body, _ = beta.request('/users/admin/github.php')
expect(code == 200 and 'simplr-demo' not in body and 'acme-labs' not in body, 'Other company GitHub page shows none of company A accounts or repositories')
expect(beta.request('/users/admin/workspace.php?module=projects&action=view&id=%d' % project)[0] == 404, 'Other company cannot open the linked project panel')
expect(github_state() == before, 'Company A GitHub links, accounts, people and synced issues are unchanged')

code, body, _ = admin.post({'action': 'disconnect', 'connection_id': connection_id('simplr-demo')})
expect(re.search(r'GitHub account @simplr-demo disconnected\. \d+ linked repositor', body) and 'Account disconnected' in body and 'Relink' in body and 'via @acme-labs' in body, 'Disconnecting one account flags only its repositories')
code, body, _ = admin.request('/users/admin/workspace.php?module=projects&action=view&id=%d' % project)
expect('The GitHub account this repository used was disconnected' in body and 'Rate limit the public API' in body, 'Other account keeps working on the project page')
code, body, _ = admin.post({'action': 'sync', 'link_id': link})
expect('was disconnected. Link the repository again' in body, 'Sync explains a repository whose account was disconnected')
code, body, _ = admin.post({'action': 'link', 'project_id': project, 'repository': 'simplr-demo/website'})
expect('GitHub could not find this repository' in body, 'Relinking fails clearly when no connected account can see it')
code, body, url = admin.request('/users/admin/github.php?code=stubcode12345&state=forged')
expect('expired or did not match' in body, 'OAuth callback rejects a forged state')
code, body, url = admin.request('/users/admin/github.php?oauth=start')
expect('GitHub account @simplr-demo connected' in body and 'GitHub sign-in' in body, 'OAuth sign-in reconnects the account through the authorization flow')
reattached = php('echo db_one($db, "SELECT connection_id FROM github_repository_links WHERE id = ?", [' + link + '])["connection_id"];')
expect(reattached == connection_id('simplr-demo'), 'Reconnecting reattaches repositories the account can see')
code, body, url = admin.request('/users/admin/github.php?oauth=start')
expect('was already connected; its access was refreshed' in body, 'Signing in with an already connected account refreshes it')
mode = php('echo db_one($db, "SELECT auth_type FROM integration_connections WHERE company_id = ? AND provider = \'github\' AND account_login = \'simplr-demo\'", [' + company + '])["auth_type"];')
expect(mode == 'oauth', 'OAuth connection is recorded as GitHub sign-in')
code, body, _ = admin.request('/users/admin/activity.php')
text = html.unescape(body)
expect('connected GitHub as @acme-labs' in text and 'disconnected GitHub account @simplr-demo' in text and 'synced GitHub issues for' in text, 'Activity explains GitHub changes per account')
code, body, _ = admin.post({'action': 'unlink', 'link_id': link})
expect('Repository unlinked' in body, 'Repository can be unlinked')
remaining = php('echo db_one($db, "SELECT COUNT(*) AS count FROM tasks WHERE id = ? AND company_id = ?", [' + str(tasks[1]['id']) + ', ' + company + '])["count"];')
expect(remaining == '1', 'Unlinking keeps tasks created from issues')

# Several syncs of one repository at once (scheduled job plus Sync clicks) create each task once.
admin.request(STUB + '/__test/reset')
created = admin.request('/api/records.php?module=projects', {'csrf_token': admin.token(), 'projectName': 'GitHub race ' + str(int(time.time())), 'startDate': '2026-10-01', 'endDate': '2026-12-31', 'status': 'ongoing'}, api=True)
race_project = json.loads(created[1])['id']
code, body, _ = admin.post({'action': 'link', 'project_id': race_project, 'repository': 'simplr-demo/website'})
race_link = php('echo db_one($db, "SELECT id FROM github_repository_links WHERE company_id = ? AND project_id = ?", [' + company + ', ' + str(race_project) + '])["id"];')
race_code = 'require "app/github.php"; $db = database(); try { echo json_encode(github_sync_issues($db, ["company_id" => ' + company + ', "id" => 0], ' + race_link + ')); } catch (GitHubUnavailable $error) { echo json_encode(["error" => $error->getMessage()]); }'
racers = [subprocess.Popen(['php', '-r', race_code], cwd=ROOT, env=job_env, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True) for _ in range(4)]
outcomes = []
for racer in racers:
    out, _ = racer.communicate(timeout=120)
    lines = out.strip().splitlines()
    outcomes.append(json.loads(lines[-1]) if lines and lines[-1].startswith('{') else {'error': 'crashed'})
mappings = php('echo db_one($db, "SELECT COUNT(*) AS count FROM github_issue_tasks WHERE link_id = ?", [' + race_link + '])["count"];')
race_tasks = php('echo db_one($db, "SELECT COUNT(*) AS count FROM tasks t JOIN projects p ON p.unique_id = t.project_id AND p.company_id = t.company_id WHERE p.id = ?", [' + str(race_project) + '])["count"];')
expect(all('error' not in outcome for outcome in outcomes), 'Concurrent syncs of one repository all finish without errors')
expect(int(mappings) == 2 and sum(outcome.get('imported', 0) for outcome in outcomes) == int(mappings) == int(race_tasks), 'Concurrent syncs create one task per issue and no duplicates')
admin.post({'action': 'unlink', 'link_id': race_link})
admin.post({'action': 'unlink', 'link_id': acme_link})
disconnect_all()
admin.request(STUB + '/__test/reset')
print(json.dumps({'checks': len(checks), 'passed': sum(c['passed'] for c in checks), 'failures': [c['name'] for c in checks if not c['passed']]}))

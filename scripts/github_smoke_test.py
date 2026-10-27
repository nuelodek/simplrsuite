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
if not re.search(r'^APP_ENV=local$', (ROOT / '.env.local').read_text(), re.M) or not BASE.startswith('http://127.0.0.1:'):
    sys.exit('Refusing to test outside a local environment.')
checks = []
TOKEN = 'ghp_stubToken0123456789abcdefABCDEF'


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
if 'Disconnect' in admin.request('/users/admin/github.php')[1]:
    admin.post({'action': 'disconnect'})
code, body, _ = admin.request('/users/admin/github.php')
expect(code == 200 and 'Not connected' in body and 'Continue with GitHub' in body and 'Connect with a token' in body, 'Owner sees both connection options')
code, body, _ = admin.post({'action': 'connect_token', 'token': 'short'})
expect('Paste the full GitHub token' in body, 'Malformed token is rejected before contacting GitHub')
code, body, _ = admin.post({'action': 'connect_token', 'token': 'ghp_' + 'x' * 36})
expect('GitHub rejected the saved access' in body, 'Token GitHub rejects is not stored')
code, body, _ = admin.post({'action': 'connect_token', 'token': TOKEN})
expect('GitHub connected as simplr-demo' in body and 'Connected' in body, 'Valid token connects the workspace')
cipher = php('echo db_one($db, "SELECT token_ciphertext FROM integration_connections WHERE company_id = ? AND provider = \'github\'", [' + company + '])["token_ciphertext"];')
expect(cipher.startswith('v1:') and TOKEN not in cipher, 'Token is encrypted at rest')
expect(TOKEN not in body, 'Token is never echoed back to the browser')
expect('simplr-demo/website' in body and 'simplr-demo/mobile-app' in body, 'Repository picker lists accessible repositories')

created = admin.request('/api/records.php?module=projects', {'csrf_token': admin.token(), 'projectName': 'GitHub sync ' + str(int(time.time())), 'startDate': '2026-10-01', 'endDate': '2026-12-31', 'status': 'ongoing'}, api=True)
project = json.loads(created[1])['id']
code, body, _ = admin.post({'action': 'link', 'project_id': project, 'repository': '../etc/passwd'})
expect('Choose a repository in owner/name form' in body, 'Malformed repository names are rejected')
code, body, _ = admin.post({'action': 'link', 'project_id': '999999999', 'repository': 'simplr-demo/website'})
expect('Choose a project from your workspace' in body, 'Projects outside the workspace cannot be linked')
code, body, _ = admin.post({'action': 'link', 'project_id': project, 'repository': 'simplr-demo/website'})
expect('Repository linked to the project' in body and 'simplr-demo/website' in body, 'Repository links to the project')

code, body, _ = admin.request('/users/admin/workspace.php?module=projects&action=view&id=%d' % project)
expect('Write onboarding guide' in body and 'Ship invoice export' in body and 'Draft' in body, 'Project page shows issues, pull requests and commits')
expect('&lt;script&gt;alert(1)&lt;/script&gt;' in body and '<script>alert(1)' not in body, 'GitHub text is escaped on the project page')
issue_list = body.split('Open issues', 1)[1].split('Pull requests', 1)[0]
expect('Add dark mode' not in issue_list, 'Pull requests are not listed as issues')

link = php('echo db_one($db, "SELECT id FROM github_repository_links WHERE company_id = ? AND project_id = ?", [' + company + ', ' + str(project) + '])["id"];')
code, body, _ = admin.post({'action': 'sync', 'link_id': link})
expect('2 new tasks added from GitHub issues and 0 tasks completed' in body, 'Sync creates one task per open issue')
tasks = json.loads(php('echo json_encode(db_rows($db, "SELECT t.id, t.title, t.priority, t.status, t.due_date FROM github_issue_tasks g JOIN tasks t ON t.id = g.task_id WHERE g.company_id = ? AND g.link_id = ? ORDER BY g.issue_number", [' + company + ', ' + link + ']));'))
expect(len(tasks) == 2 and tasks[0]['priority'] == 'high' and tasks[0]['due_date'] == '2026-12-01' and tasks[0]['status'] == 'ongoing', 'Urgent label, milestone date and assignee map onto the task')
expect(tasks[1]['priority'] == 'low' and tasks[1]['due_date'] == '2026-12-31' and tasks[1]['status'] == 'pending', 'Low label and project end date map onto the task')
code, body, _ = admin.post({'action': 'sync', 'link_id': link})
expect('Tasks are already up to date with GitHub' in body, 'Repeat sync does not duplicate tasks')
admin.request(STUB + '/__test/close?issue=1')
code, body, _ = admin.post({'action': 'sync', 'link_id': link, 'return_to': 'project', 'project_id': project})
expect('0 new tasks added from GitHub issues and 1 task completed' in body and '/workspace.php?module=projects' in _, 'Closing an issue completes its task and returns to the project')
status = php('echo db_one($db, "SELECT status FROM tasks WHERE id = ? AND company_id = ?", [' + str(tasks[0]['id']) + ', ' + company + '])["status"];')
expect(status == 'completed', 'Completed task is saved')
code, body, _ = admin.request('/users/admin/workspace.php?module=tasks&action=view&id=%d' % tasks[0]['id'])
expect('Created from GitHub issue #1' in body and 'simplr-demo/website' in body, 'Task shows its GitHub issue')
code, body, _ = admin.request('/users/admin/activity.php')
expect('connected GitHub as @simplr-demo' in body and 'synced GitHub issues for' in html.unescape(body), 'Activity explains GitHub changes')

admin.post({'action': 'disconnect'})
code, body, _ = admin.request('/users/admin/workspace.php?module=projects&action=view&id=%d' % project)
expect('GitHub is disconnected' in body, 'Project panel explains a disconnected integration')
code, body, url = admin.request('/users/admin/github.php?oauth=callback&code=stubcode12345&state=forged')
expect('expired or did not match' in body, 'OAuth callback rejects a forged state')
code, body, url = admin.request('/users/admin/github.php?oauth=start')
expect('GitHub connected as simplr-demo' in body and 'GitHub sign-in' in body, 'OAuth sign-in connects through the authorization flow')
mode = php('echo db_one($db, "SELECT auth_type FROM integration_connections WHERE company_id = ? AND provider = \'github\'", [' + company + '])["auth_type"];')
expect(mode == 'oauth', 'OAuth connection is recorded as GitHub sign-in')
code, body, _ = admin.post({'action': 'unlink', 'link_id': link})
expect('Repository unlinked' in body, 'Repository can be unlinked')
remaining = php('echo db_one($db, "SELECT COUNT(*) AS count FROM tasks WHERE id = ? AND company_id = ?", [' + str(tasks[1]['id']) + ', ' + company + '])["count"];')
expect(remaining == '1', 'Unlinking keeps tasks created from issues')
admin.post({'action': 'disconnect'})
admin.request(STUB + '/__test/reset')
print(json.dumps({'checks': len(checks), 'passed': sum(c['passed'] for c in checks), 'failures': [c['name'] for c in checks if not c['passed']]}))

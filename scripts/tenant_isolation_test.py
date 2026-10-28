"""Two-company isolation and object-level authorisation matrix.

Uses only the synthetic example.test workspaces from smoke_test.py and
consolidation_smoke_test.py, against the local server (APP_ENV=local,
MAIL_TRANSPORT=log). No external requests, payments or real email. Staff tool
grants are restored to their previous values at the end.

Covers search, the Files library, attachment download/removal, record views,
the records API (list, update, board move, archive), CSV export and formula
neutralising, alerts, queries, accounting, dashboards and redirect targets, for
another company's owner, an employee with view-only grants, a manager with no
grants and an anonymous visitor.
"""
from access_smoke_test import ROOT, BASE, Client, expect, checks, grant
from urllib.request import Request, build_opener, HTTPCookieProcessor, HTTPRedirectHandler
from urllib.parse import urlencode, quote
from urllib.error import HTTPError
from http.cookiejar import CookieJar
import json
import re
import subprocess
import uuid


class Session(Client):
    def __init__(self):
        self.base = BASE
        self.jar = CookieJar()
        self.opener = build_opener(HTTPCookieProcessor(self.jar))

    def token(self):
        status, body, _ = self.request('/users/admin/notifications.php', api=False)
        match = re.search(r'name="csrf_token" value="([a-f0-9]+)"', body)
        expect(status == 200 and match, 'CSRF token available')
        return match.group(1)

    def post(self, path, data, api=True):
        return self.request(path, {'csrf_token': self.token(), **data}, api)

    def location(self, path, data):
        class Stop(HTTPRedirectHandler):
            def redirect_request(self, *args, **kwargs):
                return None
        opener = build_opener(HTTPCookieProcessor(self.jar), Stop())
        try:
            response = opener.open(Request(self.base + path, data=urlencode({'csrf_token': self.token(), **data}).encode()), timeout=30)
        except HTTPError as error:
            response = error
        return response.status, response.headers.get('Location', '')

    def upload(self, path, fields, field_name, filename, content):
        boundary = 'simplr' + uuid.uuid4().hex
        pieces = [f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode() for key, value in fields.items()]
        pieces.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{field_name}"; filename="{filename}"\r\nContent-Type: text/csv\r\n\r\n'.encode() + content + b'\r\n')
        body = b''.join(pieces) + f'--{boundary}--\r\n'.encode()
        request = Request(self.base + path, data=body, headers={'Accept': 'application/json', 'Content-Type': 'multipart/form-data; boundary=' + boundary})
        try:
            response = self.opener.open(request, timeout=30)
        except HTTPError as error:
            response = error
        return response.status, response.read().decode(errors='replace')


def php(code):
    return subprocess.run(['php', '-r', 'require "app/database.php"; $db = database(); ' + code], cwd=ROOT, capture_output=True, text=True, check=True).stdout.strip()


def value(sql, *params):
    return php('$row = db_one($db, ' + json.dumps(sql) + ', ' + json.dumps(list(params)) + '); echo $row === null ? "" : (string)reset($row);')


def search(client, term):
    status, body, _ = client.request('/api/search.php?q=' + quote(term))
    return status, (json.loads(body)['data'] if status == 200 else [])


def denied(status, url=''):
    return status in (401, 403, 404) or '/login.php' in url


fixture = json.loads((ROOT / '.local/browser-fixture.json').read_text())
staff = json.loads((ROOT / '.local/recovery-browser-fixture.json').read_text())
assert re.fullmatch(r'alpha-\d+@example\.test', fixture['email'])
assert re.fullmatch(r'employee-\d+@example\.test', staff['email'])
company = value('SELECT company_id FROM users WHERE email = ?', fixture['email'])
owner_id = value('SELECT id FROM users WHERE email = ?', fixture['email'])
employee_id = value('SELECT id FROM employees WHERE company_id = ? AND email = ?', company, staff['email'])
manager_id = value('SELECT id FROM managers WHERE company_id = ? AND manager_email = ?', company, staff['email'].replace('employee-', 'manager-'))
saved = json.loads(php('echo json_encode(db_rows($db, "SELECT staff_role, staff_id, module_key, access_level FROM staff_tool_access WHERE company_id = ?", [' + company + ']));'))
probe = 'Isolation probe ' + uuid.uuid4().hex[:10]

admin, beta, employee, manager, anon = [Session() for _ in range(5)]
granted = False
try:
    admin.login('admin', fixture['email'], fixture['password'])
    beta.login('admin', fixture['email'].replace('alpha-', 'beta-'), 'Synthetic-workspace-passphrase-2026!')
    employee.login('employee', staff['email'], staff['password'], staff['workspace'])
    manager.login('manager', staff['email'].replace('employee-', 'manager-'), staff['password'], staff['workspace'])
    granted = True
    grant(admin, 'employee', employee_id, {'tasks': 'view', 'projects': 'view'})
    grant(admin, 'manager', manager_id, {})

    # Company A fixtures, created through the same HTTP paths customers use.
    status, body, _ = admin.post('/api/records.php?module=projects', {'projectName': probe + ' project', 'startDate': '2026-10-01', 'endDate': '2026-12-31', 'status': 'ongoing'})
    expect(status == 201, 'Owner creates a probe project')
    project_unique = value('SELECT unique_id FROM projects WHERE id = ? AND company_id = ?', json.loads(body)['id'], company)
    status, body, _ = admin.post('/api/records.php?module=tasks', {'title': probe, 'project_id': project_unique, 'description': '=SUM(1,2) ' + probe, 'due_date': '2026-12-01', 'priority': 'medium', 'status': 'pending', 'assignee': employee_id})
    expect(status == 201, 'Owner creates a probe task')
    task = str(json.loads(body)['id'])
    task_file_name = 'probe-task-' + uuid.uuid4().hex[:8] + '.csv'
    status, body = admin.upload('/api/attachments.php', {'csrf_token': admin.token(), 'module': 'tasks', 'record_id': task}, 'attachment', task_file_name, b'name,value\nprobe,1\n')
    expect(status == 201, 'Owner attaches a file to the task')
    task_file = value('SELECT id FROM record_attachments WHERE company_id = ? AND original_name = ?', company, task_file_name)
    doc_file_name = 'probe-doc-' + uuid.uuid4().hex[:8] + '.csv'
    status, body = admin.upload('/users/admin/files.php', {'csrf_token': admin.token(), 'title': probe + ' document', 'category': 'General', 'notes': ''}, 'file', doc_file_name, b'name,value\ndocument,2\n')
    doc_file = value('SELECT id FROM record_attachments WHERE company_id = ? AND original_name = ?', company, doc_file_name)
    document = value('SELECT record_id FROM record_attachments WHERE id = ?', doc_file)
    expect(status == 200 and doc_file and document, 'Owner uploads a document in the Files library')
    alert = php('require "app/notifications.php"; echo notify($db, ' + company + ', "admin", ' + owner_id + ', "system", ' + json.dumps(probe + ' alert') + ', "Synthetic isolation alert.", "/users/admin/notifications.php", false, ' + json.dumps('isolation:' + probe) + ');')
    expect(alert.isdigit(), 'Owner receives a probe alert')
    status, body, url = admin.post('/users/admin/query.php', {'action': 'issue', 'subjects[]': 'employee:' + employee_id, 'title': probe + ' query', 'message': '<p>Synthetic isolation query.</p>', 'respond_minutes': '10080'}, api=False)
    query = value('SELECT id FROM task_queries WHERE company_id = ? AND title = ?', company, probe + ' query')
    expect(status == 200 and query, 'Owner issues a probe query to the employee')
    # The chart goes in company B: the consolidation browser test expects company A to start without one.
    beta_company = value('SELECT company_id FROM users WHERE email = ?', fixture['email'].replace('alpha-', 'beta-'))
    beta.post('/users/admin/accounting.php', {'action': 'create_chart'}, api=False)
    accounts = json.loads(php('echo json_encode(array_column(db_rows($db, "SELECT id FROM accounting_accounts WHERE company_id = ? ORDER BY id LIMIT 2", [' + beta_company + ']), "id"));'))
    expect(len(accounts) == 2, 'Company B has a chart of accounts')

    # Positive controls: the owner sees their own data, so the denials below mean something.
    expect(any(item['label'] == probe for item in search(admin, probe)[1]), 'Owner search finds own task')
    expect(task_file_name in admin.request('/users/admin/files.php?q=probe-task', api=False)[1], 'Owner Files library lists own attachment')
    status, body, _ = admin.request('/users/admin/reports.php?export=tasks', api=False)
    expect(status == 200 and probe in body, 'Owner export includes own task')
    expect("\"'=SUM(1,2) " + probe + '"' in body or "'=SUM(1,2) " + probe in body, 'CSV export neutralises spreadsheet formulas')
    expect(admin.request('/api/attachments.php?id=' + task_file)[0] == 200, 'Owner downloads own attachment')

    # Company B owner: guessed IDs and filters never reach company A.
    expect(search(beta, probe) == (200, []), 'Other company search returns nothing for company A terms')
    expect(task_file_name not in beta.request('/users/admin/files.php?q=probe', api=False)[1], 'Other company Files library hides company A files')
    for file_id in (task_file, doc_file):
        expect(beta.request('/api/attachments.php?id=' + file_id)[0] == 404, 'Other company cannot download attachment ' + file_id)
    for key, record in (('tasks', task), ('documents', document), ('projects', json.loads(admin.request('/api/records.php?module=projects')[1])['data'][0]['id'])):
        expect(beta.request(f'/users/admin/workspace.php?module={key}&action=view&id={record}', api=False)[0] == 404, 'Other company cannot open company A ' + key + ' record')
    status, body, _ = beta.request('/api/records.php?module=tasks')
    expect(status == 200 and probe not in body, 'Other company task list excludes company A')
    expect(beta.post('/api/records.php?module=tasks', {'action': 'move', 'id': task, 'stage': 'completed', 'version': 'x'})[0] == 404, 'Other company cannot move company A task on the board')
    expect(beta.post('/api/records.php?module=tasks', {'action': 'update', 'id': task, 'title': 'Hijacked', 'project_id': project_unique, 'due_date': '2026-12-01', 'priority': 'high', 'status': 'completed', 'version': 'x'})[0] in (404, 409, 422), 'Other company cannot update company A task')
    expect(beta.post('/api/records.php?module=tasks', {'action': 'archive', 'id': task, 'version': 'x'})[0] in (404, 409), 'Other company cannot archive company A task')
    expect(value('SELECT CONCAT(title, "|", status, "|", COALESCE(archived_at, "")) FROM tasks WHERE id = ?', task) == probe + '|pending|', 'Company A task is unchanged')
    expect(beta.post('/api/attachments.php', {'action': 'remove', 'module': 'tasks', 'record_id': task, 'id': task_file})[0] == 404, 'Other company cannot remove company A attachment')
    expect(value('SELECT id FROM record_attachments WHERE id = ?', task_file) == task_file, 'Company A attachment still exists')
    for key in ('tasks', 'documents', 'projects'):
        status, body, _ = beta.request('/users/admin/reports.php?export=' + key, api=False)
        expect(status == 200 and probe not in body, 'Other company ' + key + ' export excludes company A')
    expect(beta.post('/users/admin/notifications.php', {'action': 'open', 'id': alert})[0] == 404, 'Other company cannot open company A alert')
    expect(value('SELECT COALESCE(read_at, "") FROM notifications WHERE id = ?', alert) == '', 'Company A alert stays unread')
    expect(beta.request('/users/admin/query.php?id=' + query, api=False)[0] == 404, 'Other company cannot read company A query')
    beta.post('/users/admin/query.php', {'action': 'decide', 'id': query, 'outcome': 'closed', 'note': 'x'})
    beta.post('/users/admin/query.php', {'action': 'respond', 'id': query, 'response': '<p>x</p>'})
    expect(value('SELECT CONCAT(status, "|", COALESCE(response, "")) FROM task_queries WHERE id = ?', query) == 'awaiting_response|', 'Company A query is unchanged by the other company')
    entries = value('SELECT COUNT(*) FROM accounting_journal_entries')
    status, body, _ = admin.post('/users/admin/accounting.php', {'action': 'post_entry', 'entry_date': '2026-10-27', 'description': probe, 'reference': '', 'account_id[]': accounts, 'debit[]': ['10.00', ''], 'credit[]': ['', '10.00']})
    expect(status == 422 and value('SELECT COUNT(*) FROM accounting_journal_entries') == entries, 'A company cannot post journal entries to another company accounts')
    for page in ('/users/admin/newdashboard.php', '/users/admin/activity.php', '/users/admin/calendar.php', '/users/admin/queries.php?show=all', '/users/admin/notifications.php'):
        expect(probe not in beta.request(page, api=False)[1], 'Other company does not see company A on ' + page.split('?')[0])

    # Employee in company A with view-only Tasks and Projects grants.
    results = search(employee, probe)[1]
    expect(any(item['label'] == probe for item in results), 'Employee search finds tasks they may view')
    expect(not any('module=documents' in item['url'] for item in results), 'Employee search skips tools they were not given')
    status, body, _ = employee.request('/users/admin/files.php', api=False)
    expect(status == 200 and task_file_name in body and doc_file_name not in body, 'Employee Files library lists only granted tools')
    expect(employee.request('/api/attachments.php?id=' + task_file)[0] == 200, 'Employee downloads a task attachment they may view')
    expect(employee.request('/api/attachments.php?id=' + doc_file)[0] == 403, 'Employee cannot download a document from an ungranted tool')
    expect(employee.post('/api/records.php?module=tasks', {'action': 'move', 'id': task, 'stage': 'completed', 'version': 'x'})[0] == 403, 'View-only employee cannot move tasks')
    expect(employee.post('/api/attachments.php', {'action': 'remove', 'module': 'tasks', 'record_id': task, 'id': task_file})[0] == 403, 'View-only employee cannot remove attachments')
    status, body, _ = employee.request('/users/admin/reports.php?export=documents', api=False)
    expect(status == 403 and doc_file_name not in body, 'Employee cannot export an ungranted tool')
    expect(denied(*employee.request('/users/admin/accounting.php', api=False)[::2]), 'Employee cannot open accounting')
    expect(employee.post('/users/admin/github.php', {'action': 'people', 'login[]': 'someone', 'employee[]': employee_id})[0] == 403, 'Employee cannot change GitHub people matches')
    expect(employee.post('/users/admin/notifications.php', {'action': 'open', 'id': alert})[0] == 404, 'Employee cannot open the owner alert')
    status, body, _ = employee.request('/users/admin/query.php?id=' + query, api=False)
    expect(status == 200 and probe + ' query' in body, 'Employee reads the query addressed to them')

    # Manager in company A with no grants.
    expect(search(manager, probe) == (200, []), 'Manager without grants finds nothing')
    status, body, _ = manager.request('/users/admin/files.php', api=False)
    expect(status == 200 and task_file_name not in body and doc_file_name not in body, 'Manager without grants sees no files')
    expect(manager.request('/api/attachments.php?id=' + task_file)[0] == 403, 'Manager without grants cannot download task files')
    expect(manager.request('/api/records.php?module=tasks')[0] == 403, 'Manager without grants cannot list tasks')
    expect(manager.request('/users/admin/reports.php?export=tasks', api=False)[0] == 403, 'Manager without grants cannot export tasks')

    # Anonymous visitors.
    expect(anon.request('/api/search.php?q=' + quote(probe))[0] == 401, 'Search requires sign-in')
    expect(anon.request('/api/attachments.php?id=' + task_file)[0] == 401, 'Downloads require sign-in')
    expect(anon.request('/api/records.php?module=tasks')[0] == 401, 'Records API requires sign-in')
    for page in ('/users/admin/files.php', '/users/admin/notifications.php', '/users/admin/query.php?id=' + query, '/users/admin/github.php'):
        expect('/login.php' in anon.request(page, api=False)[2], 'Sign-in required for ' + page.split('?')[0])

    # Redirect targets stay on this site.
    for target in ('//evil.example/', '/\\evil.example/', '/\\/evil.example/', 'https://evil.example/', '/\tevil.example/'):
        status, location = admin.location('/users/admin/notifications.php', {'action': 'read_all', 'return_to': target})
        expect(status == 303 and location == '/users/admin/notifications.php', 'Alert return path ' + target + ' falls back to the alerts page')
    status, location = admin.location('/users/admin/notifications.php', {'action': 'read_all', 'return_to': '/users/admin/queries.php'})
    expect(location == '/users/admin/queries.php', 'Alert return path keeps a local page')

    # Remove the probe files so repeated runs do not grow storage.
    for file_id, key, record in ((task_file, 'tasks', task), (doc_file, 'documents', document)):
        expect(admin.post('/api/attachments.php', {'action': 'remove', 'module': key, 'record_id': record, 'id': file_id})[0] == 200, 'Owner removes probe file ' + file_id)
finally:
    try:
        for role, staff_id in (('employee', employee_id), ('manager', manager_id)) if granted else ():
            grant(admin, role, staff_id, {row['module_key']: row['access_level'] for row in saved if row['staff_role'] == role and str(row['staff_id']) == staff_id})
    finally:
        print(json.dumps({'checks': len(checks), 'passed': sum(c['passed'] for c in checks), 'failures': [c['name'] for c in checks if not c['passed']]}))

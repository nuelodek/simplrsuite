"""Integration checks using synthetic data and local mail only.

Run against the local PHP server after installing the schema. Each run creates
two isolated test companies, retained for inspection. Never uses real email.
"""
from pathlib import Path
from urllib.request import Request, build_opener, HTTPCookieProcessor
from urllib.parse import urlencode, urlsplit
from urllib.error import HTTPError
from http.cookiejar import CookieJar
import json
import re
import time
import sys

ROOT = Path(__file__).resolve().parent.parent
BASE = 'http://127.0.0.1:8000'
local = (ROOT / '.env').read_text()
if not re.search(r'^APP_ENV=local$', local, re.M) or not re.search(r'^MAIL_TRANSPORT=log$', local, re.M):
    sys.exit('Refusing to test without APP_ENV=local and MAIL_TRANSPORT=log in .env.')
checks = []


def expect(condition, label):
    checks.append({'name': label, 'passed': bool(condition)})
    if not condition:
        raise AssertionError(label)


class Client:
    def __init__(self):
        self.cookies = CookieJar()
        self.opener = build_opener(HTTPCookieProcessor(self.cookies))

    def request(self, path, data=None, api=False):
        headers = {'Accept': 'application/json' if api else 'text/html'}
        body = urlencode(data, doseq=True).encode() if data is not None else None
        request = Request(BASE + path, data=body, headers=headers)
        try:
            response = self.opener.open(request, timeout=30)
        except HTTPError as error:
            response = error
        return response.status, response.read().decode(), response.url

    def csrf(self, path):
        code, body, _ = self.request(path)
        expect(code == 200, 'Form loads: ' + path.split('?')[0])
        token = re.search(r'name="csrf_token" value="([a-f0-9]+)"', body)
        expect(token is not None, 'Form provides a CSRF token')
        return token.group(1), body


def latest_mail(email, subject):
    for path in sorted((ROOT / 'storage/mail').glob('*.json'), key=lambda p: p.stat().st_mtime, reverse=True):
        message = json.loads(path.read_text())
        if message['to'] == email and message['subject'] == subject:
            return message['text']
    raise AssertionError('Expected local test email was not written')


def mail_path(email, subject):
    link = re.search(r'http://127\.0\.0\.1:8000/\S+', latest_mail(email, subject)).group(0)
    parsed = urlsplit(link)
    return parsed.path + '?' + parsed.query


def signup(client, email, company):
    password = 'Synthetic-workspace-passphrase-2026!'
    token, _ = client.csrf('/users/admin/signup.php')
    code, body, url = client.request('/users/admin/congrats.php', {'csrf_token': token, 'company_name': company, 'industry': 'Technology', 'email': email, 'password': password, 'confirm_password': password, 'status': 'Verified', 'company_id': '1'})
    expect(code == 200 and '/verifyaccount.php' in url, 'Signup requires verification and ignores client status/company ID')
    token, _ = client.csrf('/users/admin/login.php')
    _, _, url = client.request('/users/admin/login_process.php', {'csrf_token': token, 'email': email, 'password': password})
    expect('/verifyaccount.php' in url, 'Unverified account cannot enter workspace')
    path = mail_path(email, 'Verify your Simplr workspace')
    token, _ = client.csrf(path)
    code, body, url = client.request(path, {'csrf_token': token})
    expect(code == 200 and 'Email verified.' in body and '/login.php' in url, 'Email verification completes')
    token, _ = client.csrf('/users/admin/login.php')
    before = {cookie.name: cookie.value for cookie in client.cookies}.get('PHPSESSID')
    code, body, url = client.request('/users/admin/login_process.php', {'csrf_token': token, 'email': email, 'password': password})
    after = {cookie.name: cookie.value for cookie in client.cookies}.get('PHPSESSID')
    expect(code == 200 and '/newdashboard.php' in url and company in body, 'Verified login reaches live dashboard')
    expect(before != after, 'Successful login rotates session ID')
    return password


def add(client, key, values):
    token, _ = client.csrf('/users/admin/workspace.php?module=' + key + '&action=new')
    status, body, _ = client.request('/api/records.php?module=' + key, {**values, 'csrf_token': token, 'company_id': '1'}, True)
    result = json.loads(body)
    expect(status == 201 and result.get('ok'), 'Create ' + key + ': ' + str(result.get('error', 'ok')))
    return result['id']


run = str(int(time.time()))
alpha, beta, anonymous = Client(), Client(), Client()
try:
    code, body, _ = anonymous.request('/')
    expect(code == 200 and 'Less juggling.' in body, 'Public website loads')
    for path in ['/public/scripts/suite.js','/public/styles/suite.css','/public/images/brand.svg','/scripts/projdashboard.js']:
        expect(anonymous.request(path)[0] == 200, 'Static asset loads: ' + path)
    for path in ['/.env', '/.env.local', '/database/schema.sql', '/storage/mail/test.json', '/sql_queries/db.php', '/superadmin/gitpayloads.php', '/composer.json']:
        expect(anonymous.request(path)[0] in (403, 404), 'Private path blocked: ' + path)
    expect(anonymous.request('/api/records.php?module=projects', api=True)[0] == 401, 'Anonymous API access is rejected')
    expect(anonymous.request('/users/admin/congrats.php', {'email': 'nobody@example.test'})[0] == 419, 'Account mutations require CSRF')
    alpha_email = 'alpha-' + run + '@example.test'
    beta_email = 'beta-' + run + '@example.test'
    password = signup(alpha, alpha_email, 'Test Workspace Alpha')
    signup(beta, beta_email, 'Test Workspace Beta')
    department = add(alpha, 'departments', {'department_name': 'Operations', 'location': 'Lagos'})
    manager = add(alpha, 'managers', {'manager_name': 'Test Manager', 'manager_email': 'manager-' + run + '@example.test', 'department_id': department})
    employee = add(alpha, 'employees', {'first_name': 'Test', 'last_name': 'Employee', 'email': 'employee-' + run + '@example.test', 'hire_date': '2026-10-22', 'department_id': department, 'manager_id': manager})
    project = add(alpha, 'projects', {'projectName': 'Test launch <script>alert(1)</script>', 'projectDescription': 'Synthetic test record', 'startDate': '2026-10-22', 'endDate': '2026-11-22', 'status': 'ongoing', 'department': department, 'projectManager': manager})
    _, form, _ = alpha.request('/users/admin/workspace.php?module=tasks&action=new')
    project_code = re.search(r'<option value="(pro_[a-f0-9]+)"', form).group(1)
    task_id = add(alpha, 'tasks', {'title': 'Review launch checklist', 'project_id': project_code, 'due_date': '2026-11-20', 'priority': 'high', 'status': 'pending', 'assignee': employee})
    add(alpha, 'teams', {'team_name': 'Launch team', 'project_id': project, 'team_leader_id': employee, 'team_members[]': [employee], 'team_status': 'active'})
    add(alpha, 'leads', {'lead_name': 'Example lead', 'lead_email': 'lead-' + run + '@example.test', 'lead_source': 'Referral', 'status': 'new'})
    customer = add(alpha, 'customers', {'first_name': 'Example', 'last_name': 'Customer', 'business_name': 'Synthetic company', 'customer_email': 'customer-' + run + '@example.test'})
    add(alpha, 'contacts', {'contact_name': 'Example contact', 'contact_email': 'contact-' + run + '@example.test'})
    _, form, _ = alpha.request('/users/admin/workspace.php?module=accounts&action=new')
    customer_code = re.search(r'<option value="(cus_[a-f0-9]+)"', form).group(1)
    add(alpha, 'accounts', {'account_owner': 'Example account', 'account_email': 'account-' + run + '@example.test', 'customer_id': customer_code, 'status': 'active'})
    _, form, _ = alpha.request('/users/admin/workspace.php?module=deals&action=new')
    account_code = re.search(r'<option value="(acc_[a-f0-9]+)"', form).group(1)
    add(alpha, 'deals', {'deal_with': 'Example opportunity', 'account_id': account_code, 'amount': '500.00', 'stage': 'proposal'})
    add(alpha, 'invoices', {'customer_id': customer, 'invoice_date': '2026-10-22', 'due_date': '2026-11-22', 'amount': '500.00', 'status': 'Unpaid'})
    add(alpha, 'expenses', {'description': 'Synthetic stationery expense', 'date': '2026-10-22', 'amount': '45.25', 'category': 'Office'})
    add(alpha, 'ledger', {'description': 'Synthetic income record', 'date': '2026-10-22', 'amount': '200.00', 'type': 'income', 'category': 'Services'})
    add(alpha, 'inventory', {'name': 'Test item', 'quantity': '8', 'unit_price': '20.50', 'category': 'Supplies'})
    add(alpha, 'applications', {'applicant_name': 'Test applicant', 'email': 'applicant@example.test', 'position_applied_for': 'Test role', 'status': 'new'})
    add(alpha, 'incidents', {'description': 'Synthetic test incident', 'severity': 'low', 'status': 'open'})
    add(alpha, 'compliance', {'regulation': 'Synthetic review requirement', 'status': 'in review'})
    add(alpha, 'products', {'product_name': 'Test product', 'stage': 'testing', 'start_date': '2026-10-22', 'end_date': '2026-12-01', 'status': 'ongoing'})
    add(alpha, 'assets', {'asset_name': 'Test laptop', 'purchase_date': '2026-10-22', 'value': '100.00'})
    add(alpha, 'objectives', {'title': 'Synthetic objective', 'target_date': '2026-12-01', 'status': 'ongoing', 'measure': 'Complete test plan'})
    code, body, _ = alpha.request('/users/admin/workspace.php?module=projects')
    expect('&lt;script&gt;' in body and '<script>alert(1)</script>' not in body, 'Stored record names are HTML escaped')
    code, body, _ = beta.request('/api/records.php?module=projects', api=True)
    expect(code == 200 and json.loads(body)['total'] == 0, 'Second company cannot list the first company’s records')
    expect(beta.request('/users/admin/workspace.php?module=projects&action=edit&id=' + str(project))[0] == 404, 'Cross-company edit is rejected')
    token, _ = beta.csrf('/users/admin/workspace.php?module=tasks&action=new')
    code, body, _ = beta.request('/api/records.php?module=tasks', {'csrf_token': token, 'title': 'Cross-company task', 'project_id': project_code, 'due_date': '2026-11-20', 'priority': 'high', 'status': 'pending'}, True)
    expect(code == 422, 'Cross-company project assignment is rejected')
    token, _ = alpha.csrf('/users/admin/workspace.php?module=expenses&action=new')
    code, body, _ = alpha.request('/api/records.php?module=expenses', {'csrf_token': token, 'description': 'Invalid', 'date': '2026-02-30', 'amount': '-20.001', 'category': 'Test'}, True)
    expect(code == 422 and 'date' in json.loads(body)['fields'] and 'amount' in json.loads(body)['fields'], 'Invalid dates and amounts are rejected')
    expect(alpha.request('/api/records.php?module=expenses', {'description': 'No token'}, True)[0] == 419, 'Record API requires CSRF')
    token, form = alpha.csrf('/users/admin/workspace.php?module=departments&action=edit&id=' + str(department))
    version = re.search(r'name="version" value="([a-f0-9]+)"', form).group(1)
    edit_path = '/users/admin/workspace.php?module=departments&action=edit&id=' + str(department)
    values = {'csrf_token': token, 'version': version, 'department_name': 'Updated Operations', 'location': 'Lagos'}
    code, body, _ = alpha.request(edit_path, values)
    expect(code == 200 and 'Department updated.' in body and 'Updated Operations' in body, 'Record update persists')
    expect(alpha.request(edit_path, values)[0] == 409, 'Stale edit cannot overwrite a newer record')
    code, body, _ = alpha.request('/users/admin/workspace.php?module=departments&q=' + run)
    expect(code == 200 and 'No matching records.' in body, 'Search shows a clear empty result')
    for key in ['projects','tasks','departments','managers','employees','teams','leads','customers','contacts','accounts','deals','invoices','expenses','ledger','inventory','applications','incidents','compliance','products','assets','objectives']:
        code, body, _ = alpha.request('/users/admin/workspace.php?module=' + key)
        expect(code == 200 and 'Fatal error' not in body, 'List page loads: ' + key)
    for page in ['newdashboard.php','modules.php','integrations.php']:
        expect(alpha.request('/users/admin/' + page)[0] == 200, 'Workspace page loads: ' + page)
    for path in ['/toolkits/acct_payroll/list_expenses.php','/toolkits/project_mgt/allprojects.php','/users/admin/viewemployees.php']:
        code, body, url = anonymous.request(path)
        expect(code == 200 and '/users/admin/login.php' in url, 'Legacy reader requires sign-in: ' + path)
    token, _ = beta.csrf('/users/admin/workspace.php?module=departments&action=new')
    code, body, _ = beta.request('/users/admin/createdepartment.php', {'csrf_token': token, 'company_id': '1', 'department_name': 'Legacy adapter test'}, True)
    expect(code == 201 and json.loads(body).get('ok'), 'Legacy create adapter uses validated record service')
    code, body, _ = beta.request('/api/records.php?module=departments', api=True)
    expect(json.loads(body)['data'][0]['department_name'] == 'Legacy adapter test', 'Legacy create ignores client company ID')
    expect(anonymous.request('/users/admin/createproject.php', {'company_id': '1'}, True)[0] == 419, 'Legacy writes require CSRF')
    for role, staff_id, staff_email in [('employee', employee, 'employee-' + run + '@example.test'), ('manager', manager, 'manager-' + run + '@example.test')]:
        token, _ = alpha.csrf('/users/admin/workspace.php?module=employees')
        code, body, _ = alpha.request('/users/admin/invite.php', {'csrf_token': token, 'role': role, 'staff_id': staff_id})
        expect(code == 200 and 'The mail server accepted the invitation.' in body, role + ' invitation writes local test mail')
        path = mail_path(staff_email, 'Your Simplr workspace invitation')
        colleague = Client()
        token, _ = colleague.csrf(path)
        staff_password = 'Synthetic-staff-passphrase-2026!'
        code, body, url = colleague.request(path, {'csrf_token': token, 'password': staff_password, 'confirm_password': staff_password})
        expect(code == 200 and '/login.php' in url and 'Your password is ready.' in body, role + ' can accept invitation')
        expect(colleague.request(path)[0] == 410, role + ' invitation cannot be reused')
        login_path = '/users/' + ('employees' if role == 'employee' else 'managers') + '/login.php'
        token, _ = colleague.csrf(login_path)
        code, body, url = colleague.request(login_path, {'csrf_token': token, 'email': staff_email, 'password': staff_password})
        expect(code == 200 and '/workspace.php' in url and 'Review launch checklist' in body, role + ' sees their assigned work')
        expect(colleague.request('/api/records.php?module=employees', api=True)[0] == 403, role + ' cannot access the staff directory API')
        token, _ = colleague.csrf('/users/admin/logout.php')
        code, body, url = colleague.request('/users/employees/task-status.php', {'csrf_token': token, 'task_id': task_id, 'status': 'completed'})
        expect(code == (200 if role == 'employee' else 404), role + ' task status permissions are enforced')
    token, _ = beta.csrf('/users/admin/workspace.php?module=employees')
    expect(beta.request('/users/admin/invite.php', {'csrf_token': token, 'role': 'employee', 'staff_id': employee})[0] == 404, 'Cross-company invitations are rejected')
    recovery = Client()
    token, _ = recovery.csrf('/users/admin/resetpassword.php')
    code, body, _ = recovery.request('/users/admin/resetpassword.php', {'csrf_token': token, 'email': alpha_email})
    expect('If there is an eligible workspace' in body, 'Password recovery returns a generic response')
    path = mail_path(alpha_email, 'Reset your Simplr password')
    token, _ = recovery.csrf(path)
    new_password = 'Another-synthetic-passphrase-2026!'
    code, body, url = recovery.request(path, {'csrf_token': token, 'new_password': new_password, 'confirm_password': new_password})
    expect('Password updated.' in body and '/login.php' in url, 'Password reset completes with the emailed token')
    expect(alpha.request('/api/records.php?module=projects', api=True)[0] == 401, 'Password change invalidates previous admin sessions')
    token, _ = recovery.csrf(path)
    code, body, _ = recovery.request(path, {'csrf_token': token, 'new_password': new_password, 'confirm_password': new_password})
    expect('expired or has already been used' in body, 'Password reset tokens are single-use')
    token, _ = recovery.csrf('/users/admin/login.php')
    code, body, url = recovery.request('/users/admin/login_process.php', {'csrf_token': token, 'email': alpha_email, 'password': new_password})
    expect(code == 200 and '/newdashboard.php' in url, 'New password signs in successfully')
    token, _ = recovery.csrf('/users/admin/logout.php')
    code, body, url = recovery.request('/users/admin/logout.php', {'csrf_token': token})
    expect('/login.php' in url and recovery.request('/api/records.php?module=projects', api=True)[0] == 401, 'Sign out clears access')
    (ROOT / '.local/browser-fixture.json').write_text(json.dumps({'email': alpha_email, 'password': new_password, 'company': 'Test Workspace Alpha'}))
finally:
    target = ROOT / 'work/test-results'
    target.mkdir(parents=True, exist_ok=True)
    report = {'run': run, 'checks': len(checks), 'passed': sum(x['passed'] for x in checks), 'results': checks}
    (target / ('smoke-' + run + '.json')).write_text(json.dumps(report, indent=2))
    print(json.dumps({'checks': report['checks'], 'passed': report['passed'], 'failures': [x['name'] for x in checks if not x['passed']]}))

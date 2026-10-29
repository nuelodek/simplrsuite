"""Local consolidation regression: core + talent + role/tenant-bound staff recovery."""
import talent_smoke_test  # Runs the existing core and talent journeys once.
from smoke_test import Client, ROOT, expect, mail_path, latest_mail, checks, run, beta, add, project
import hashlib
import json
import re
import subprocess

old_password = 'Synthetic-staff-passphrase-2026!'
new_password = 'Recovered-synthetic-staff-passphrase-2026!'
employee_email = 'employee-' + run + '@example.test'
manager_email = 'manager-' + run + '@example.test'
workspace = re.search(r'workspace code: (\d+)', latest_mail(employee_email, 'Your Simplr workspace invitation'), re.I).group(1)

def login_staff(client, role, email, password, company):
    path = '/users/' + ('employees' if role == 'employee' else 'managers') + '/login.php'
    csrf, _ = client.csrf(path)
    return client.request(path, {'csrf_token':csrf,'email':email,'password':password,'workspace':company})

def request_reset(client, role, email, company):
    csrf, _ = client.csrf('/users/forgetpassword.php?role=' + (role if role in ['employee','manager'] else 'employee'))
    return client.request('/users/send_reset_email.php', {'csrf_token':csrf,'role':role,'email':email,'workspace':company}, True)

def invite(client, role, staff_id, email):
    module = 'employees' if role == 'employee' else 'managers'
    list_path = '/users/admin/workspace.php?module=' + module
    csrf, _ = client.csrf(list_path)
    expect(client.request('/users/admin/invite.php', {'csrf_token':csrf,'role':role,'staff_id':staff_id})[0] == 200, 'Consolidation fixture invitation succeeds')
    code, body, _ = client.request(list_path)
    expect(code == 200 and 'awaiting acceptance' in body and 'Resend' in body, 'Staff directory shows sent invitation awaiting acceptance with resend action')
    previous_invitation = mail_path(email, 'Your Simplr workspace invitation')
    expect(client.request('/users/admin/invite.php', {'csrf_token':csrf,'role':role,'staff_id':staff_id})[0] == 200, 'Consolidation fixture invitation can be resent')
    expect(client.request(previous_invitation)[0] == 410, 'Resending invalidates the previous invitation link')
    invited = Client(); path = mail_path(email, 'Your Simplr workspace invitation')
    company = re.search(r'workspace code: (\d+)', latest_mail(email,'Your Simplr workspace invitation'), re.I).group(1)
    csrf, _ = invited.csrf(path)
    code, body, _ = invited.request(path, {'csrf_token':csrf,'password':old_password,'confirm_password':old_password})
    expect(code == 200 and 'Your password is ready.' in body, 'Consolidation fixture account is activated')
    return company

def expire_test_token(email, path):
    token = path.split('token=')[1]
    code = '''require 'app/database.php';
if (config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log') exit(2);
$data = json_decode(stream_get_contents(STDIN), true);
if (!preg_match('/^(employee|manager)-[0-9]+@example\\.test$/D', $data['email'])) exit(3);
$db = database();
$statement = db_run($db, 'UPDATE staff_password_resets SET expires_at = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE email = ? AND token_hash = ?', [$data['email'],$data['hash']]);
echo $statement->affected_rows;'''
    result = subprocess.run([r'C:\Program Files\Ampps\php\php.exe','-r',code],input=json.dumps({'email':email,'hash':hashlib.sha256(token.encode()).hexdigest()}),capture_output=True,text=True,cwd=ROOT)
    expect(result.returncode == 0 and result.stdout == '1', 'Expiry fixture updates only the synthetic reset token')

try:
    anon = Client()
    code, body, _ = anon.request('/users/forgetpassword.php')
    expect(code == 200 and all(label in body for label in ['Business owner','Employee','Manager','Talent']), 'Shared recovery exposes all four account journeys')
    expect(anon.request('/users/send_reset_email.php', {'role':'employee','email':employee_email,'workspace':workspace}, True)[0] == 419, 'Shared recovery requires CSRF')
    expect(anon.request('/users/send_reset_email.php',api=True)[0] == 405, 'Shared recovery rejects GET mutation')
    expect(request_reset(anon,'employee',employee_email,'')[0] == 422, 'Staff recovery requires explicit workspace')
    expect(request_reset(anon,'admin',employee_email,workspace)[0] == 422, 'Staff recovery cannot select an admin table')
    department_b = add(beta,'departments',{'department_name':'Recovery test team'})
    manager_b = add(beta,'managers',{'manager_name':'Recovery cross-role manager','manager_email':employee_email,'department_id':department_b})
    employee_b = add(beta,'employees',{'first_name':'Recovery','last_name':'Cross-company','email':employee_email,'hire_date':'2026-10-23','department_id':department_b,'manager_id':manager_b})
    workspace_b = invite(beta,'employee',employee_b,employee_email)
    invite(beta,'manager',manager_b,employee_email)
    other_employee, other_manager = Client(), Client()
    expect('/workspace.php' in login_staff(other_employee,'employee',employee_email,old_password,workspace_b)[2], 'Same email can sign into second company as employee')
    expect('/workspace.php' in login_staff(other_manager,'manager',employee_email,old_password,workspace_b)[2], 'Same email can sign into distinct manager account')
    for role,email in [('employee',employee_email),('manager',manager_email)]:
        current = Client()
        expect('/workspace.php' in login_staff(current,role,email,old_password,workspace)[2], role + ' initial sign-in works')
        code, response, _ = request_reset(anon,role,email,workspace)
        expect(code == 200 and json.loads(response)['success'], role + ' recovery request succeeds')
        reset = mail_path(email,'Reset your Simplr ' + role + ' password')
        code, missing, _ = request_reset(anon,role,'unknown-' + run + '@example.test',workspace)
        expect(code == 200 and json.loads(missing) == json.loads(response), role + ' recovery does not enumerate accounts')
        expect('/workspace.php' in current.request('/users/employees/workspace.php')[2], role + ' requesting recovery does not invalidate session')
        request_reset(anon,role,email,workspace)
        sibling_token = mail_path(email,'Reset your Simplr ' + role + ' password')
        csrf, _ = anon.csrf(reset)
        expect(anon.request(reset,{'csrf_token':csrf,'new_password':'short','confirm_password':'short'})[0] == 422, role + ' reset enforces password length')
        code, body, url = anon.request(reset,{'csrf_token':csrf,'new_password':new_password,'confirm_password':new_password,'role':'admin','workspace':workspace_b,'auth_id':'1'})
        expect(code == 200 and 'Password updated.' in body and '/login.php' in url, role + ' resets token-bound identity despite forged scope fields')
        expect('/login.php' in current.request('/users/employees/workspace.php')[2], role + ' old session is invalidated')
        expect('/login.php' in login_staff(Client(),role,email,old_password,workspace)[2], role + ' old password is rejected')
        expect('/workspace.php' in login_staff(Client(),role,email,new_password,workspace)[2], role + ' new password works')
        csrf, _ = anon.csrf(reset)
        expect(anon.request(reset,{'csrf_token':csrf,'new_password':old_password,'confirm_password':old_password})[0] == 422, role + ' reset link is single-use')
        csrf, _ = anon.csrf(sibling_token)
        expect(anon.request(sibling_token,{'csrf_token':csrf,'new_password':old_password,'confirm_password':old_password})[0] == 422, role + ' reset revokes other outstanding links')
        request_reset(anon,role,email,workspace)
        expired = mail_path(email,'Reset your Simplr ' + role + ' password')
        expire_test_token(email,expired)
        csrf, _ = anon.csrf(expired)
        expect(anon.request(expired,{'csrf_token':csrf,'new_password':old_password,'confirm_password':old_password})[0] == 422, role + ' expired link cannot change password')
    expect('/workspace.php' in other_employee.request('/users/employees/workspace.php')[2], 'Reset in first company preserves second-company employee session')
    expect('/workspace.php' in other_manager.request('/users/employees/workspace.php')[2], 'Employee reset preserves same-email manager session')
    expect('/workspace.php' in login_staff(Client(),'employee',employee_email,old_password,workspace_b)[2], 'Other company retains its original employee password')
    expect('/workspace.php' in login_staff(Client(),'manager',employee_email,old_password,workspace_b)[2], 'Other role retains its original password')
    fixture = json.loads((ROOT/'.local/browser-fixture.json').read_text())
    admin = Client(); csrf,_ = admin.csrf('/users/admin/login.php')
    admin.request('/users/admin/login_process.php',{'csrf_token':csrf,'email':fixture['email'],'password':fixture['password']})
    code, body, _ = admin.request('/users/developers/documentation.php')
    expect(code == 200 and '/api/records.php?module=projects' in body and '/api/get-projects' not in body, 'Consolidated docs describe implemented endpoints')
    expect(Client().request('/users/developers/documentation.php',api=True)[0] == 401, 'API documentation workspace requires admin sign-in')
    for asset in ['/public/scripts/toast.js','/public/styles/toast.css']:
        expect(anon.request(asset)[0] == 200, 'Consolidated notification asset loads: ' + asset)
    fixture['project_id'] = project
    (ROOT/'.local/browser-fixture.json').write_text(json.dumps(fixture))
    (ROOT/'.local/recovery-browser-fixture.json').write_text(json.dumps({'email':employee_email,'password':new_password,'workspace':workspace,'role':'employee'}))
finally:
    report={'checks':len(checks),'passed':sum(c['passed'] for c in checks),'failures':[c['name'] for c in checks if not c['passed']]}
    (ROOT/'work/test-results'/('consolidation-smoke-'+run+'.json')).write_text(json.dumps({'summary':report,'checks':checks},indent=2))
    print(json.dumps(report))

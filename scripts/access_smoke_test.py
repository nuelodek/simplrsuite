"""Verify local staff grants, tenant boundaries and the HTTP route manifest.

Reuses only synthetic example.test fixtures from consolidation_smoke_test.py.
No external requests, email, payments, fixture deletion or credential output.
"""
from pathlib import Path
from urllib.request import Request, build_opener, HTTPCookieProcessor
from urllib.parse import urlencode
from urllib.error import HTTPError
from http.cookiejar import CookieJar
import json
import os
import re
import subprocess
import uuid

ROOT = Path(__file__).resolve().parent.parent
BASE = os.environ.get('SIMPLR_TEST_BASE_URL', 'http://127.0.0.1:8000').rstrip('/')
checks = []
local = (ROOT / '.env').read_text()
assert re.search(r'^APP_ENV=local$',local,re.M) and re.search(r'^MAIL_TRANSPORT=log$',local,re.M)

def expect(condition, label):
    checks.append({'name':label,'passed':bool(condition)})
    if not condition: raise AssertionError(label)

class Client:
    def __init__(self, base=BASE):
        self.base = base
        self.opener = build_opener(HTTPCookieProcessor(CookieJar()))
    def request(self,path,data=None,api=True):
        body = urlencode(data,doseq=True).encode() if data is not None else None
        try: r = self.opener.open(Request(self.base+path,data=body,headers={'Accept':'application/json' if api else 'text/html'}),timeout=30)
        except HTTPError as e: r = e
        return r.status,r.read().decode(),r.url
    def form(self,path):
        status,body,_ = self.request(path,api=False)
        expect(status == 200,'Form loads: '+path)
        token = re.search(r'name="csrf_token" value="([a-f0-9]+)"',body)
        expect(token is not None,'Form has CSRF')
        return token.group(1),body
    def login(self,role,email,password,workspace=''):
        folder = {'admin':'admin','employee':'employees','manager':'managers'}[role]
        path = '/users/'+folder+'/login.php'
        token,_ = self.form(path)
        result = self.request('/users/admin/login_process.php' if role == 'admin' else path,{'csrf_token':token,'email':email,'password':password,'workspace':workspace},False)
        expect(result[0] == 200 and ('newdashboard.php' if role == 'admin' else 'workspace.php') in result[2],role+' fixture login')

def rows(client,key):
    status,body,_ = client.request('/api/records.php?module='+key)
    expect(status == 200,'API readable: '+key)
    return json.loads(body)['data']

def grant(admin,role,staff_id,levels):
    path = f'/users/admin/access.php?role={role}&id={staff_id}'
    token,body = admin.form(path)
    version = re.search(r'name="version" value="([a-f0-9]+)"',body).group(1)
    values = {'csrf_token':token,'version':version,**{'access['+k+']':v for k,v in levels.items()}}
    status,body,_ = admin.request(path,values,False)
    expect(status == 200 and 'Tool access updated.' in body,'Save '+role+' grants')
    return path,values

if __name__ == '__main__':
    try:
        fixture = json.loads((ROOT/'.local/browser-fixture.json').read_text())
        staff = json.loads((ROOT/'.local/recovery-browser-fixture.json').read_text())
        assert re.fullmatch(r'alpha-\d+@example\.test',fixture['email'])
        assert re.fullmatch(r'employee-\d+@example\.test',staff['email'])
        admin,beta,employee,manager,anon = [Client() for _ in range(5)]
        admin.login('admin',fixture['email'],fixture['password'])
        beta.login('admin',fixture['email'].replace('alpha-','beta-'),'Synthetic-workspace-passphrase-2026!')
        employee.login('employee',staff['email'],staff['password'],staff['workspace'])
        manager.login('manager',staff['email'].replace('employee-','manager-'),staff['password'],staff['workspace'])
        employee_id = next(x['id'] for x in rows(admin,'employees') if x['email'] == staff['email'])
        manager_id = next(x['id'] for x in rows(admin,'managers') if x['manager_email'] == staff['email'].replace('employee-','manager-'))
        grant(admin,'employee',employee_id,{})
        grant(admin,'manager',manager_id,{})
        expect(employee.request('/api/records.php?module=projects')[0] == 403,'Unassigned employee cannot read company projects')
        expect(manager.request('/api/records.php?module=projects')[0] == 403,'Unassigned manager cannot read all company projects')
        expect('Review launch checklist' in employee.request('/users/employees/workspace.php',api=False)[1],'Assigned tasks remain available without grants')
        expect(employee.request('/users/admin/access.php')[0] == 401,'Staff cannot administer grants')
        expect(beta.request(f'/users/admin/access.php?role=employee&id={employee_id}')[0] == 404,'Owner cannot grant another company staff access')
        expect(admin.request('/users/admin/access.php?role=admin&id=1')[0] == 422,'Grant role is allowlisted')
        expect(admin.request(f'/users/admin/access.php?role=employee&id={employee_id}',{})[0] == 419,'Grant mutations require CSRF')
        keys = ['projects','tasks','leads','customers','contacts','accounts','deals','invoices','expenses','ledger','inventory','applications','incidents','compliance','products','assets','objectives']
        path,stale = grant(admin,'employee',employee_id,dict.fromkeys(keys,'view'))
        expect(admin.request(path,stale)[0] == 409,'Stale grant form cannot overwrite newer access')
        for key in keys:
            expect(employee.request('/api/records.php?module='+key)[0] == 200,'View grant reads '+key)
            expect(employee.request('/api/records.php?module='+key,{})[0] == 403,'View grant cannot write '+key)
        status,body,_ = employee.request('/users/admin/workspace.php?module=leads',api=False)
        expect(status == 200 and 'View only' in body and 'action=edit' not in body and 'action=new' not in body,'Read-only screen has no edit or add actions')
        expect(employee.request('/users/admin/workspace.php?module=leads&action=edit&id=1')[0] == 403,'Read-only user cannot open edit form')
        expect(manager.request('/api/records.php?module=leads')[0] == 403,'Employee grant does not grant manager access')
        token,_ = admin.form(path)
        expect(admin.request(path,{'csrf_token':token,'access[employees]':'edit'})[0] == 422,'Staff cannot be granted directory administration')
        grant(admin,'employee',employee_id,{'leads':'edit','expenses':'view'})
        token,_ = employee.form('/users/admin/workspace.php?module=leads&action=new')
        payload = {'csrf_token':token,'lead_name':'Staff access regression','lead_email':'access-'+uuid.uuid4().hex+'@example.test','lead_source':'Referral','status':'new','company_id':'1'}
        code,body,_ = employee.request('/api/records.php?module=leads',payload)
        expect(code == 201,'Editor creates a tenant-bound CRM record')
        lead_id = json.loads(body)['id']
        lead = next(x for x in rows(employee,'leads') if x['lead_id'] == lead_id)
        expect(not any(x['lead_id'] == lead_id for x in rows(beta,'leads')),'Other company cannot see staff-created record')
        update = {**payload,'action':'update','id':lead_id,'version':lead['version'],'status':'qualified'}
        expect(employee.request('/api/records.php?module=leads',update)[0] == 200,'Editor updates CRM record with version')
        expect(employee.request('/api/records.php?module=leads',update)[0] == 409,'Stale API edit is rejected')
        lead = next(x for x in rows(employee,'leads') if x['lead_id'] == lead_id)
        legacy = {'csrf_token':token,'lead_id':lead_id,'version':lead['version'],'new_status':'contacted'}
        expect(employee.request('/toolkits/sales_mgt/updateLeadStatus.php',legacy)[0] == 200,'Legacy lead mutation honors current permissions')
        beta_token,_ = beta.form('/users/admin/workspace.php?module=leads&action=new')
        expect(beta.request('/toolkits/sales_mgt/updateLeadStatus.php',{**legacy,'csrf_token':beta_token})[0] == 404,'Legacy lead mutation cannot cross companies')
        grant(admin,'employee',employee_id,{})
        expect(employee.request('/api/records.php?module=leads')[0] == 403,'Revocation applies to existing read session')
        expect(employee.request('/api/records.php?module=leads',update)[0] == 403,'Revocation applies to already open edit form')
        expect(employee.request('/toolkits/sales_mgt/updateLeadStatus.php',legacy)[0] == 403,'Legacy write cannot bypass revocation')
        grant(admin,'manager',manager_id,{'projects':'view'})
        expect(manager.request('/api/records.php?module=projects')[0] == 200,'Manager receives explicit view grant')
        expect(manager.request('/api/records.php?module=projects',{})[0] == 403,'Manager view grant cannot mutate')
        grant(admin,'manager',manager_id,{})
        employee_row = next(x for x in rows(admin,'employees') if x['id'] == employee_id)
        task = rows(admin,'tasks')[0]
        task_form, _ = admin.form('/users/admin/workspace.php?module=tasks&action=new')
        task_payload = {'csrf_token':task_form,'title':'Task-query scope regression','project_id':task['project_id'],'due_date':'2026-11-20','priority':'high','status':'pending','assignee':employee_id}
        code, body, _ = admin.request('/api/records.php?module=tasks',task_payload)
        expect(code == 201,'Create synthetic task for query-scope checks')
        task_id = json.loads(body)['id']
        query_path = f'/users/admin/query.php?action=new&task={task_id}&subject=employee:{employee_id}'
        expect(manager.request(query_path,api=False)[0] == 403,'Manager without task access cannot view task facts for a query')
        grant(admin,'manager',manager_id,{'tasks':'view'})
        code, form, _ = manager.request(query_path,api=False)
        expect(code == 200 and 'About a task' in form,'Manager with task access can query an assigned direct report about their task')
        employee_form, _ = admin.form('/users/admin/workspace.php?module=employees&action=new')
        other_email = 'query-scope-' + uuid.uuid4().hex + '@example.test'
        other_payload = {'csrf_token':employee_form,'first_name':'Query','last_name':'Scope','email':other_email,'hire_date':'2026-10-27','job_title':'Synthetic test','department_id':employee_row['department_id'],'manager_id':manager_id}
        code, body, _ = admin.request('/api/records.php?module=employees',other_payload)
        expect(code == 201,'Create second synthetic direct report for query-scope checks')
        other_employee_id = json.loads(body)['id']
        csrf = re.search(r'name="csrf_token" value="([a-f0-9]+)"',form).group(1)
        mismatched_query = {'csrf_token':csrf,'action':'issue','task_id':task_id,'subjects[]':'employee:'+str(other_employee_id),'title':'Task assignee scope check','message':'Please explain this task.','respond_minutes':'10'}
        expect(manager.request('/users/admin/query.php',mismatched_query)[0] == 403,'Manager cannot attach another direct report to a task assigned to someone else')
        grant(admin,'manager',manager_id,{})
        teams = rows(admin,'teams')
        for route in ['get_team_data.php','getteamdata.php']:
            expect(beta.request('/users/admin/'+route+'?team_id='+str(teams[0]['id']))[0] == 404,'Legacy team lookup isolates company: '+route)
            expect(anon.request('/users/admin/'+route+'?team_id='+str(teams[0]['id']))[0] == 401,'Legacy team lookup requires login: '+route)
        task = rows(admin,'tasks')[0]
        token,_ = admin.form('/users/admin/workspace.php?module=tasks')
        values = {'csrf_token':token,'taskId':task['id'],'version':task['version'],'updateTitle':'Review launch checklist'}
        expect(beta.request('/users/admin/updatetask.php',{**values,'csrf_token':beta_token})[0] == 404,'Legacy task update isolates company')
        expect(admin.request('/users/admin/updatetask.php',{'taskId':task['id']})[0] == 419,'Legacy task update requires CSRF')
        expect(admin.request('/users/admin/updatetask.php',values)[0] == 200,'Legacy task update works for owner with version')
        blocked = ['/users/admin/revoketools.php','/users/admin/processtools.php','/users/admin/createbankaccount.php','/users/admin/createdollarcard.php','/users/admin/marquettawebhook.php','/toolkits/project_mgt/update.php','/toolkits/acct_payroll/recordpayment.php','/users/talents/update_inventory.php','/users/talents/unitbankdemo.php','/superadmin/gitpayloads.php','/users/employees/db.php','/users/admin/session.php','/scripts/smoke_test.py','/app/routes.php','/.env','/storage/mail/test.json','/work/overnight-handoff.md','/public-entry.php','/users/admin/login.php/extra','/users/admin/login.php%00','/%252eenv','/users%5cadmin%5cprocesstools.php']
        bases = [BASE] if os.environ.get('SIMPLR_TEST_BASE_URL') else [BASE,'http://127.0.0.1:8001']
        for base in bases:
            client = Client(base)
            expect(client.request('/health.php')[0] == 200,'Healthy server: '+base)
            for page in ['/','/landing/business/','/landing/talents/','/landing/work/','/users/admin/login.php','/public/styles/suite.css']:
                expect(client.request(page,api=False)[0] == 200,'Public route works on '+base+': '+page)
            for path in blocked:
                expect(client.request(path)[0] in [400,403,404],'Unreviewed/private route blocked on '+base+': '+path)
                expect(client.request(path,{'company_id':'1'})[0] in [400,403,404],'POST cannot bypass route boundary on '+base+': '+path)
        # Leave a synthetic, useful view-only assignment for browser verification.
        grant(admin,'employee',employee_id,{'leads':'view','projects':'view'})
        (ROOT/'.local/access-browser-fixture.json').write_text(json.dumps({'employee_id':employee_id,'manager_id':manager_id,'lead_id':lead_id}))
    finally:
        report = {'passed':sum(x['passed'] for x in checks),'total':len(checks),'checks':checks}
        (ROOT/'work/test-results/access-smoke.json').write_text(json.dumps(report,indent=2))
        print(json.dumps({'passed':report['passed'],'total':report['total'],'failures':[x['name'] for x in checks if not x['passed']]}))

"""Tenant- and author-bound internal help workflow, using synthetic accounts only."""
from access_smoke_test import ROOT, Client, expect, checks
import json
import re

try:
    fixture=json.loads((ROOT/'.local/browser-fixture.json').read_text())
    staff=json.loads((ROOT/'.local/recovery-browser-fixture.json').read_text())
    admin,beta,employee,manager=Client(),Client(),Client(),Client()
    admin.login('admin',fixture['email'],fixture['password'])
    beta.login('admin',fixture['email'].replace('alpha-','beta-'),'Synthetic-workspace-passphrase-2026!')
    employee.login('employee',staff['email'],staff['password'],staff['workspace'])
    manager.login('manager',staff['email'].replace('employee-','manager-'),staff['password'],staff['workspace'])
    token,_=employee.form('/users/help.php')
    expect(employee.request('/users/help.php',{})[0]==419,'Help requires CSRF')
    expect(employee.request('/users/help.php',{'csrf_token':token,'subject':'','body':'','category':'forged'})[0]==422,'Help validates fields')
    status,body,url=employee.request('/users/help.php',{'csrf_token':token,'subject':'Need project access <script>bad()</script>','body':'Please review my tools.','category':'Account access','company_id':'1','author_id':'1','author_role':'admin'},False)
    expect(status==200 and '?id=' in url,'Employee creates own tenant-bound request')
    path='/users/help.php?id='+url.split('?id=')[1]
    expect('&lt;script&gt;' in body and '<script>bad()' not in body,'Help escapes submitted text')
    expect(manager.request(path)[0]==404,'Another staff member cannot read request')
    expect(beta.request(path)[0]==404,'Another company cannot read request')
    expect('Please review my tools.' in admin.request(path,api=False)[1],'Workspace owner can read team request')
    token,body=employee.form(path)
    version=re.search(r'name="version" type="hidden" value="(\d+)"',body).group(1)
    expect(employee.request(path,{'csrf_token':token,'version':version,'body':'Forged status','state':'resolved'})[0]==403,'Staff cannot resolve by forging owner fields')
    payload={'csrf_token':token,'version':version,'body':'Additional detail.'}
    expect(employee.request(path,payload,False)[0]==200,'Author adds a reply')
    expect(employee.request(path,payload)[0]==409,'Repeated/stale reply is rejected')
    token,body=admin.form(path)
    version=re.search(r'name="version" type="hidden" value="(\d+)"',body).group(1)
    expect(admin.request(path,{'csrf_token':token,'version':version,'body':'Access reviewed.','state':'resolved'},False)[0]==200,'Owner replies and resolves request')
    status,body,_=employee.request(path,api=False)
    expect(status==200 and 'Access reviewed.' in body and '>resolved<' in body,'Author sees owner response and status')
    token,_=manager.form('/users/help.php')
    expect(manager.request(path,{'csrf_token':token,'version':'3','body':'Intruder'})[0]==404,'Another staff member cannot reply')
    expect(employee.request('/users/admin/activity.php')[0]==401,'Audit activity remains owner-only')
    status,body,_=admin.request('/users/admin/activity.php',api=False)
    expect(status==200 and 'help' in body,'Help changes appear in owner activity log')
    (ROOT/'.local/help-browser-fixture.json').write_text(json.dumps({'path':path}))
finally:
    report={'passed':sum(x['passed'] for x in checks),'total':len(checks),'checks':checks}
    (ROOT/'work/test-results/help-smoke.json').write_text(json.dumps(report,indent=2))
    print(json.dumps({'passed':report['passed'],'total':report['total'],'failures':[x['name'] for x in checks if not x['passed']]}))

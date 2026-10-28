"""Local settings, event/calendar, reports and old-record compatibility checks."""
from access_smoke_test import ROOT, Client, expect, rows, grant, checks
import csv
import io
import json
import re
import subprocess
import uuid

def db_fixture(sql,params):
    # The caller passes only IDs returned by the authenticated synthetic fixture.
    code = """require 'app/database.php';
if (config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log') exit(2);
$v=json_decode(stream_get_contents(STDIN),true); $db=database();
$owner=db_one($db,'SELECT company_id FROM users WHERE email = ?',[$v['email']]);
if (!$owner || !preg_match('/^alpha-[0-9]+@example\\.test$/D',$v['email'])) exit(3);
if ((string)end($v['params']) !== (string)$owner['company_id']) exit(4);
// Simulate old schemas that had no project FK, on this test connection only.
if (str_starts_with($v['sql'],'UPDATE tasks SET project_id')) $db->query('SET FOREIGN_KEY_CHECKS=0');
db_run($db,$v['sql'],$v['params']); echo 'ok';"""
    r=subprocess.run([r'C:\Program Files\Ampps\php\php.exe','-r',code],input=json.dumps({'email':fixture['email'],'sql':sql,'params':params}),text=True,capture_output=True,cwd=ROOT)
    expect(r.returncode == 0 and r.stdout == 'ok','Synthetic compatibility fixture updated safely')

def settings_post(client,currency='NGN',timezone='Africa/Lagos'):
    token,body=client.form('/users/admin/settings.php')
    version=re.search(r'name="version" value="([a-f0-9]+)"',body).group(1)
    return {'csrf_token':token,'version':version,'company_name':'Test Workspace Alpha','currency':currency,'timezone':timezone,'query_response_minutes':'4320','warning_expiry_months':'12'}

try:
    fixture=json.loads((ROOT/'.local/browser-fixture.json').read_text())
    staff=json.loads((ROOT/'.local/recovery-browser-fixture.json').read_text())
    admin,beta,employee=Client(),Client(),Client()
    admin.login('admin',fixture['email'],fixture['password'])
    beta.login('admin',fixture['email'].replace('alpha-','beta-'),'Synthetic-workspace-passphrase-2026!')
    employee.login('employee',staff['email'],staff['password'],staff['workspace'])
    employee_id=next(x['id'] for x in rows(admin,'employees') if x['email']==staff['email'])
    grant(admin,'employee',employee_id,{'projects':'view','leads':'view'})
    expect(employee.request('/users/admin/settings.php')[0]==401,'Settings are owner-only')
    expect(admin.request('/users/admin/settings.php',{})[0]==419,'Settings require CSRF')
    expect(admin.request('/users/admin/settings.php',settings_post(admin,'USD','UTC'),False)[0]==200,'Prepare distinct settings for conflict test')
    data=settings_post(admin)
    expect(admin.request('/users/admin/settings.php',data,False)[0]==200,'Owner saves settings')
    expect(admin.request('/users/admin/settings.php',{**data,'currency':'USD'})[0]==409,'Settings reject stale versions')
    expect(admin.request('/users/admin/settings.php',settings_post(admin,'BAD','Mars/Phobos'))[0]==422,'Settings validate currency and timezone')
    expect('Amounts are recorded in NGN' in admin.request('/users/admin/workspace.php?module=expenses',api=False)[1],'Financial forms show selected currency')
    expect('value="Africa/Lagos" selected' in admin.request('/users/admin/workspace.php?module=events&action=new',api=False)[1],'New event uses workspace timezone')
    token,_=admin.form('/users/admin/workspace.php?module=events&action=new')
    event={'csrf_token':token,'event_name':'Launch planning <script>bad()</script>','start_datetime':'2026-10-26T10:00','end_datetime':'2026-10-26T11:00','timezone':'Africa/Lagos','event_type':'online','meet_link':'https://example.test/meeting','agenda':'Plan, review; ship\nBEGIN:VEVENT','event_description':'Synthetic event','organizer':'Test team'}
    for updates in [{'end_datetime':'2026-10-26T09:00'},{'meet_link':'javascript:alert(1)'},{'meet_link':'https://user:pass@example.test/'},{'timezone':'Invalid/Zone'},{'start_datetime':'2026-03-08T02:30','end_datetime':'2026-03-08T04:00','timezone':'America/New_York'}]:
        expect(admin.request('/api/records.php?module=events',{**event,**updates})[0]==422,'Invalid event rejected: '+','.join(updates))
    code,body,_=admin.request('/api/records.php?module=events',event)
    expect(code==201,'Create event with validated date, link and timezone')
    event_id=json.loads(body)['id']
    status,body,_=admin.request('/users/admin/calendar.php?month=2026-10',api=False)
    expect(status==200 and 'Launch planning &lt;script&gt;' in body and '<script>bad()' not in body,'Calendar escapes event content')
    code,ics,_=admin.request('/users/admin/calendar.php?download='+str(event_id))
    expect(code==200 and 'DTSTART:20261026T090000Z' in ics and 'DTEND:20261026T100000Z' in ics,'Calendar download converts event timezone to UTC')
    expect(ics.count('\r\nBEGIN:VEVENT\r\n')==1 and 'Plan\\, review\\; ship\\nBEGIN:VEVENT' in ics,'Calendar content cannot inject another event')
    expect(beta.request('/users/admin/calendar.php?download='+str(event_id))[0]==404,'Calendar download cannot cross companies')
    expect(employee.request('/users/admin/calendar.php?download='+str(event_id))[0]==403,'Calendar download requires grant')
    expect(admin.request('/users/admin/calendar.php?month=2026-99')[0]==422,'Calendar validates month')
    grant(admin,'employee',employee_id,{'events':'view','leads':'view','projects':'view'})
    expect(employee.request('/users/admin/calendar.php?download='+str(event_id))[0]==200,'Assigned staff can download events')
    expect(employee.request('/api/records.php?module=events',event)[0]==403,'Calendar view grant cannot edit events')
    expect(admin.request('/users/admin/settings.php',settings_post(admin,timezone='UTC'),False)[0]==200,'Owner changes default timezone')
    expect('DTSTART:20261026T090000Z' in admin.request('/users/admin/calendar.php?download='+str(event_id))[1],'Changing default preserves existing event timezone')
    db_fixture('UPDATE events SET timezone = NULL WHERE event_id = ? AND company_id = ?',[event_id,staff['workspace']])
    expect(admin.request('/users/admin/calendar.php?download='+str(event_id))[0]==409,'Legacy event needs explicit timezone before export')
    legacy_event=next(x for x in rows(admin,'events') if x['event_id']==event_id)
    expect(admin.request('/api/records.php?module=events',{**event,'action':'update','id':event_id,'version':legacy_event['version']})[0]==200,'Legacy event can be reviewed and saved')
    lead_token,_=admin.form('/users/admin/workspace.php?module=leads&action=new')
    code,body,_=admin.request('/api/records.php?module=leads',{'csrf_token':lead_token,'lead_name':'=HYPERLINK("https://example.test","test")','lead_email':'formula-'+uuid.uuid4().hex+'@example.test','lead_source':'Referral','status':'new'})
    expect(code==201,'Create synthetic formula-like record')
    status,body,_=employee.request('/users/admin/reports.php?export=leads')
    data=list(csv.DictReader(io.StringIO(body.lstrip('\ufeff'))))
    expect(status==200 and any(x['lead_name'].startswith("'=HYPERLINK") for x in data),'CSV neutralizes spreadsheet formula injection')
    expect(employee.request('/users/admin/reports.php?export=employees')[0]==403,'Exports enforce module access')
    _,body,_=beta.request('/users/admin/reports.php?export=leads')
    expect('HYPERLINK' not in body,'CSV export isolates company')
    expect('record_currency' in admin.request('/users/admin/reports.php?export=expenses')[1] and 'NGN' in admin.request('/users/admin/reports.php?export=expenses')[1],'Finance export includes currency')
    expect(employee.request('/users/admin/reports.php',api=False)[0]==200,'Staff report overview loads')
    task=rows(admin,'tasks')[0]
    project=rows(admin,'projects')[0]
    try:
        db_fixture('UPDATE tasks SET project_id = ? WHERE id = ? AND company_id = ?',[str(project['id']),task['id'],staff['workspace']])
        code,body,_=employee.request('/users/employees/workspace.php',api=False)
        expect(code==200 and project['projectName'].replace('<','&lt;').replace('>','&gt;') in body,'Employee sees project with historical numeric task reference')
        _,form=admin.form('/users/admin/workspace.php?module=tasks&action=edit&id='+str(task['id']))
        expect(re.search(r'value="pro_[a-f0-9]+" selected',form) is not None,'Editing old task resolves its numeric project reference')
    finally:
        db_fixture('UPDATE tasks SET project_id = ? WHERE id = ? AND company_id = ?',[task['project_id'],task['id'],staff['workspace']])
    grant(admin,'employee',employee_id,{'events':'view','projects':'view','leads':'view'})
    (ROOT/'.local/operations-browser-fixture.json').write_text(json.dumps({'event_id':event_id,'employee_id':employee_id}))
finally:
    report={'passed':sum(x['passed'] for x in checks),'total':len(checks),'checks':checks}
    (ROOT/'work/test-results/operations-smoke.json').write_text(json.dumps(report,indent=2))
    print(json.dumps({'passed':report['passed'],'total':report['total'],'failures':[x['name'] for x in checks if not x['passed']]}))

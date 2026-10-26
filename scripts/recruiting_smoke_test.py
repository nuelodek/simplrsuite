"""Local recruiting journey with synthetic accounts and inert PDF files only."""
from access_smoke_test import ROOT, Client, expect, checks, grant
from urllib.request import Request
from urllib.error import HTTPError
import json, re, subprocess, uuid

PHP = 'C:/Program Files/Ampps/php/php.exe'

def hidden(body,name):
    match=re.search(r'name="'+name+r'" value="([^"]*)"',body)
    assert match,name
    return match.group(1)

def multipart(client,path,fields,filename,content):
    boundary='simplr'+uuid.uuid4().hex
    chunks=[f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n'.encode() for k,v in fields.items()]
    chunks.append(f'--{boundary}\r\nContent-Disposition: form-data; name="resume"; filename="{filename}"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode()+content+b'\r\n')
    data=b''.join(chunks)+f'--{boundary}--\r\n'.encode()
    try:r=client.opener.open(Request(client.base+path,data=data,headers={'Accept':'text/html','Content-Type':'multipart/form-data; boundary='+boundary}),timeout=30)
    except HTTPError as e:r=e
    return r.status,r.read().decode(),r.url

def download(client,id):
    try:r=client.opener.open(Request(client.base+'/api/attachments.php?id='+str(id),headers={'Accept':'application/json'}),timeout=30)
    except HTTPError as e:r=e
    return r.status,r.read(),r.headers

def login_talent(client,fixture):
    token,_=client.form('/users/talents/talentlogin.php')
    code,body,url=client.request('/users/talents/talentlogin.php',{'csrf_token':token,'email':fixture['email'],'password':fixture['password']},False)
    expect(code==200 and 'talentpool.php' in url,'Verified synthetic talent signs in')

def job_row(admin,id):
    code,body,_=admin.request('/api/records.php?module=jobs&q=Recruiting-check')
    expect(code==200,'Employer reads jobs')
    return next(x for x in json.loads(body)['data'] if x['id']==id)

try:
    source=r"""<?php
require 'app/database.php';
if(config('APP_ENV')!=='local'||config('MAIL_TRANSPORT')!=='log'||config('DB_DATABASE')!=='simplr_dev'||config('DB_HOST')!=='127.0.0.1'||config('DB_PORT')!=='3306')exit(2);
$db=database();$out=[];
for($i=0;$i<2;$i++){
 $tag=bin2hex(random_bytes(8));$email='recruiting-'.$tag.'@example.test';$pass='Synthetic-recruiting-'.$tag.'!';
 db_run($db,"INSERT INTO talentprofile (full_name,industry,email,password,talent_id,status) VALUES (?,?,?,?,?,'Verified')",['Recruiting Check '.$i,'Design',$email,password_hash($pass,PASSWORD_DEFAULT),'tal_'.$tag]);$id=(int)$db->insert_id;
 db_run($db,"INSERT INTO talent_portfolios (account_id,headline,location,experience,skills,bio,portfolio_url,availability,visibility) VALUES (?,'Product designer','Lagos','Five years','Design & research','Build useful products','https://example.test/portfolio','Available','private')",[$id]);
 $out[]=['email'=>$email,'password'=>$pass,'id'=>$id];
}
echo json_encode($out);
"""
    seeded=subprocess.run([PHP],input=source,text=True,capture_output=True,cwd=ROOT,check=True)
    talent_fixtures=json.loads(seeded.stdout)
    fixture=json.loads((ROOT/'.local/browser-fixture.json').read_text());staff=json.loads((ROOT/'.local/recovery-browser-fixture.json').read_text());access=json.loads((ROOT/'.local/access-browser-fixture.json').read_text())
    assert re.fullmatch(r'alpha-\d+@example\.test',fixture['email'])
    admin,beta,talent,other,employee,anon=[Client() for _ in range(6)]
    admin.login('admin',fixture['email'],fixture['password']);beta.login('admin',fixture['email'].replace('alpha-','beta-'),'Synthetic-workspace-passphrase-2026!')
    login_talent(talent,talent_fixtures[0]);login_talent(other,talent_fixtures[1]);employee.login('employee',staff['email'],staff['password'],staff['workspace'])
    token,_=admin.form('/users/admin/workspace.php?module=jobs&action=new')
    title='Recruiting-check '+uuid.uuid4().hex[:8]
    values={'csrf_token':token,'title':title,'location':'Lagos','workplace':'hybrid','employment_type':'full-time','description':'Design useful products. <script>alert(1)</script>','requirements':'Portfolio and thoughtful collaboration.','closing_date':'','timezone':'Africa/Lagos','status':'draft','company_id':'1','public_id':'forged'}
    expect(admin.request('/api/records.php?module=jobs',{**values,'csrf_token':'bad'})[0]==419,'Job posting requires CSRF')
    for wrong in [{'closing_date':'not-a-date'},{'timezone':'Not/AZone'},{'workplace':'unknown'},{'status':'published','closing_date':'2000-01-01'}]:
        expect(admin.request('/api/records.php?module=jobs',{**values,**wrong})[0]==422,'Invalid job field rejected: '+next(iter(wrong)))
    code,body,_=admin.request('/api/records.php?module=jobs',values);expect(code==201,'Employer creates private draft');job_id=json.loads(body)['id'];job=job_row(admin,job_id)
    public='/jobs/?job='+job['public_id'];apply='/users/talents/apply.php?job='+job['public_id']
    expect(re.fullmatch('[a-f0-9]{32}',job['public_id']) is not None,'Public job identifier is server generated')
    expect(anon.request(public)[0]==404 and talent.request(apply)[0]==404,'Draft cannot be viewed or applied to')
    update={**values,'action':'update','id':job_id,'version':job['version'],'status':'published','closing_date':'2099-12-31'}
    expect(admin.request('/api/records.php?module=jobs',update)[0]==200,'Employer publishes role')
    expect(admin.request('/api/records.php?module=jobs',update)[0]==409,'Stale job edit rejected')
    job=job_row(admin,job_id);code,body,_=anon.request(public,api=False)
    expect(code==200 and title in body and '&lt;script&gt;' in body and '<script>alert(1)</script>' not in body,'Public role displays escaped content')
    expect(talent_fixtures[0]['email'] not in body and fixture['email'] not in body,'Public job does not expose account emails')
    expect(title in anon.request('/jobs/?q='+title.replace(' ','+'),api=False)[1],'Published role appears in searchable jobs board')
    talent_csrf,talent_body=talent.form('/users/talents/talentpool.php')
    profile={'csrf_token':talent_csrf,'version':hidden(talent_body,'version'),'headline':'Recruiting check profile','visibility':'listed'}
    expect(talent.request('/users/talents/talentpool.php',profile)[0]==200,'Talent opts into recruiter directory')
    directory='/users/admin/talent-directory.php?q=Recruiting+Check+0'
    admin_csrf,directory_body=admin.form(directory)
    expect('Recruiting Check 0' in directory_body and talent_fixtures[0]['email'] not in directory_body,'Recruiter can discover listed talent without seeing private email')
    expect(admin.request(directory,{'action':'shortlist','talent_id':talent_fixtures[0]['id']})[0]==419,'Shortlisting requires CSRF')
    code,body,_=admin.request(directory,{'csrf_token':admin_csrf,'action':'shortlist','talent_id':talent_fixtures[0]['id'],'q':'Recruiting Check 0'})
    expect(code==200 and 'Remove from shortlist' in body,'Recruiter can save talent to a company shortlist')
    beta_directory='/users/admin/talent-directory.php?shortlist=1&q=Recruiting+Check+0'
    expect('Your shortlist is empty.' in beta.request(beta_directory,api=False)[1],'Talent shortlists are isolated by company')
    invite={'csrf_token':admin_csrf,'action':'invite','talent_id':talent_fixtures[0]['id'],'job_id':job_id,'q':'Recruiting Check 0'}
    expect(admin.request(directory,invite)[0]==200,'Recruiter can invite listed talent to a published role')
    offer={'csrf_token':admin_csrf,'action':'hire_offer','talent_id':talent_fixtures[0]['id'],'role_title':'Senior product designer','message':'We would like to make you an offer.','q':'Recruiting Check 0'}
    expect(admin.request(directory,offer)[0]==200,'Recruiter can send direct hire offer')
    opportunity_path='/users/talents/opportunities.php'
    talent_csrf,opportunities=talent.form(opportunity_path)
    expect('Invitation to apply' in opportunities and 'Direct hire offer' in opportunities,'Talent receives recruiter invitations and offers in account')
    expect('Accept hire offer' in opportunities and 'I agree to share these details' in opportunities,'Talent offer shows explicit contact-sharing consent')
    expect('Direct hire offer' not in other.request(opportunity_path,api=False)[1],'Recruiter messages are private to the intended talent account')
    offer_fragment=opportunities[opportunities.index('Direct hire offer'):]
    offer_fragment=offer_fragment[:offer_fragment.index('Accept hire offer')]
    offer_id=re.search(r'name="id" value="(\d+)"',offer_fragment).group(1)
    offer_version=re.search(r'name="version" value="([^"]+)"',offer_fragment).group(1)
    expect(talent.request(opportunity_path,{'csrf_token':talent_csrf,'id':offer_id,'version':offer_version,'response':'accept'})[0]==422,'Talent must consent before accepting hire offer')
    code,body,_=talent.request(opportunity_path,{'csrf_token':talent_csrf,'id':offer_id,'version':offer_version,'response':'accept','consent':'1'})
    expect(code==200 and 'Hire offer accepted.' in body,'Talent accepts direct hire offer')
    code,body,_=admin.request(directory)
    expect(code==200 and 'Accepted hire offers' in body and talent_fixtures[0]['email'] in body,'Employer sees contact details only after accepted hire offer')
    expect(talent_fixtures[0]['email'] not in beta.request('/users/admin/talent-directory.php',api=False)[1],'Accepted hire details remain private to the hiring company')
    beta_token,_=beta.form('/users/admin/workspace.php?module=jobs&action=new')
    expect(beta.request('/api/records.php?module=jobs',{**update,'csrf_token':beta_token,'version':job['version']})[0]==404,'Other company cannot edit published role')
    grant(admin,'employee',access['employee_id'],{})
    expect(employee.request('/api/records.php?module=jobs')[0]==403,'Unassigned employee cannot read job records')
    grant(admin,'employee',access['employee_id'],{'jobs':'view','applications':'view'})
    staff_token,_=employee.form('/users/help.php')
    expect(employee.request('/api/records.php?module=jobs',{**values,'csrf_token':staff_token})[0]==403,'Viewer cannot publish jobs')
    expect(employee.request(directory)[0]==200,'Recruiting staff with jobs view access can read talent directory')
    staff_directory_csrf,_=employee.form(directory)
    expect(employee.request(directory,{'csrf_token':staff_directory_csrf,'action':'shortlist','talent_id':talent_fixtures[0]['id']})[0]==403,'Recruiting staff without edit access cannot change shortlist')
    csrf,body=talent.form(apply)
    payload={'csrf_token':csrf,'job_version':hidden(body,'job_version'),'profile_version':hidden(body,'profile_version'),'candidate_note':'I would love to help. <img src=x onerror=alert(1)>','consent':'1','company_id':'1','account_id':talent_fixtures[1]['id'],'status':'hired','email':'forged@example.test'}
    expect(talent_fixtures[0]['email'] in body and 'Profile shared with this employer' in body,'Candidate reviews exact shared profile and contact email')
    expect(talent.request(apply,{**payload,'csrf_token':'bad'})[0]==419,'Application requires CSRF')
    expect(talent.request(apply,{**payload,'consent':''})[0]==422,'Application requires explicit sharing consent')
    expect(talent.request(apply,{**payload,'candidate_note':'x'*4001})[0]==422,'Application note has a server size limit')
    expect(talent.request(apply,{**payload,'profile_version':'bad'})[0]==409,'Changed profile version cannot be submitted')
    expect(talent.request(apply,{**payload,'job_version':'bad'})[0]==409,'Changed job version cannot be submitted')
    pdf=b'%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n'
    for name,data in [('bad.php',b'<?php echo 1;'),('wrong.jpg',pdf),('large.pdf',b'%PDF-'+b'x'*(6*1024*1024))]:
        expect(multipart(talent,apply,payload,name,data)[0]==422,'Unsafe resume rejected: '+name)
    code,body,url=multipart(talent,apply,payload,'candidate-resume.pdf',pdf)
    expect(code==200 and 'Application submitted.' in body and '/applications.php?id=' in url,'Candidate submits profile and private resume')
    application_id=int(url.split('id=')[-1]);detail='/users/talents/applications.php?id='+str(application_id);review='/users/admin/application.php?id='+str(application_id)
    expect(talent_fixtures[0]['email'] in body and 'forged@example.test' not in body and '>New<' in body,'Server derives applicant identity and initial stage')
    file_id=int(re.search(r'/api/attachments.php\?id=(\d+)',body).group(1))
    stale_withdraw={'csrf_token':hidden(body,'csrf_token'),'action':'withdraw','confirm':'1','version':hidden(body,'version')}
    expect(talent.request(apply,payload)[0]==409,'Duplicate application rejected')
    expect(other.request(detail)[0]==404,'Another talent cannot read application')
    expect(beta.request(review)[0]==404,'Another company cannot review application')
    expect(anon.request(review)[0]==401,'Anonymous review denied')
    expect(other.request(review)[0]==401,'Talent cannot enter employer review')
    for client,label,expected in [(talent,'Applicant',200),(admin,'Employer',200),(employee,'Assigned viewer',200),(other,'Another talent',404),(beta,'Another employer',404),(anon,'Anonymous',401)]:
        status,content,headers=download(client,file_id);expect(status==expected,label+' resume access is scoped')
        if status==200:expect(content==pdf and headers['Content-Type']=='application/octet-stream' and headers['Cache-Control']=='no-store','Resume bytes delivered privately as attachment')
    review_csrf,body=admin.form(review);stage={'csrf_token':review_csrf,'version':hidden(body,'version'),'status':'interview'}
    expect('&lt;img' in body and '<img src=x' not in body,'Applicant note is escaped for employer')
    expect(admin.request(review,{**stage,'csrf_token':'bad'})[0]==419,'Stage update requires CSRF')
    expect(employee.request(review,{**stage,'csrf_token':staff_token})[0]==403,'Application viewer cannot change stage')
    expect(admin.request(review,{**stage,'status':'injected'})[0]==422,'Stage must match available choices')
    code,body,_=admin.request(review,stage,False);expect(code==200 and 'Application stage updated.' in body,'Employer changes application stage')
    expect(admin.request(review,stage)[0]==409,'Stale stage update rejected')
    expect('Interview' in talent.request(detail,api=False)[1],'Candidate sees employer stage update')
    csrf,body=admin.form(review)
    interview={'csrf_token':csrf,'action':'interview_schedule','version':hidden(body,'version'),'interview_title':'Portfolio discussion','interview_datetime':'2099-05-12T14:30','interview_timezone':'Africa/Lagos','meeting_type':'remote','meeting_url':'https://meet.example.test/room','meeting_location':'','interview_agenda':'Discuss the candidate portfolio.'}
    expect(admin.request(review,{**interview,'csrf_token':'bad'})[0]==419,'Interview scheduling requires CSRF')
    expect(admin.request(review,{**interview,'meeting_url':'http://meet.example.test/room'})[0]==422,'Remote interview requires HTTPS')
    expect(admin.request(review,{**interview,'interview_datetime':'2000-01-01T10:00'})[0]==422,'Past interview time is rejected')
    expect(employee.request(review,{**interview,'csrf_token':staff_token},False)[0]==403,'Application viewer cannot schedule an interview')
    code,body,_=admin.request(review,interview,False);expect(code==200 and 'Interview scheduled.' in body,'Employer schedules a remote interview')
    code,body,_=talent.request(detail,api=False)
    expect(code==200 and 'Portfolio discussion' in body and 'Tue, 12 May 2099 at 14:30 WAT' in body and 'https://meet.example.test/room' in body,'Candidate sees interview in its selected timezone')
    interview_id=int(re.search(r'name="interview_id" value="(\d+)"',body).group(1))
    interview_version=re.search(r'name="interview_version" value="([^"]+)"',body).group(1)
    response={'csrf_token':hidden(body,'csrf_token'),'version':hidden(body,'version'),'action':'interview_response','interview_id':str(interview_id),'interview_version':interview_version,'response':'accepted'}
    expect(talent.request(detail,{**response,'csrf_token':'bad'})[0]==419,'Interview response requires CSRF')
    expect(talent.request(detail,{**response,'interview_version':'stale'})[0]==409,'Stale interview response is rejected')
    code,body,_=talent.request(detail,response,False);expect(code==200 and 'Interview accepted.' in body,'Candidate accepts interview')
    expect(talent.request(detail,response)[0]==409,'Interview cannot be answered twice')
    _,body=admin.form(review)
    expect('Accepted' in body,'Employer sees candidate interview response')
    interview_cancel={'csrf_token':hidden(body,'csrf_token'),'version':hidden(body,'version'),'action':'interview_cancel','interview_id':str(interview_id),'interview_version':re.search(r'name="interview_version" value="([^"]+)"',body).group(1)}
    expect(admin.request(review,{**interview_cancel,'interview_version':'stale'})[0]==409,'Stale interview cancellation is rejected')
    code,body,_=admin.request(review,interview_cancel,False);expect(code==200 and 'Interview cancelled.' in body,'Employer cancels an accepted interview')
    expect('Cancelled' in talent.request(detail,api=False)[1],'Candidate sees interview cancellation')
    expect(talent.request(detail,stale_withdraw)[0]==409,'Withdrawal requires current application version')
    expect(admin.request('/api/attachments.php',{'csrf_token':review_csrf,'action':'remove','module':'applications','record_id':application_id,'id':file_id})[0]==409,'Employer cannot remove candidate submitted resume')
    overwrite={'csrf_token':review_csrf,'action':'update','id':application_id,'version':'anything','applicant_name':'Changed','email':'changed@example.test','position_applied_for':'Changed','status':'hired'}
    expect(admin.request('/api/records.php?module=applications',overwrite)[0]==409,'Generic API cannot overwrite submitted applicant identity')
    code,_,url=admin.request('/users/admin/workspace.php?module=applications&action=edit&id='+str(application_id),api=False)
    expect(code==200 and '/application.php?id=' in url,'Applicant register opens dedicated review')
    grant(admin,'employee',access['employee_id'],{})
    expect(download(employee,file_id)[0]==403 and employee.request(review)[0]==403,'Revoking application access blocks review and resume')
    withdraw_token,body=talent.form(detail);withdraw={'csrf_token':withdraw_token,'version':hidden(body,'version'),'action':'withdraw','confirm':'1'}
    expect(talent.request(detail,{**withdraw,'confirm':''})[0]==422,'Withdrawal requires confirmation')
    expect(talent.request(detail,{**withdraw,'csrf_token':'bad'})[0]==419,'Withdrawal requires CSRF')
    code,body,_=talent.request(detail,withdraw,False);expect(code==200 and 'Application withdrawn.' in body,'Applicant withdraws application')
    expect(download(admin,file_id)[0]==403 and download(talent,file_id)[0]==200,'Withdrawal revokes employer download and preserves applicant copy')
    _,body=admin.form(review)
    expect('Withdrawn' in body and 'Save stage' not in body,'Employer sees withdrawn state with review closed')
    expect(admin.request(review,stage)[0]==409,'Employer cannot change withdrawn application')
    expect(talent.request(apply,payload)[0]==409,'Withdrawn application cannot be resubmitted')
    # Keep one active application for browser review and close publication to avoid test jobs on the public board.
    csrf,body=other.form(apply);second={'csrf_token':csrf,'job_version':hidden(body,'job_version'),'profile_version':hidden(body,'profile_version'),'consent':'1','candidate_note':'Second synthetic application'}
    code,body,url=other.request(apply,second,False);expect(code==200 and 'Application submitted.' in body,'Another candidate can apply without a resume')
    second_id=int(url.split('id=')[-1]);job=job_row(admin,job_id)
    expect(admin.request('/api/records.php?module=jobs',{**update,'version':job['version'],'status':'closed'})[0]==200,'Employer closes published role')
    expect('Applications closed' in anon.request(public,api=False)[1],'Closed public job explains unavailability')
    expect(title not in anon.request('/jobs/?q='+title.replace(' ','+'),api=False)[1].split('open roles')[-1],'Closed job removed from board')
    (ROOT/'.local/recruiting-browser-fixture.json').write_text(json.dumps({'job_id':job_id,'public_id':job['public_id'],'application_id':application_id,'active_application_id':second_id,'resume_id':file_id,'talents':talent_fixtures}),encoding='utf-8')
finally:
    (ROOT/'work/test-results/recruiting-smoke.json').write_text(json.dumps({'passed':sum(x['passed'] for x in checks),'total':len(checks),'checks':checks},indent=2),encoding='utf-8')
    print(f'Recruiting: {sum(x["passed"] for x in checks)}/{len(checks)} checks passed')

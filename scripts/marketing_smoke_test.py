"""Read-only HTTP checks for three public audiences; no DNS or provider calls."""
from pathlib import Path
from urllib.request import Request, urlopen
from urllib.error import HTTPError
import json
import subprocess
import os
import re

root = Path(__file__).resolve().parent.parent
checks = []
def check(value, label):
    checks.append({'name':label,'passed':bool(value)})
    assert value, label
def get(path, host=None):
    request = Request('http://127.0.0.1:8000'+path,headers={'Host':host} if host else {})
    try: response = urlopen(request,timeout=15)
    except HTTPError as error: response = error
    return response.status, response.read().decode()
try:
    audiences = {'business':('Less juggling.','/users/admin/signup.php'),'talents':('Good work','/users/talents/talentsignup.php'),'work':('A clearer start','/users/employees/login.php')}
    for audience,(headline,action) in audiences.items():
        for route in ['/landing/'+audience+'/','/landing/'+audience+'/index.php']:
            status,html = get(route)
            check(status == 200 and headline in html, audience + ' preview route works: ' + route)
        status,html = get('/',audience+'.simplr.com')
        check(status == 200 and headline in html, audience + ' subdomain selects its own index')
        check(action in html, audience + ' links to the correct existing account flow')
        check('<link rel="canonical" href="https://'+audience+'.simplr.com/">' in html, audience + ' has its own canonical URL')
        check('name="description"' in html and 'property="og:title"' in html, audience + ' has promotional metadata')
        check('name="robots" content="noindex, nofollow"' in html, audience + ' local preview is not indexable')
        check(all('/landing/'+other+'/' in html for other in audiences), audience + ' switches between all local previews')
        for target in set(re.findall(r'href="(/users/[^"?]+\.php)"',html)):
            check(get(target)[0] == 200, audience + ' account destination loads: ' + target)
    status,html = get('/','talents.simplr.com.attacker.invalid')
    check(status == 200 and 'Less juggling.' in html and 'attacker.invalid' not in html, 'Unknown Host cannot inject a redirect or canonical URL')
    check(get('/landing/not-a-role/')[0] == 404, 'Unknown public audience has no dynamic file access')
    check(get('/work/overnight-handoff.md')[0] == 404, 'Work landing does not expose private work directory')
    env = dict(os.environ,APP_ENV='production',APP_BUSINESS_URL='https://business.simplr.com',APP_TALENT_URL='https://talents.simplr.com',APP_WORK_URL='https://work.simplr.com')
    code = "require 'app/mail.php'; echo json_encode(array_map(fn($p)=>account_link($p,'synthetic-token'), ['/users/admin/verifyaccount.php','/users/talents/verifyaccount.php','/users/accept-invitation.php','/users/reset_password.php']));"
    result = subprocess.run([r'C:\Program Files\Ampps\php\php.exe','-r',code],env=env,cwd=root,capture_output=True,text=True)
    check(result.returncode == 0, 'Configured audience email-link builder runs')
    links = json.loads(result.stdout)
    for link,host in zip(links,['business.simplr.com','talents.simplr.com','work.simplr.com','work.simplr.com']):
        check(link.startswith('https://'+host+'/users/'), 'Account link stays on its intended audience: ' + host)
finally:
    report={'checks':len(checks),'passed':sum(c['passed'] for c in checks),'results':checks}
    (root/'work/test-results/marketing-smoke.json').write_text(json.dumps(report,indent=2))
    print(json.dumps({'checks':report['checks'],'passed':report['passed']}))

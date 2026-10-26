"""Local-only talent journey checks. Runs core checks first using their fixtures."""
from smoke_test import Client, ROOT, expect, mail_path, checks, run
import json
import re
from urllib.parse import quote
from urllib.request import Request
from urllib.error import HTTPError

password = 'Synthetic-talent-passphrase-2026!'
new_password = 'Synthetic-updated-passphrase-2026!'
talent, other, employer, anon = Client(), Client(), Client(), Client()
name = 'Test Talent ' + run
email = 'talent-' + run + '@example.test'

def login(client, address, secret, base='/users/talents/talentlogin.php'):
    csrf, _ = client.csrf(base)
    return client.request(base, {'csrf_token': csrf, 'email': address, 'password': secret})

def profile_form(client):
    csrf, body = client.csrf('/users/talents/talentpool.php')
    version = re.search(r'name="version" value="([a-f0-9]+)"', body).group(1)
    return {'csrf_token': csrf, 'version': version}

try:
    expect(anon.request('/users/talents/talentpool.php', api=True)[0] == 401, 'Anonymous talent profile is protected')
    expect(anon.request('/users/talents/congrats.php', {'email': email})[0] == 419, 'Talent signup requires CSRF')
    csrf, _ = talent.csrf('/users/talents/talentsignup.php')
    code, body, url = talent.request('/users/talents/congrats.php', {'csrf_token':csrf,'full_name':name,'industry':'Design','email':email,'password':password,'confirm_password':password,'status':'Verified','talent_id':'1'})
    expect(code == 200 and '/verifyaccount.php' in url, 'Talent signup ignores client verification status')
    expect('/verifyaccount.php' in login(talent,email,password)[2], 'Unverified talent cannot enter profile')
    path = mail_path(email,'Verify your Simplr talent account')
    csrf, _ = talent.csrf(path)
    code, body, url = talent.request(path, {'csrf_token':csrf})
    expect(code == 200 and 'Email verified.' in body, 'Talent verifies using emailed token')
    csrf, _ = talent.csrf(path)
    expect(talent.request(path, {'csrf_token':csrf})[0] == 422, 'Talent verification token is single use')
    before = {c.name:c.value for c in talent.cookies}.get('PHPSESSID')
    code, body, url = login(talent,email,password)
    after = {c.name:c.value for c in talent.cookies}.get('PHPSESSID')
    expect(code == 200 and '/talentpool.php' in url and name in body, 'Verified talent can sign in')
    expect(before != after, 'Talent login rotates session')
    expect('Talent workspace' in body and '/users/admin/newdashboard.php' not in body, 'Talent receives dedicated navigation')
    expect(talent.request('/api/records.php?module=employees',api=True)[0] == 401, 'Talent cannot read business API')
    expect(talent.request('/users/admin/talent-directory.php',api=True)[0] == 401, 'Talent cannot read employer directory')
    fixture = json.loads((ROOT / '.local/browser-fixture.json').read_text())
    csrf, _ = employer.csrf('/users/admin/login.php')
    code, body, url = employer.request('/users/admin/login_process.php', {'csrf_token':csrf,'email':fixture['email'],'password':fixture['password']})
    expect(code == 200 and '/newdashboard.php' in url, 'Employer signs in for directory checks')
    search = '/users/admin/talent-directory.php?q=' + quote(name)
    values = {'headline':'Product designer <script>alert(1)</script>','skills':'Research, prototyping','bio':'Synthetic professional profile','location':'Lagos','experience':'Five years','availability':'Open to roles','portfolio_url':'https://example.test/portfolio','visibility':'private'}
    form = profile_form(talent)
    boundary = 'SimplrSyntheticUploadBoundary'
    payload = ''.join('--' + boundary + '\r\nContent-Disposition: form-data; name="' + key + '"\r\n\r\n' + value + '\r\n' for key, value in {**form, **values}.items())
    payload += '--' + boundary + '\r\nContent-Disposition: form-data; name="resume"; filename="synthetic.txt"\r\nContent-Type: text/plain\r\n\r\nSynthetic test attachment\r\n--' + boundary + '--\r\n'
    upload = Request('http://127.0.0.1:8000/users/talents/submit_talent.php', data=payload.encode(), headers={'Content-Type':'multipart/form-data; boundary=' + boundary})
    try:
        response = talent.opener.open(upload, timeout=30)
    except HTTPError as error:
        response = error
    expect(response.status == 422 and 'File uploads are not available' in response.read().decode(), 'Unsupported talent attachment is explicitly rejected')
    expect(talent.request('/users/talents/talentpool.php',values)[0] == 419, 'Profile saves require CSRF')
    code, body, _ = talent.request('/users/talents/talentpool.php',{**form,**values,'account_id':1})
    expect(code == 200 and 'Profile saved.' in body and '&lt;script&gt;' in body, 'Profile saves escaped professional details')
    expect('No matching profiles.' in employer.request(search)[1], 'Private talent is absent from employer directory')
    expect(talent.request('/users/talents/talentpool.php',{**form,**values})[0] == 409, 'Stale profile edits are rejected')
    form = profile_form(talent)
    expect(talent.request('/users/talents/talentpool.php',{**form,**values,'portfolio_url':'javascript:alert(1)'})[0] == 422, 'Unsafe portfolio URL is rejected')
    code, body, _ = talent.request('/users/talents/talentpool.php',{**form,**values,'visibility':'listed'})
    expect(code == 200 and 'Profile saved.' in body, 'Talent explicitly lists profile')
    code, body, _ = employer.request(search)
    expect(code == 200 and name in body and '&lt;script&gt;' in body and '<script>alert' not in body, 'Employer sees opted-in profile with escaped content')
    expect(email not in body, 'Directory does not disclose talent email')
    expect(anon.request('/users/admin/talent-directory.php',api=True)[0] == 401, 'Directory requires verified business session')
    other_email = 'talent-other-' + run + '@example.test'
    csrf, _ = other.csrf('/users/talents/talentsignup.php')
    other.request('/users/talents/talentsignup.php',{'csrf_token':csrf,'full_name':'Other Talent','industry':'Design','email':other_email,'password':password,'confirm_password':password})
    path2 = mail_path(other_email,'Verify your Simplr talent account')
    csrf, _ = other.csrf(path2)
    other.request(path2,{'csrf_token':csrf})
    login(other,other_email,password)
    form2 = profile_form(other)
    other.request('/users/talents/submit_talent.php',{**form2,**values,'headline':'Another account profile','account_id':'1','talent_id':'1'})
    expect('Product designer &lt;script&gt;' in talent.request('/users/talents/talentpool.php')[1], 'Submitted identity cannot modify another talent profile')
    form = profile_form(talent)
    talent.request('/users/talents/talentpool.php',{**form,**values,'visibility':'private'})
    expect('No matching profiles.' in employer.request(search)[1], 'Withdrawing listing removes profile immediately')
    csrf, _ = anon.csrf('/users/talents/resetpassword.php')
    code, body, _ = anon.request('/users/talents/resetpassword.php',{'csrf_token':csrf,'email':email})
    expect(code == 200 and 'If that email' in body, 'Talent recovery has generic response')
    reset = mail_path(email,'Reset your Simplr talent password')
    csrf, _ = anon.csrf(reset)
    expect(anon.request(reset,{'csrf_token':csrf,'new_password':'short','confirm_password':'short'})[0] == 422, 'Talent recovery enforces password length')
    code, body, _ = anon.request(reset,{'csrf_token':csrf,'new_password':new_password,'confirm_password':new_password})
    expect(code == 200 and 'Password updated.' in body, 'Talent password reset succeeds')
    expect(talent.request('/users/talents/talentpool.php',api=True)[0] == 401, 'Password reset invalidates existing talent sessions')
    expect(login(talent,email,password)[0] == 422, 'Old talent password is rejected')
    expect('/talentpool.php' in login(talent,email,new_password)[2], 'New talent password works')
    csrf, _ = anon.csrf(reset)
    expect(anon.request(reset,{'csrf_token':csrf,'new_password':password,'confirm_password':password})[0] == 422, 'Talent reset token is single use')
    csrf, _ = talent.csrf('/users/talents/talentpool.php')
    expect('/users/talents/talentlogin.php' in talent.request('/users/talents/talentlogout.php',{'csrf_token':csrf})[2], 'Talent sign-out returns to talent sign-in')
    (ROOT / '.local/talent-browser-fixture.json').write_text(json.dumps({'email':email,'password':new_password}))
finally:
    report = {'checks':len(checks),'passed':sum(c['passed'] for c in checks),'failures':[c['name'] for c in checks if not c['passed']]}
    (ROOT / 'work/test-results' / ('talent-smoke-' + run + '.json')).write_text(json.dumps({'summary':report,'checks':checks},indent=2))
    print(json.dumps(report))

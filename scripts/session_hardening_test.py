"""Owner sign-in and session hardening that the journey suites do not already cover.

Uses only the synthetic beta workspace from smoke_test.py and random example.test
addresses; log mail only. Covers account enumeration on sign-in and reset, rejection
of attacker-chosen session IDs, logout invalidating the server session, GET not
signing anyone out, and cookie flags (Secure under an HTTPS APP_URL on a throwaway
local server).
"""
from access_smoke_test import ROOT, BASE, Client, expect, checks
from urllib.request import Request, build_opener, HTTPCookieProcessor, HTTPRedirectHandler
from urllib.error import HTTPError
from http.cookiejar import CookieJar
import json
import os
import re
import subprocess
import time
import uuid


class Session(Client):
    def __init__(self, base=BASE):
        self.base = base
        self.jar = CookieJar()
        self.opener = build_opener(HTTPCookieProcessor(self.jar))

    def sid(self):
        return {cookie.name: cookie.value for cookie in self.jar}.get('PHPSESSID')

    def flash_after(self, path, data):
        token, _ = self.form(path.replace('login_process.php', 'login.php'))
        status, body, url = self.request(path, {'csrf_token': token, **data}, False)
        message = re.search(r'data-flash-type="[a-z]+"[^>]*>(.*?)<', body, re.S) or re.search(r'class="(?:notice|flash|alert)[^"]*"[^>]*>(.*?)<', body, re.S)
        return status, url, (message.group(1).strip() if message else '')


def raw(path, cookie=None, base=BASE):
    class Stop(HTTPRedirectHandler):
        def redirect_request(self, *args, **kwargs):
            return None
    headers = {'Accept': 'text/html'}
    if cookie:
        headers['Cookie'] = cookie
    try:
        response = build_opener(Stop()).open(Request(base + path, headers=headers), timeout=20)
    except HTTPError as error:
        response = error
    response.read()
    return response.status, response.headers.get('Location') or '', response.headers.get_all('Set-Cookie') or []


fixture = json.loads((ROOT / '.local/browser-fixture.json').read_text())
beta_email = fixture['email'].replace('alpha-', 'beta-')
beta_password = 'Synthetic-workspace-passphrase-2026!'
unknown = 'nobody-' + uuid.uuid4().hex[:12] + '@example.test'

# Sign-in does not reveal whether an account exists.
wrong = Session().flash_after('/users/admin/login_process.php', {'email': beta_email, 'password': 'Wrong-password-' + uuid.uuid4().hex})
missing = Session().flash_after('/users/admin/login_process.php', {'email': unknown, 'password': 'Wrong-password-' + uuid.uuid4().hex})
expect(wrong[2] != '' and wrong[1:] == missing[1:], 'Owner sign-in gives the same answer for a wrong password and an unknown email')

# Password reset requests do not reveal whether an account exists.
reset_known = Session().flash_after('/users/admin/resetpassword.php', {'email': beta_email})
reset_unknown = Session().flash_after('/users/admin/resetpassword.php', {'email': unknown})
expect(reset_known[2] != '' and reset_known[1:] == reset_unknown[1:], 'Owner reset request gives the same answer for known and unknown emails')

# An attacker-chosen session ID is replaced, not adopted (session.use_strict_mode).
planted = 'a' * 26 + uuid.uuid4().hex[:6]
status, _, cookies = raw('/users/admin/login.php', 'PHPSESSID=' + planted)
issued = [c for c in cookies if c.startswith('PHPSESSID=')]
expect(issued and planted not in issued[0], 'Server issues a fresh session ID instead of accepting a planted one')
expect(issued and 'httponly' in issued[0].lower() and 'samesite=lax' in issued[0].lower(), 'Session cookie is HttpOnly and SameSite=Lax')
expect(issued and 'secure' not in issued[0].lower().replace('samesite', ''), 'Session cookie is not marked Secure on plain local HTTP')

# Sign in, then check GET on the logout page does not sign out, and POST logout kills the session.
owner = Session()
owner.login('admin', beta_email, beta_password)
stolen = 'PHPSESSID=' + owner.sid()
expect(raw('/users/admin/newdashboard.php', stolen)[0] == 200, 'Signed-in session opens the dashboard')
expect(owner.request('/users/admin/logout.php', api=False)[0] == 200 and raw('/users/admin/newdashboard.php', stolen)[0] == 200, 'Opening the logout page alone does not sign out')
token, _ = owner.form('/users/admin/logout.php')
status, body, url = owner.request('/users/admin/logout.php', {'csrf_token': token}, False)
expect('/users/admin/login.php' in url, 'Logout returns to sign-in')
status, location, _ = raw('/users/admin/newdashboard.php', stolen)
expect(status in (302, 303) and '/login.php' in location, 'A copied session cookie stops working after logout')

# Secure flag appears when APP_URL is HTTPS (throwaway server on a spare local port).
env = {**os.environ, 'APP_ENV': 'local', 'MAIL_TRANSPORT': 'log', 'APP_URL': 'https://simplr.test'}
server = subprocess.Popen(['php', '-S', '127.0.0.1:8095', '-t', '.', 'router.php'], cwd=ROOT, env=env, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
try:
    for _ in range(50):
        try:
            secure_cookies = raw('/users/admin/login.php', base='http://127.0.0.1:8095')[2]
            break
        except OSError:
            time.sleep(0.1)
    secure = [c for c in secure_cookies if c.startswith('PHPSESSID=')]
    expect(secure and '; secure' in secure[0].lower(), 'Session cookie is marked Secure when APP_URL is HTTPS')
finally:
    server.terminate()

print(json.dumps({'checks': len(checks), 'passed': sum(c['passed'] for c in checks), 'failures': [c['name'] for c in checks if not c['passed']]}))

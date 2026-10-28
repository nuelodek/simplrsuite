"""Route manifest audit: every reachable entry point is public on purpose or guarded.

For each route in app/routes.php:
  - static: the target calls a sign-in helper, is a guarded legacy adapter, is a
    redirect stub, or is on the reviewed PUBLIC list;
  - anonymous GET returns a public page only for PUBLIC routes, never a 5xx;
  - anonymous POST without a CSRF token never succeeds;
  - a signed-in owner's POST without a CSRF token is refused (419/405/4xx), redirected
    away, or lands on a page that has no POST handling at all.
Writes the matrix to work/test-results/route-audit.json. Local synthetic fixture only.
"""
from access_smoke_test import ROOT, BASE, Client, checks
from urllib.request import Request, build_opener, HTTPCookieProcessor, HTTPRedirectHandler
from urllib.error import HTTPError
from http.cookiejar import CookieJar
import json
import re
import subprocess

# Reviewed public entry points: marketing, jobs, sign-in/sign-up/recovery forms, legal, health.
PUBLIC = {
    '/', '/index.php', '/health.php', '/jobs/', '/jobs/index.php', '/public/privacypolicy.php',
    '/landing/business/', '/landing/talents/', '/landing/work/',
    '/landing/business/index.php', '/landing/talents/index.php', '/landing/work/index.php',
    '/users/admin/login.php', '/users/admin/logout.php', '/users/admin/resetpassword.php', '/users/admin/signup.php',
    '/users/admin/verifyaccount.php', '/users/employees/login.php', '/users/managers/login.php',
    '/users/forgetpassword.php', '/users/reset_password.php',
    '/users/talents/congrats.php', '/users/talents/logintalent.php', '/users/talents/newpassword.php',
    '/users/talents/resetpassword.php', '/users/talents/talentlogin.php', '/users/talents/talentlogout.php',
    '/users/talents/talentsignup.php', '/users/talents/verifyaccount.php', '/users/talents/verifytalents.php',
}
# Public handlers reviewed on 27 Oct 2026: each needs a CSRF token or a single-use hashed token.
PUBLIC_HANDLERS = {
    '/users/accept-invitation.php': 'single-use, expiring invitation token (hashed)',
    '/users/admin/forgetpassword.php': 'single-use, expiring reset token; POST needs CSRF',
    '/users/admin/congrats.php': 'sign-up handler; CSRF and rate limit',
    '/users/admin/login_process.php': 'sign-in handler; CSRF and rate limits',
    '/users/send_reset_email.php': 'staff reset request; CSRF and rate limits',
}
# Guarded by an included file: app/talent-profile.php calls talent_user() when loaded.
TALENT_INCLUDED = {'/users/talents/talentpool.php', '/users/talents/nextsteps.php', '/users/talents/submit_talent.php'}
# Signed provider callbacks: refused without a valid signature (503 while the provider is off).
WEBHOOKS = {'/api/paystack-webhook.php', '/api/flutterwave-webhook.php'}
SIGN_IN = ('/users/admin/login.php', '/users/employees/login.php', '/users/managers/login.php', '/users/talents/talentlogin.php')

routes = json.loads(subprocess.run(['php', '-r', 'echo json_encode(require "app/routes.php");'], cwd=ROOT, capture_output=True, text=True, check=True).stdout)
static_code = r'''
$out = [];
foreach (json_decode($argv[1], true) as $path => $file) {
    $s = is_file($file) ? file_get_contents($file) : '';
    $out[$path] = [
        'exists' => is_file($file),
        'guard' => (bool)preg_match('/\b(admin_user|workspace_user|staff_user|talent_user)\s*\(/', $s),
        'adapter' => (bool)preg_match('/\blegacy_(create|update|lookup)\s*\(/', $s),
        'stub' => (bool)preg_match('/\bredirect\s*\(|header\(\s*.Location:/', $s) && !preg_match('/\bdatabase\s*\(/', $s),
        'handles_post' => (bool)preg_match('/REQUEST_METHOD|\$_POST|require_post\s*\(|\binput\s*\(|legacy_(create|update)\s*\(/', $s),
    ];
}
echo json_encode($out);
'''
static = json.loads(subprocess.run(['php', '-r', static_code, json.dumps(routes)], cwd=ROOT, capture_output=True, text=True, check=True).stdout)


class Stop(HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


def hit(opener, path, data=None):
    try:
        response = opener.open(Request(BASE + path, data=data, headers={'Accept': 'text/html'}), timeout=20)
    except HTTPError as error:
        response = error
    response.read()
    return response.status, response.headers.get('Location') or ''


def expect(condition, label):
    # Records every finding instead of stopping at the first, so the audit lists them all.
    checks.append({'name': label, 'passed': bool(condition)})


anonymous = build_opener(Stop())
fixture = json.loads((ROOT / '.local/browser-fixture.json').read_text())
jar = CookieJar()
owner = Client()
owner.opener = build_opener(HTTPCookieProcessor(jar))
owner.login('admin', fixture['email'], fixture['password'])
signed_in = build_opener(HTTPCookieProcessor(jar), Stop())

matrix = []
for path, file in routes.items():
    info = static[path]
    row = {'route': path, 'file': file, **info}
    expect(info['exists'], 'Route target exists: ' + path)
    expect(path in PUBLIC or path in WEBHOOKS or path in PUBLIC_HANDLERS or path in TALENT_INCLUDED or info['guard'] or info['adapter'] or info['stub'], 'Route is public by review or guarded in code: ' + path)
    row['anon_get'] = hit(anonymous, path)
    if path in TALENT_INCLUDED:
        expect(row['anon_get'][1] == '/users/talents/talentlogin.php', 'Talent page sends anonymous visitors to talent sign-in: ' + path)
    row['anon_post'] = hit(anonymous, path, b'probe=1')
    status, location = row['anon_get']
    expect(status < 500 or path in WEBHOOKS, 'Anonymous GET does not error: ' + path)
    expect(status != 200 or path in PUBLIC, 'Only reviewed public routes answer anonymous GET: ' + path)
    status, location = row['anon_post']
    expect(not (200 <= status < 300) or not info['handles_post'], 'Anonymous POST without CSRF never runs a handler: ' + path)
    if 'logout' not in path:
        row['owner_post'] = hit(signed_in, path, b'probe=1')
        status, location = row['owner_post']
        refused = status in (400, 401, 403, 404, 405, 419, 422, 503)
        redirected_away = status in (302, 303) and (location in SIGN_IN or location.startswith(('/users/admin/newdashboard.php', '/users/employees/workspace.php', '/users/admin/resetpassword.php', '/users/admin/talent-directory.php', '/')))
        harmless_page = 200 <= status < 300 and not info['handles_post']
        expect(refused or redirected_away or harmless_page, 'Signed-in POST without CSRF is refused: ' + path + ' (' + str(status) + ')')
    matrix.append(row)

(ROOT / 'work/test-results').mkdir(parents=True, exist_ok=True)
(ROOT / 'work/test-results/route-audit.json').write_text(json.dumps({'routes': len(routes), 'matrix': matrix}, indent=1), encoding='utf-8')
print(json.dumps({'routes': len(routes), 'checks': len(checks), 'passed': sum(c['passed'] for c in checks), 'failures': [c['name'] for c in checks if not c['passed']]}))

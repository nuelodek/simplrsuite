"""HTTP guards and concurrent settlement; no outgoing provider requests."""
from access_smoke_test import ROOT, Client, expect, checks
from urllib.request import Request, urlopen
from urllib.error import HTTPError
import hashlib
import hmac
import json
import subprocess

PHP=r'C:\Program Files\Ampps\php\php.exe'
TEST_BASE='http://127.0.0.1:8002'

def webhook(body,signature=None):
    headers={'Content-Type':'application/json','Accept':'application/json'}
    if signature is not None: headers['X-Paystack-Signature']=signature
    try:r=urlopen(Request(TEST_BASE+'/api/paystack-webhook.php',data=body,headers=headers),timeout=15)
    except HTTPError as e:r=e
    return r.status,r.read().decode()

try:
    fixture=json.loads((ROOT/'.local/browser-fixture.json').read_text())
    payment=json.loads((ROOT/'.local/billing-test-fixture.json').read_text())
    admin,beta,anon=Client(),Client(),Client()
    admin.login('admin',fixture['email'],fixture['password'])
    beta.login('admin',fixture['email'].replace('alpha-','beta-'),'Synthetic-workspace-passphrase-2026!')
    token,_=admin.form('/users/admin/billing.php')
    expect('not configured' in admin.request('/users/admin/billing.php',api=False)[1],'Unconfigured billing is honest')
    expect(anon.request('/users/admin/billing.php')[0]==401,'Checkout requires verified business session')
    expect(admin.request('/users/admin/billing.php',{})[0]==419,'Checkout requires CSRF')
    expect(admin.request('/users/admin/billing.php',{'csrf_token':token,'nonce':'a'*64,'months[project]':'1','amount':'1','company_id':'1'})[0]==503,'Disabled checkout cannot initialize any provider call')
    path='/users/admin/billing-return.php?reference='+payment['reference']
    expect(beta.request(path)[0]==404,'Payment history and callback are tenant-bound')
    expect(anon.request(path)[0]==401,'Callback requires sign-in')
    expect(admin.request(path,api=False)[0]==200,'Owner can view stored payment status')
    expect(admin.request(path,{})[0]==419,'Manual verification requires CSRF')
    expect(anon.request('/api/paystack-webhook.php')[0]==405,'Webhook rejects GET')
    # The 8002 test router supplies only a fake test key. Unknown refs are ignored,
    # and this already-paid order is acknowledged without calling the provider.
    raw=json.dumps({'event':'charge.success','data':{'reference':payment['reference']}}).encode()
    key=b'sk_test_httpfixtureonlynotarealkey'
    sign=lambda body:hmac.new(key,body,hashlib.sha512).hexdigest()
    expect(webhook(raw)[0]==401,'Webhook rejects absent signature')
    expect(webhook(raw,'0'*128)[0]==401,'Webhook rejects forged signature')
    expect(webhook(raw+b' ',sign(raw))[0]==401,'Webhook authenticates exact raw payload')
    expect(webhook(raw,sign(raw))[0]==200,'Valid duplicate webhook is acknowledged')
    expect(webhook(raw,sign(raw))[0]==200,'Repeated webhook remains idempotent')
    unrelated=b'{"event":"charge.success","data":{"reference":"other-product"}}'
    expect(webhook(unrelated,sign(unrelated))[0]==200,'Other products cannot create workspace orders')
    invalid=b'not-json'
    expect(webhook(invalid,sign(invalid))[0]==400,'Signed malformed JSON is rejected')
    large=b'x'*262145
    expect(webhook(large,sign(large))[0]==413,'Webhook body is bounded')
    workers=[subprocess.Popen([PHP,'scripts/paystack_concurrency_worker.php'],cwd=ROOT,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True) for _ in range(2)]
    outputs=[worker.communicate(timeout=30) for worker in workers]
    expect(all(worker.returncode==0 and out[0]=='paid' for worker,out in zip(workers,outputs)),'Concurrent callback and webhook both complete safely')
    code="""require 'app/database.php';
if(config('APP_ENV')!=='local'||config('MAIL_TRANSPORT')!=='log')exit(2);
$f=json_decode(file_get_contents('.local/billing-concurrency-fixture.json'),true);
$r=db_one(database(),\"SELECT COUNT(*) AS total FROM billing_events e JOIN billing_orders o ON o.id=e.order_id WHERE o.reference=? AND e.event_type='fulfillment'\",[$f['reference']]); echo $r['total'];"""
    r=subprocess.run([PHP,'-r',code],cwd=ROOT,capture_output=True,text=True)
    expect(r.returncode==0 and r.stdout=='1','Concurrent settlement grants access exactly once')
    flutterwave=json.loads((ROOT/'.local/flutterwave-test-fixture.json').read_text())
    dollar_path='/users/admin/billing-return.php?tx_ref='+flutterwave['reference']+'&status=successful&transaction_id=1'
    expect(beta.request(dollar_path)[0]==404,'Flutterwave callback cannot cross company boundaries')
    expect(admin.request(dollar_path,api=False)[0]==200,'Flutterwave callback resolves stored reference')
    expect('Flutterwave' in admin.request('/users/admin/billing.php?currency=USD',api=False)[1],'Billing offers the source USD provider')
    expect(admin.request('/users/admin/billing.php?currency=EUR')[0]==422,'Unsupported currency cannot start checkout')
    expect(admin.request('/users/admin/billing.php',{'csrf_token':token,'nonce':'b'*64,'currency':'USD','months[project]':'1'})[0]==503,'Unconfigured Flutterwave cannot start a provider call')
    def flw_hook(body,signature=None):
        headers={'Content-Type':'application/json'}
        if signature is not None: headers['Verif-Hash']=signature
        try: response=urlopen(Request(TEST_BASE+'/api/flutterwave-webhook.php',data=body,headers=headers),timeout=15)
        except HTTPError as error: response=error
        return response.status,response.read().decode()
    flw_secret='local-fixture-hash-only-not-a-real-secret'
    raw=json.dumps({'event':'charge.completed','data':{'tx_ref':flutterwave['reference'],'amount':1,'currency':'USD'}}).encode()
    expect(flw_hook(raw)[0]==401,'Flutterwave webhook requires signature')
    expect(flw_hook(raw,'forged')[0]==401,'Flutterwave webhook rejects wrong signature')
    expect(flw_hook(raw,flw_secret)[0]==200,'Valid duplicate dollar webhook acknowledges paid order')
    expect(flw_hook(raw,flw_secret)[0]==200,'Repeated Flutterwave webhook is idempotent')
    expect(flw_hook(b'invalid',flw_secret)[0]==400,'Flutterwave rejects malformed JSON')
    expect(flw_hook(b'x'*262145,flw_secret)[0]==413,'Flutterwave webhook body is bounded')
    wrong_provider=json.dumps({'event':'charge.completed','data':{'tx_ref':payment['reference']}}).encode()
    expect('ignored' in flw_hook(wrong_provider,flw_secret)[1],'Flutterwave hook cannot settle Paystack orders')
    wrong_provider=json.dumps({'event':'charge.success','data':{'reference':flutterwave['reference']}}).encode()
    expect('ignored' in webhook(wrong_provider,sign(wrong_provider))[1],'Paystack hook cannot settle Flutterwave orders')
finally:
    report={'passed':sum(x['passed'] for x in checks),'total':len(checks),'checks':checks,'provider_calls':'none'}
    (ROOT/'work/test-results/paystack-http.json').write_text(json.dumps(report,indent=2))
    print(json.dumps({k:report[k] for k in ['passed','total','provider_calls']}))

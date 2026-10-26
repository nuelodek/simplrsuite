"""Private record attachments: synthetic local files only, no external traffic."""
from access_smoke_test import ROOT, Client, expect, checks, rows, grant
from urllib.request import Request
from urllib.error import HTTPError
import base64
import hashlib
import json
import re
import uuid

def send(client, path, fields=None, file=None):
    headers = {'Accept':'application/json'}
    body = None
    if fields is not None:
        boundary = 'simplr' + uuid.uuid4().hex
        pieces = []
        for key,value in fields.items():
            pieces.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
        if file:
            name,content = file
            pieces.append(f'--{boundary}\r\nContent-Disposition: form-data; name="attachment"; filename="{name}"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode()+content+b'\r\n')
        body = b''.join(pieces)+f'--{boundary}--\r\n'.encode()
        headers['Content-Type'] = 'multipart/form-data; boundary='+boundary
    try: response = client.opener.open(Request(client.base+path,data=body,headers=headers),timeout=30)
    except HTTPError as error: response = error
    return response.status,response.read(),response.headers

try:
    fixture=json.loads((ROOT/'.local/browser-fixture.json').read_text())
    staff=json.loads((ROOT/'.local/recovery-browser-fixture.json').read_text())
    access=json.loads((ROOT/'.local/access-browser-fixture.json').read_text())
    assert re.fullmatch(r'alpha-\d+@example\.test',fixture['email'])
    admin,beta,employee,anon=[Client() for _ in range(4)]
    admin.login('admin',fixture['email'],fixture['password'])
    beta.login('admin',fixture['email'].replace('alpha-','beta-'),'Synthetic-workspace-passphrase-2026!')
    employee.login('employee',staff['email'],staff['password'],staff['workspace'])
    lead_id=access['lead_id']
    detail=f'/users/admin/workspace.php?module=leads&action=view&id={lead_id}'
    token,body=admin.form(detail)
    expect('Attachments' in body and 'Add a file' in body,'Record details expose private attachment form')
    expect(beta.request(detail)[0]==404,'Record detail cannot cross companies')
    payload={'csrf_token':token,'module':'leads','record_id':lead_id}
    pdf=b'%PDF-1.4\n% '+uuid.uuid4().hex.encode()+b'\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n'
    expect(send(anon,'/api/attachments.php',payload,('test.pdf',pdf))[0]==401,'Anonymous upload denied')
    expect(send(admin,'/api/attachments.php',{**payload,'csrf_token':'bad'},('test.pdf',pdf))[0]==419,'Upload requires CSRF')
    for name,content in [('shell.php',b'<?php echo 1;'),('vector.svg',b'<svg xmlns="http://www.w3.org/2000/svg"/>'),('wrong.png',pdf),('empty.pdf',b''),('large.pdf',b'%PDF-'+b'x'*(6*1024*1024))]:
        expect(send(admin,'/api/attachments.php',payload,(name,content))[0]==422,'Reject unsafe or invalid attachment: '+name)
    status,data,_=send(admin,'/api/attachments.php',payload,('evidence.pdf',pdf))
    expect(status==201,'Owner uploads a PDF to scoped record')
    file_id=json.loads(data)['id']; download='/api/attachments.php?id='+str(file_id)
    status,content,headers=send(admin,download)
    expect(status==200 and hashlib.sha256(content).digest()==hashlib.sha256(pdf).digest(),'Authenticated download preserves uploaded bytes')
    expect(headers['Content-Type']=='application/octet-stream' and headers['Content-Disposition'].startswith('attachment;') and headers['X-Content-Type-Options']=='nosniff','Downloads cannot execute inline')
    expect(headers['Cache-Control']=='no-store' and headers['Content-Security-Policy']=='sandbox','Downloads are private and sandboxed')
    expect(send(beta,download)[0]==404,'Another company cannot download attachment')
    expect(send(anon,download)[0]==401,'Anonymous download denied')
    grant(admin,'employee',access['employee_id'],{'leads':'view'})
    status,body,_=employee.request(detail,api=False)
    expect(status==200 and 'evidence.pdf' in body and 'name="attachment"' not in body and 'value="remove"' not in body,'View-only details allow downloads without mutations')
    expect(send(employee,download)[0]==200,'View grant permits download')
    staff_token,_=employee.form('/users/help.php')
    staff_payload={**payload,'csrf_token':staff_token}
    expect(send(employee,'/api/attachments.php',staff_payload,('test.pdf',pdf))[0]==403,'Viewer cannot upload')
    expect(employee.request('/api/attachments.php',{**staff_payload,'action':'remove','id':file_id})[0]==403,'Viewer cannot remove')
    grant(admin,'employee',access['employee_id'],{})
    expect(send(employee,download)[0]==403,'Revoked grant immediately denies existing attachment download')
    grant(admin,'employee',access['employee_id'],{'leads':'edit'})
    png=base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jVn0AAAAASUVORK5CYII=')
    status,data,_=send(employee,'/api/attachments.php',staff_payload,('proof.png',png))
    expect(status==201,'Editor can upload a real image')
    image_id=json.loads(data)['id']
    expect(employee.request('/api/attachments.php',{**staff_payload,'action':'remove','id':image_id})[0]==200,'Editor can remove scoped attachment')
    expect(send(admin,'/api/attachments.php?id='+str(image_id))[0]==404,'Removed attachment is unavailable')
    # Both web servers must prevent access to storage even with a known basename.
    stored=next(p.name for p in (ROOT/'storage/attachments').iterdir() if p.is_file() and p.read_bytes()==pdf)
    for base in ['http://127.0.0.1:8000','http://127.0.0.1:8001']:
        expect(Client(base).request('/storage/attachments/'+stored)[0]==404,'Private storage blocked by '+base)
    beta_token,_=beta.form('/users/help.php')
    expect(beta.request('/api/attachments.php',{**payload,'csrf_token':beta_token,'action':'remove','id':file_id})[0]==404,'Other company cannot delete attachment')
    status,data,_=send(admin,'/api/attachments.php',{**payload,'return_to_record':'1'},('invalid.txt',b'no'))
    expect(status==200 and b'Only PDF, PNG and JPEG' in data,'HTML upload validation returns to record with error')
    expect(admin.request('/api/attachments.php',{**payload,'action':'remove','id':file_id})[0]==200,'Owner removes only this synthetic test attachment')
    expect(not (ROOT/'storage/attachments'/stored).exists(),'Removal clears stored bytes')
    expect(admin.request('/api/attachments.php',{**payload,'action':'remove','id':file_id})[0]==404,'Repeated removal has no effect')
    (ROOT/'.local/attachments-browser-fixture.json').write_text(json.dumps({'path':detail}))
finally:
    report={'passed':sum(x['passed'] for x in checks),'total':len(checks),'checks':checks}
    (ROOT/'work/test-results/attachments-smoke.json').write_text(json.dumps(report,indent=2))
    print(json.dumps({'passed':report['passed'],'total':report['total'],'failures':[x['name'] for x in checks if not x['passed']]}))

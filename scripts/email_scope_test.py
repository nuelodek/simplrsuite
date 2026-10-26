"""Regression for company-scoped directory/CRM email uniqueness."""
from access_smoke_test import ROOT,Client,expect,checks
import json
import re
import uuid

try:
    fixture=json.loads((ROOT/'.local/browser-fixture.json').read_text())
    assert re.fullmatch(r'alpha-\d+@example\.test',fixture['email'])
    alpha,beta=Client(),Client()
    alpha.login('admin',fixture['email'],fixture['password'])
    beta.login('admin',fixture['email'].replace('alpha-','beta-'),'Synthetic-workspace-passphrase-2026!')
    # Ensure each synthetic workspace has a selectable department. Some core
    # fixtures intentionally contain no beta directory records.
    for client in [alpha,beta]:
        token,_=client.form('/users/admin/workspace.php?module=departments&action=new')
        expect(client.request('/api/records.php?module=departments',{'csrf_token':token,'department_name':'Email scope regression'})[0]==201,'Create local fixture department')
        expect(client.request('/api/records.php?module=customers',{'csrf_token':token,'first_name':'Shared','last_name':'Customer','business_name':'Scope fixture','customer_email':'customer-'+uuid.uuid4().hex+'@example.test'})[0]==201,'Create local fixture customer')
    configs={
        'leads':('lead_email',{'lead_name':'Shared customer regression','lead_source':'Referral','status':'new'},None),
        'contacts':('contact_email',{'contact_name':'Shared contact regression'},None),
        'accounts':('account_email',{'account_owner':'Shared account regression','status':'active'},'customer_id'),
        'managers':('manager_email',{'manager_name':'Shared manager regression'},'department_id'),
    }
    for key,(email_field,values,relation) in configs.items():
        shared='shared-'+uuid.uuid4().hex+'@example.test'
        for label,client in [('first',alpha),('second',beta)]:
            token,form=client.form('/users/admin/workspace.php?module='+key+'&action=new')
            payload={**values,'csrf_token':token,email_field:shared}
            if relation:
                select=re.search(r'<select[^>]+name="'+relation+r'".*?</select>',form,re.S).group(0)
                payload[relation]=re.search(r'<option value="([^"]+)"',select).group(1)
            status,body,_=client.request('/api/records.php?module='+key,payload)
            expect(status==201,key+' accepts shared email in '+label+' company')
            expect(client.request('/api/records.php?module='+key,payload)[0]==409,key+' rejects duplicate email within one company')
            if key!='managers':
                payload[email_field]=''
                for n in range(2): expect(client.request('/api/records.php?module='+key,payload)[0]==201,key+' permits multiple records without optional email')
finally:
    report={'passed':sum(x['passed'] for x in checks),'total':len(checks),'checks':checks}
    (ROOT/'work/test-results/email-scope.json').write_text(json.dumps(report,indent=2))
    print(json.dumps({'passed':report['passed'],'total':report['total'],'failures':[x['name'] for x in checks if not x['passed']]}))

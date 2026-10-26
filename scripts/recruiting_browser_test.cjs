/* Local browser journey: no external services or real accounts. */
'use strict';
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const {chromium}=require('playwright');
const root=path.resolve(__dirname,'..'),base='http://127.0.0.1:8000',checks=[];
const fixture=JSON.parse(fs.readFileSync(path.join(root,'.local/browser-fixture.json')));
const recruit=JSON.parse(fs.readFileSync(path.join(root,'.local/recruiting-browser-fixture.json')));
const config=fs.readFileSync(path.join(root,'.env.local'),'utf8');assert.match(config,/^APP_ENV=local$/m);assert.match(config,/^MAIL_TRANSPORT=log$/m);
function check(value,name){checks.push({name,passed:!!value});assert.ok(value,name);}
(async()=>{
 const browser=await chromium.launch({headless:true,args:['--disk-cache-size=1','--media-cache-size=1','--disable-gpu-shader-disk-cache'],...(fs.existsSync(chromium.executablePath())?{}:{channel:'msedge'})});
 let admin,jobId,publicId;const errors=[];
 try{
  async function pageFor(){const ctx=await browser.newContext({viewport:{width:1280,height:900}});await ctx.route('**/*',r=>new URL(r.request().url()).hostname==='127.0.0.1'?r.continue():r.abort());const p=await ctx.newPage();p.on('pageerror',e=>errors.push(e.message));return p;}
  admin=await pageFor();await admin.goto(base+'/users/admin/login.php');await admin.getByLabel('Work email').fill(fixture.email);await admin.getByLabel('Password',{exact:true}).fill(fixture.password);await admin.getByRole('button',{name:/Sign in/}).click();await admin.waitForURL('**/newdashboard.php');
  await admin.goto(base+'/users/admin/settings.php');const workspaceTimezone=await admin.locator('[name=timezone]').inputValue();await admin.goto(base+'/users/admin/workspace.php?module=jobs&action=new');
  const title='Browser recruiting check '+Date.now();
  await admin.getByLabel('Job title').fill(title);await admin.getByLabel('Location').fill('Lagos, Nigeria');await admin.getByLabel('Work arrangement').selectOption('remote');await admin.getByLabel('Employment type').selectOption('contract');await admin.getByLabel('Role description').fill('Help a small team make everyday work simpler.\nThis is a synthetic local verification role.');await admin.getByLabel('Skills and requirements').fill('Thoughtful design, research and clear communication.');await admin.getByLabel('Publication status').selectOption('published');
  check(await admin.getByLabel('Closing-date timezone').inputValue()===workspaceTimezone,'New role inherits workspace timezone');
  await admin.locator('main button[type=submit]').click();await admin.waitForURL(u=>u.pathname.endsWith('workspace.php')&&!u.searchParams.has('action'));
  const data=await(await admin.request.get(base+'/api/records.php?module=jobs')).json();const job=data.data.find(x=>x.title===title);check(!!job,'Browser form publishes new job');jobId=job.id;publicId=job.public_id;
  const talent=await pageFor();await talent.goto(base+'/users/talents/apply.php?job='+publicId);check(talent.url().includes('talentlogin.php'),'Application asks unauthenticated visitor to sign in');await talent.getByLabel('Email address').fill(recruit.talents[0].email);await talent.getByLabel('Password',{exact:true}).fill(recruit.talents[0].password);await talent.getByRole('button',{name:'Sign in',exact:true}).click();await talent.waitForURL('**/apply.php?job=*');check(talent.url().endsWith(publicId),'Login returns to intended role');
  for(const [name,p,url] of [['jobs-board',talent,'/jobs/'],['public-job',talent,'/jobs/?job='+publicId],['job-application',talent,'/users/talents/apply.php?job='+publicId],['employer-job-form',admin,'/users/admin/workspace.php?module=jobs&action=edit&id='+jobId],['candidate-history',talent,'/users/talents/applications.php'],['employer-review',admin,'/users/admin/application.php?id='+recruit.active_application_id]]){
   const response=await p.goto(base+url);check(response.status()===200,name+' loads');check(await p.locator('h1').count()===1,name+' has one heading');
   for(const width of [1280,390]){await p.setViewportSize({width,height:900});check(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),name+' fits '+width+'px');}
   if(['job-application','employer-review'].includes(name))await p.screenshot({path:path.join(root,'work/test-results/recruiting-'+name+'-mobile.png'),fullPage:true,animations:'disabled'});
  }
  await talent.goto(base+'/users/talents/apply.php?job='+publicId);await talent.getByLabel('Why this role?').fill('I enjoy making complex workflows easier.');await talent.getByRole('checkbox').check();await talent.getByRole('button',{name:/Submit application/}).click();await talent.waitForURL('**/applications.php?id=*');check(await talent.locator('main .badge').innerText()==='New','Browser candidate submits application');const id=new URL(talent.url()).searchParams.get('id');
  await admin.goto(base+'/users/admin/application.php?id='+id);await admin.getByLabel('Application stage').selectOption('reviewing');await admin.getByRole('button',{name:/Save stage/}).click();check(await admin.locator('main .badge').innerText()==='Reviewing','Browser employer changes stage');
  await talent.reload();check(await talent.locator('main .badge').innerText()==='Reviewing','Browser candidate sees new stage');
  await talent.getByRole('button',{name:'Open navigation'}).click();check(await talent.getByRole('link',{name:'My applications',exact:true}).isVisible(),'Mobile talent navigation includes applications');await talent.keyboard.press('Escape');
  await talent.getByRole('checkbox').check();await talent.getByRole('button',{name:'Withdraw application',exact:true}).click();check(await talent.locator('main .badge').innerText()==='Withdrawn','Browser applicant withdraws');
  check(errors.length===0,'Recruiting pages have no browser script errors');
 }finally{
  if(admin&&jobId){const token=await admin.locator('[name=csrf_token]').first().inputValue();const data=await(await admin.request.get(base+'/api/records.php?module=jobs')).json();const row=data.data.find(x=>x.id===jobId);if(row){const result=await admin.request.post(base+'/api/records.php?module=jobs',{form:{...row,csrf_token:token,action:'update',status:'closed',closing_date:row.closing_date||''}});check(result.status()===200,'Synthetic browser job closed after verification');}}
  await browser.close();fs.writeFileSync(path.join(root,'work/test-results/recruiting-browser.json'),JSON.stringify({passed:checks.filter(x=>x.passed).length,total:checks.length,checks},null,2));console.log('Recruiting browser: '+checks.filter(x=>x.passed).length+'/'+checks.length);
 }
})().catch(e=>{console.error(e.message);process.exitCode=1;});

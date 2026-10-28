'use strict';
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const {chromium} = require('playwright');
const root = path.resolve(__dirname,'..');
const reportPath = path.join(root,'work/test-results/workspace-browser.json');
const checks = [];
function check(ok,name) { checks.push({name,passed:!!ok}); assert.ok(ok,name); }
const fixture = JSON.parse(fs.readFileSync(path.join(root,'.local/browser-fixture.json')));
const staff = JSON.parse(fs.readFileSync(path.join(root,'.local/recovery-browser-fixture.json')));
const access = JSON.parse(fs.readFileSync(path.join(root,'.local/access-browser-fixture.json')));
const help = JSON.parse(fs.readFileSync(path.join(root,'.local/help-browser-fixture.json')));
const attachments = JSON.parse(fs.readFileSync(path.join(root,'.local/attachments-browser-fixture.json')));
const config = fs.readFileSync(path.join(root,'.env'),'utf8');
assert.match(config,/^APP_ENV=local$/m); assert.match(config,/^MAIL_TRANSPORT=log$/m);
(async()=>{
  const browser = await chromium.launch({headless:true,...(fs.existsSync(chromium.executablePath()) ? {} : {channel:'msedge'})});
  try {
    const errors=[];
    async function makeContext() {
      const ctx=await browser.newContext({viewport:{width:1280,height:900}});
      await ctx.route('**/*',route=>new URL(route.request().url()).hostname==='127.0.0.1' ? route.continue() : route.abort());
      const page=await ctx.newPage(); page.on('pageerror',e=>errors.push(e.message)); return {ctx,page};
    }
    const {ctx,page}=await makeContext();
    await page.goto('http://127.0.0.1:8000/users/admin/login.php');
    await page.locator('[name=email]').fill(fixture.email); await page.locator('[name=password]').fill(fixture.password);
    await page.getByRole('button',{name:/Sign in/}).click(); await page.waitForURL('**/newdashboard.php');
    const screens=[['access','/users/admin/access.php?role=employee&id='+access.employee_id],['settings','/users/admin/settings.php'],['calendar','/users/admin/calendar.php?month=2026-10'],['reports','/users/admin/reports.php'],['help',help.path],['activity','/users/admin/activity.php'],['billing','/users/admin/billing.php'],['billing-usd','/users/admin/billing.php?currency=USD'],['record-detail',attachments.path],['event-form','/users/admin/workspace.php?module=events&action=new']];
    for (const [name,url] of screens) {
      await page.setViewportSize({width:1280,height:900});
      const response=await page.goto('http://127.0.0.1:8000'+url);
      check(response.status()===200,name+' desktop route works');
      check(await page.locator('h1').count()===1,name+' has one page heading');
      check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),name+' fits desktop');
      await page.screenshot({path:path.join(root,'work/test-results/'+name+'-desktop.png'),fullPage:true,animations:'disabled'});
      check(await page.locator('.logout-button').isInViewport?.() ?? await page.locator('.logout-button').evaluate(el=>el.getBoundingClientRect().bottom<=innerHeight),name+' desktop sign-out remains reachable');
      await page.setViewportSize({width:390,height:844});
      check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),name+' fits mobile');
      await page.screenshot({path:path.join(root,'work/test-results/'+name+'-mobile.png'),fullPage:true,animations:'disabled'});
      await page.getByRole('button',{name:'Open navigation'}).click();
      const settings=page.locator('#sidebar').getByRole('link',{name:'Settings',exact:true});
      await settings.scrollIntoViewIfNeeded();
      check(await settings.isVisible(),name+' mobile navigation reaches later links');
      await page.keyboard.press('Escape');
      check(await page.getByRole('button',{name:'Open navigation'}).evaluate(el=>el===document.activeElement),name+' menu restores focus');
    }
    // This isolated helper supplies fake provider credentials. External traffic
    // is blocked by makeContext; never submit a checkout from this test.
    await page.goto('http://127.0.0.1:8002/users/admin/billing.php?currency=USD');
    await page.locator('#months-project').selectOption('2');
    const dollarTotal = await page.locator('[data-billing-total]').innerText();
    check(dollarTotal==='USD 4.41','USD total matches source cart conversion: '+dollarTotal+'; browser errors: '+errors.join(','));
    await page.locator('#months-strategy').selectOption('1');
    check(await page.locator('[data-billing-total]').innerText()==='USD 6.62','USD combined cart rounds once');
    check(await page.getByRole('button',{name:'Continue to Flutterwave'}).isEnabled(),'Dollar checkout names Flutterwave');
    await page.goto('http://127.0.0.1:8002/users/admin/billing.php?currency=NGN');
    await page.locator('#months-project').selectOption('2');
    check(await page.locator('[data-billing-total]').innerText()==='NGN 7,000.00','NGN total preserves source prices');
    check(await page.getByRole('button',{name:'Continue to Paystack'}).isEnabled(),'Naira checkout names Paystack');
    await page.goto('http://127.0.0.1:8000/users/admin/access.php?role=employee&id='+access.employee_id);
    await page.locator('#access-leads').selectOption('view');
    await page.locator('#access-events').selectOption('view');
    await page.getByRole('button',{name:'Save access',exact:true}).click();
    check(await page.locator('.toast-message').innerText().then(s=>s.includes('Tool access updated.')),'Access form saves through browser');
    const staffContext=await makeContext(); const colleague=staffContext.page;
    await colleague.setViewportSize({width:390,height:844});
    await colleague.goto('http://127.0.0.1:8000/users/employees/login.php');
    await colleague.locator('[name=email]').fill(staff.email); await colleague.locator('[name=password]').fill(staff.password); await colleague.locator('[name=workspace]').fill(staff.workspace);
    await colleague.getByRole('button',{name:/Sign in/}).click(); await colleague.waitForURL('**/users/employees/workspace.php');
    await colleague.goto('http://127.0.0.1:8000/users/admin/modules.php');
    check(await colleague.getByRole('link',{name:/Leads/}).count()===1,'Staff tools show granted CRM area');
    check(await colleague.getByRole('link',{name:/Expenses/}).count()===0,'Staff tools omit unassigned finance area');
    await colleague.getByRole('link',{name:/Leads/}).click();
    await colleague.waitForLoadState('load');
    check(await colleague.locator('a[href*="action=edit"],a[href*="action=new"]').count()===0,'Staff view-only page omits edit actions');
    await colleague.screenshot({path:path.join(root,'work/test-results/staff-tool-mobile.png'),fullPage:true,animations:'disabled'});
    const overflow = await colleague.evaluate(()=>({width:innerWidth,scroll:document.documentElement.scrollWidth,panels:[...document.querySelectorAll('.suite-body,main,.panel,.table-wrap,header,.suite-sidebar')].map(e=>({tag:e.tagName,cls:e.className,width:e.getBoundingClientRect().width,right:e.getBoundingClientRect().right,overflow:getComputedStyle(e).overflow}))}));
    check(await colleague.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Staff records fit mobile: '+JSON.stringify(overflow));
    await colleague.screenshot({path:path.join(root,'work/test-results/staff-tool-mobile.png'),fullPage:true,animations:'disabled'});
    await colleague.goto('http://127.0.0.1:8000'+help.path);
    check(await colleague.locator('main').innerText().then(s=>s.includes('Access reviewed.')),'Staff sees internal help response');
    check(await colleague.locator('[name=state]').count()===0,'Staff help has no owner-only status control');
    check(errors.length===0,'New workspace journeys have no uncaught browser errors');
    await staffContext.ctx.close(); await ctx.close();
  } finally {
    await browser.close();
    fs.writeFileSync(reportPath,JSON.stringify({checks,passed:checks.filter(x=>x.passed).length,total:checks.length},null,2));
    console.log(JSON.stringify({passed:checks.filter(x=>x.passed).length,total:checks.length}));
  }
})().catch(e=>{console.error(e.message);process.exitCode=1;});

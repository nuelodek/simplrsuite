<?php
// Read-only local rules; no provider calls or entitlement mutations.
require_once __DIR__.'/../app/recruiting.php';
if (PHP_SAPI !== 'cli' || config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log') exit(2);
$db=database();$checks=[];
function recruiting_check(bool $ok,string $name):void { global $checks;$checks[]=['name'=>$name,'passed'=>$ok];if(!$ok)throw new RuntimeException($name); }
$before=getenv('BILLING_ENFORCE');
try {
 putenv('BILLING_ENFORCE=false');
 $job=['company_id'=>-2147483648,'status'=>'published','closes_at'=>null];
 recruiting_check(job_accepts_applications($db,$job),'Published role accepts applications without billing enforcement');
 recruiting_check(!job_accepts_applications($db,[...$job,'status'=>'draft']),'Draft never accepts applications');
 recruiting_check(!job_accepts_applications($db,[...$job,'status'=>'closed']),'Closed role never accepts applications');
 recruiting_check(!job_accepts_applications($db,[...$job,'closes_at'=>'2000-01-01 00:00:00']),'Past UTC deadline closes applications');
 recruiting_check(job_accepts_applications($db,[...$job,'closes_at'=>'2099-12-31 23:59:59']),'Future deadline accepts applications');
 putenv('BILLING_ENFORCE=true');
 recruiting_check(!job_accepts_applications($db,$job),'Missing recruiting entitlement closes applications when billing enforced');
 recruiting_check(in_array('jobs',billing_catalog()['recruiting']['modules'],true)&&in_array('applications',billing_catalog()['recruiting']['modules'],true),'One recruiting entitlement covers jobs and applications');
 $fields=['title'=>'Synthetic rule check','location'=>'Lagos','workplace'=>'remote','employment_type'=>'contract','description'=>'Synthetic description','requirements'=>'','status'=>'published','closing_date'=>'2099-01-01','timezone'=>'Africa/Lagos'];
 [$values,$errors]=validate_record($db,-2147483648,module_config('jobs'),$fields);
 recruiting_check(!$errors && $values['closes_at']==='2099-01-01 22:59:59','Closing date converts from company timezone to UTC');
 [$values,$errors]=validate_record($db,-2147483648,module_config('jobs'),[...$fields,'closing_date'=>'not-a-date']);
 recruiting_check(isset($errors['closing_date']),'Malformed deadline returns validation error');
 [$values,$errors]=validate_record($db,-2147483648,module_config('jobs'),[...$fields,'timezone'=>'invalid']);
 recruiting_check(isset($errors['timezone']),'Malformed timezone returns validation error');
 recruiting_check(interview_datetime_utc('2099-05-12T14:30','Africa/Lagos')==='2099-05-12 13:30:00','Interview local time converts to UTC');
 recruiting_check(interview_datetime_utc('2000-01-01T10:00','Africa/Lagos')===null,'Past interview time is rejected');
 recruiting_check(interview_datetime_utc('2099-02-30T14:30','Africa/Lagos')===null,'Invalid interview calendar date is rejected');
 recruiting_check(interview_datetime_utc('2099-05-12T14:30','Not/AZone')===null,'Unknown interview timezone is rejected');
} finally {
 putenv($before===false?'BILLING_ENFORCE':'BILLING_ENFORCE='.$before);
 $passed=count(array_filter($checks,fn($x)=>$x['passed']));
 file_put_contents(SIMPLR_ROOT.'/work/test-results/recruiting-policy.json',json_encode(['passed'=>$passed,'total'=>count($checks),'checks'=>$checks],JSON_PRETTY_PRINT));
 echo 'Recruiting policy: '.$passed.'/'.count($checks).PHP_EOL;
}

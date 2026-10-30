<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/neurons.php';
require_once __DIR__ . '/../app/http.php';

$checks = 0;
$check = static function (bool $condition, string $description) use (&$checks): void {
    if (!$condition) { fwrite(STDERR,'Failed: ' . $description . PHP_EOL); exit(1); }
    $checks++;
};
$throws = static function (callable $call, string $description) use ($check): void {
    try { $call(); } catch (Throwable) { $check(true,$description); return; }
    $check(false,$description);
};

$now = new DateTimeImmutable('2026-10-30 14:00:00',new DateTimeZone('UTC'));
$daily = ['schedule_type'=>'daily','schedule_time'=>'16:00:00','timezone'=>'Africa/Lagos'];
$check(neuron_next_run_utc($daily,$now) === '2026-10-30 15:00:00','daily time uses the workspace timezone');
$daily['schedule_time'] = '14:00:00';
$check(neuron_next_run_utc($daily,$now) === '2026-10-31 13:00:00','daily schedule advances to tomorrow when today has passed');
$weekly = ['schedule_type'=>'weekly','schedule_time'=>'16:00:00','weekday'=>5,'timezone'=>'Africa/Lagos'];
$check(neuron_next_run_utc($weekly,$now) === '2026-10-30 15:00:00','weekly schedule selects the requested weekday');
$weekly['weekday'] = 4;
$check(neuron_next_run_utc($weekly,$now) === '2026-11-05 15:00:00','weekly schedule advances a week when its weekday has passed');

$once = neuron_create_schedule('once','2026-10-30T16:30','','','Africa/Lagos',$now);
$check($once['next_run_at'] === '2026-10-30 15:30:00','one-time local date converts to UTC');
$throws(static fn()=>neuron_create_schedule('once','2026-10-30T14:00','','','UTC',$now),'past one-time schedules are rejected');
$throws(static fn()=>neuron_create_schedule('daily','','25:00','','UTC',$now),'invalid recurring times are rejected');
$throws(static fn()=>neuron_create_schedule('weekly','','09:00','8','UTC',$now),'invalid weekdays are rejected');
$throws(static fn()=>neuron_next_run_utc(['schedule_type'=>'monthly','schedule_time'=>'09:00:00','timezone'=>'UTC'],$now),'unsupported recurring schedules are rejected');

$check(array_keys(neuron_types()) === ['workload_pulse','hiring_pulse'],'only implemented read-only actions are exposed');
$check(public_route('/users/admin/neurons.php') === 'users/admin/neurons.php','owner neuron page is registered as an explicit route');
$check(public_route('/users/admin/nuerons.php') === null,'legacy prototype dispatcher is not routed');
$check(public_route('/users/admin/createnuerons.php') === null,'legacy prototype write handler is not routed');

echo "Neuron unit checks passed: $checks\n";

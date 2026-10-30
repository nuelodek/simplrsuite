<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/neurons.php';
require_once __DIR__ . '/../app/jobs.php';

$db = database();
if (!job_begin($db,'neurons')) {
    echo json_encode(['skipped'=>'The previous run is still working.']) . PHP_EOL;
    exit;
}

$result = ['succeeded'=>0,'failed'=>0,'deferred'=>0];
$due = db_rows($db,"SELECT id FROM workspace_neurons WHERE status = 'active' AND next_run_at IS NOT NULL AND next_run_at <= UTC_TIMESTAMP() ORDER BY next_run_at,id LIMIT 100");
foreach ($due as $candidate) {
    $neuronId = (int)$candidate['id'];
    $startedAt = new DateTimeImmutable('now',new DateTimeZone('UTC'));
    $transactionOpen = false;
    $scheduledFor = null;
    try {
        $db->begin_transaction();
        $transactionOpen = true;
        $neuron = db_one($db,"SELECT * FROM workspace_neurons WHERE id = ? AND status = 'active' AND next_run_at IS NOT NULL AND next_run_at <= UTC_TIMESTAMP() FOR UPDATE",[$neuronId]);
        if (!$neuron) {
            $db->commit();
            $transactionOpen = false;
            continue;
        }
        $scheduledFor = (string)$neuron['next_run_at'];
        db_run($db,"INSERT INTO workspace_neuron_runs (neuron_id,company_id,scheduled_for,started_at,status) VALUES (?,?,?,?,'running')",[
            $neuronId,(int)$neuron['company_id'],$scheduledFor,$startedAt->format('Y-m-d H:i:s'),
        ]);
        $runId = (int)$db->insert_id;
        $data = neuron_run_action($db,(int)$neuron['company_id'],(string)$neuron['neuron_type'],$startedAt);
        $finishedAt = new DateTimeImmutable('now',new DateTimeZone('UTC'));
        $oneTime = $neuron['schedule_type'] === 'once';
        $nextRun = $oneTime ? null : neuron_next_run_utc($neuron,$finishedAt);
        db_run($db,"UPDATE workspace_neuron_runs SET finished_at = ?, status = 'succeeded', result_json = ? WHERE id = ? AND company_id = ?",[
            $finishedAt->format('Y-m-d H:i:s'),json_encode($data,JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),$runId,(int)$neuron['company_id'],
        ]);
        db_run($db,'UPDATE workspace_neurons SET status = ?, next_run_at = ?, last_run_at = ? WHERE id = ? AND company_id = ?',[
            $oneTime ? 'completed' : 'active',$nextRun,$finishedAt->format('Y-m-d H:i:s'),$neuronId,(int)$neuron['company_id'],
        ]);
        $db->commit();
        $transactionOpen = false;
        $result['succeeded']++;
    } catch (Throwable $error) {
        if ($transactionOpen) $db->rollback();
        error_log('Simplr: neuron run failed for automation ' . $neuronId . ' (' . get_class($error) . ')');
        if ($scheduledFor === null) throw $error;
        $db->begin_transaction();
        $transactionOpen = true;
        $current = db_one($db,"SELECT * FROM workspace_neurons WHERE id = ? AND status = 'active' AND next_run_at = ? FOR UPDATE",[$neuronId,$scheduledFor]);
        if (!$current) {
            $db->commit();
            $transactionOpen = false;
            continue;
        }
        $failedAt = new DateTimeImmutable('now',new DateTimeZone('UTC'));
        $scheduledFor = (string)$current['next_run_at'];
        db_run($db,"INSERT INTO workspace_neuron_runs (neuron_id,company_id,scheduled_for,started_at,finished_at,status,error_message) VALUES (?,?,?,?,?,'failed',?)",[
            $neuronId,(int)$current['company_id'],$scheduledFor,$startedAt->format('Y-m-d H:i:s'),$failedAt->format('Y-m-d H:i:s'),'Automation failed. Check the server log for details.',
        ]);
        $oneTime = $current['schedule_type'] === 'once';
        $nextStatus = $oneTime ? 'completed' : 'active';
        $nextRun = null;
        if (!$oneTime) {
            try { $nextRun = neuron_next_run_utc($current,$failedAt); }
            catch (Throwable $scheduleError) {
                $nextStatus = 'paused';
                error_log('Simplr: invalid schedule paused for automation ' . $neuronId . ' (' . get_class($scheduleError) . ')');
            }
        }
        db_run($db,'UPDATE workspace_neurons SET status = ?, next_run_at = ?, last_run_at = ? WHERE id = ? AND company_id = ?',[
            $nextStatus,$nextRun,$failedAt->format('Y-m-d H:i:s'),$neuronId,(int)$current['company_id'],
        ]);
        $db->commit();
        $transactionOpen = false;
        $result['failed']++;
    }
}
if (count($due) === 100) $result['deferred'] = 1;
job_finish($db,$result);
echo json_encode($result,JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . PHP_EOL;

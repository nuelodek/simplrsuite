# Scheduled jobs: install, monitor, recover

Simplr Suite has three command-line jobs. None runs from a web request, so nothing
happens until a scheduler starts them. This page is the operator runbook. It has not
been run on a production or staging host yet; the local checks at the end are the
only evidence so far.

| Job | Cadence | What it does | Outside contact |
| --- | --- | --- | --- |
| `jobs/notifications.php` | every minute | Closes query response windows, expires warnings, sends task due-today and overdue reminders, retries the email outbox, sends daily digests. | SMTP (your mail provider) |
| `jobs/github-sync.php` | every 15 minutes (`GITHUB_SYNC_MINUTES`) | Syncs open issues into tasks for repositories with automatic sync on. Reads GitHub only; never writes to it. | api.github.com |
| `jobs/neurons.php` | every minute | Runs due, owner-configured read-only workload and hiring snapshots. | None |

All three jobs:

- take a MySQL named lock (`GET_LOCK`), so a slow run makes the next start exit with
  `{"skipped": ...}` instead of running twice;
- record a heartbeat in the `scheduler_runs` table (migration `031_scheduler_runs.sql`):
  start time, finish time, status, duration, a counts-only summary and the number of
  failed runs in a row;
- print one JSON line of counts per run. Errors go to the PHP error log by exception
  class only. No tokens, passwords, email addresses or invitation links are printed.

The neurons job also serializes runs through the scheduler lock. Each automation and
its run history is scoped to one company. The initial actions only count records;
they do not edit workspace data, send email, or run user-supplied code. Configure
and monitor automations at **Neurons** in the workspace.

`jobs/github-sync.php` also stops picking up repositories one minute before its next
run is due. Repositories it did not reach go first next time, reported as `deferred`.

## Before you install

1. Apply migrations: `php scripts/migrate.php`.
2. Put production settings in the application's `.env` file. Cron does not see
   variables set only in the web server (Apache `SetEnv`, Plesk PHP settings). The
   jobs read the same `.env` as the site, so the cron user needs read access to it.
3. Choose the account the jobs run as. Use the site's own system user, or a dedicated
   one, with read access to the code and `.env` and write access to `storage/`
   only. Never use root.
4. Set `APP_URL` to the real HTTPS origin. Alert emails link to it.

## Install (Linux cron)

Edit the crontab of the service user (`crontab -u simplr -e`), replacing
`/srv/simplrsuite` with the install path and `/var/log/simplr` with a directory that
user can write:

```cron
# Alerts, reminders and the email outbox
* * * * *    cd /srv/simplrsuite && timeout 10m php jobs/notifications.php >> /var/log/simplr/notifications.log 2>&1
# GitHub issue sync (keep in step with GITHUB_SYNC_MINUTES)
*/15 * * * * cd /srv/simplrsuite && timeout 14m php jobs/github-sync.php >> /var/log/simplr/github-sync.log 2>&1
# Scheduled workspace automations
* * * * *    cd /srv/simplrsuite && timeout 5m php jobs/neurons.php >> /var/log/simplr/neurons.log 2>&1
# Scheduler health check: alerts when a job is late, stuck, failing or never ran
*/5 * * * *  cd /srv/simplrsuite && php scripts/scheduler_status.php --quiet || <your alert command>
# Wider health check: database, migrations, email outbox, payments, disk, backups, errors
*/5 * * * *  cd /srv/simplrsuite && php scripts/ops_check.php --quiet || <your alert command>
```

`<your alert command>` is whatever pages the on-call person, for example a mail
command to an operations mailbox or a webhook call. `--quiet` prints nothing when
everything is healthy (both checks) and prints the failing jobs otherwise, so the output can go into the alert. Rotate the job log files with logrotate
(weekly, keep eight weeks is a reasonable start).

**Plesk:** in *Websites & Domains → Scheduled Tasks*, add five tasks of type *Run a
PHP script* with the same scripts, arguments and schedules, running as the
subscription's system user. Turn on Plesk's notification for failed tasks on the
health-check task.

## Monitor

```bash
php scripts/scheduler_status.php          # table, exit code 0 = healthy, 1 = needs attention
php scripts/scheduler_status.php --json   # full report for dashboards
```

| State | Meaning | What to do |
| --- | --- | --- |
| `OK` | Started recently and the last run finished. | Nothing. |
| `NEVER` | No run has ever been recorded. | The cron line is missing or runs as a user that cannot reach the database. Run the job by hand as the service user and read its output. |
| `LATE` | No start in three intervals (at least ten minutes). | Cron stopped, the host was down, or the lock is held by a hung run. Check `crontab -l`, the job log and MySQL `SELECT IS_USED_LOCK('simplr_job_notifications')`. |
| `STUCK` | Started more than 30 minutes ago and never finished. | The process was killed (for example by `timeout`) or hangs on a network call. Check the log; the next run clears it. |
| `FAILED` | The last run stopped on an error. | Read the PHP error log around `last_started_at`. Database, SMTP, or automation errors are common causes. A clean run resets the failure count. |

Per-repository GitHub problems (revoked access, deleted repository) do not fail the
job. They show on the repository in the GitHub page and in the run's `failed` count.

## Recover

- The jobs are safe to re-run. Reminders are deduplicated per task per local day,
  query and warning transitions lock their row and check status first, the email
  outbox retries failed messages up to three times within two days, and GitHub sync
  updates the task already linked to each issue instead of creating a new one.
- After an outage, run each job once by hand as the service user; the backlog clears
  in that run.
- To pause an automation, the owner chooses **Pause** on the Neurons page. A one-time
  automation is marked complete after its run; **Run now** can queue it again.
- To pause GitHub sync for one company, the owner turns *Auto-sync* off on the
  repository. To pause it for everyone, comment out its cron line.

## Verify on staging (owner approval needed)

1. Install all five scheduled tasks on staging with SMTP pointed at an approved test
   mailbox and GitHub connected to a test account.
2. After five minutes `php scripts/scheduler_status.php` shows all three jobs `OK`.
3. Comment out the notifications line. Within 15 minutes the health check exits 1
   and the alert arrives. Restore the line; the next check is healthy.
4. Record the commands, times and alert in the release evidence.

## Local evidence

The existing notifications and GitHub jobs were checked on 27 Oct 2026 against an
isolated local database with `MAIL_TRANSPORT=log`. The neurons job was added later
and still needs an integration run after migration `039_neurons.sql` is applied in
an isolated local environment.

- The two existing jobs were `NEVER` before their first run and `OK` after running.
- The GitHub job used `--company` for a synthetic workspace with no linked
  repositories, so it made no network calls.
- A second notifications run while the first held the lock printed `skipped`.
- An uncaught exception was recorded as `FAILED` with the exception class only
  ("Stopped by RuntimeException"); the next clean run returned to `OK`.
- The GitHub job's scheduled path, assignment and reminders are covered against the
  local GitHub stub by `scripts/github_smoke_test.py`.

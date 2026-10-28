# Release, rollback and migration runbook

How to take a reviewed commit to production and back out safely. Nothing here has
been run against a real host yet; steps marked **host** need the production or
staging server and the owner's go-ahead. Paths assume the app lives in
`/srv/simplrsuite` and runs as the `simplr` user. Adjust to the real host.

## Roles

| Role | Does |
| --- | --- |
| Owner | Approves the release, the maintenance window and any rollback. |
| Operator | Runs the steps below and records times and outputs. |
| Reviewer | Reads the diff and the test results before the window; not the operator. |

## 1. Prepare (any machine with the local test setup)

1. Choose the commit. Record `git rev-parse HEAD` and tag it, for example
   `git tag release-2026-11-01`. Never release uncommitted work.
2. Reviewer reads `git diff <previous-release-tag>..HEAD`, including every new file
   in `database/migrations/`.
3. Run the suites against the isolated local database (`APP_ENV=local`,
   `MAIL_TRANSPORT=log`), one agent at a time:
   `python scripts/consolidation_smoke_test.py`, `access_smoke_test.py`,
   `attachments_smoke_test.py`, `tenant_isolation_test.py`, `recruiting_smoke_test.py`,
   `help_smoke_test.py`, `operations_smoke_test.py`, `email_scope_test.py`,
   `marketing_smoke_test.py`, `github_smoke_test.py` (with the stub, see its header),
   the `php scripts/*_unit_test.php` and payment simulation tests,
   `node scripts/consolidation_browser_test.cjs` and `recruiting_browser_test.cjs`.
   `access_smoke_test.py` needs the Apache helper on port 8001.
4. `php scripts/preflight.php` passes locally.
5. List the migrations this release adds. For each, note whether it only adds
   tables or nullable/defaulted columns (safe for the previous code) or changes
   existing data (needs a restore to undo).

## 2. Staging rehearsal (host)

1. Restore last night's production backup into the staging database, or use
   synthetic data. Never point staging at the production database.
2. Deploy the tagged commit, then `php scripts/migrate.php`. Time it.
3. `php scripts/preflight.php --production` shows no new failures
   (15 of 27 locally on 27 Oct; the gaps are owner configuration, listed by the script).
4. Smoke check by hand: owner sign-in, create and move a task, upload and download a
   file, open Alerts, staff sign-in, a public job page.
5. `php scripts/scheduler_status.php` is healthy after five minutes of cron.

## 3. Production window (host)

1. Announce the window to customers if downtime is expected.
2. Pause the scheduler: comment out the three cron lines from
   `deploy/scheduler.md`. Wait for running jobs to finish
   (`php scripts/scheduler_status.php` shows no `running` job).
3. Back up everything that must be restored together, to storage off this host:
   ```bash
   mysqldump --single-transaction --routines --triggers --default-character-set=utf8mb4 -u <user> -p <database> > simplr-<tag>.sql
   tar -czf simplr-<tag>-storage.tar.gz -C /srv/simplrsuite storage
   ```
   `APP_KEY` (in the private `.env`) decrypts stored integration tokens. Make sure a
   copy of the `.env` is held in the password manager or secret store, not in the
   backup archive.
4. Check the backup: the SQL file ends with `-- Dump completed`, and the archive
   lists `storage/attachments`.
5. Deploy the tagged commit (`git fetch --tags && git checkout release-…`, or upload
   the build). Keep the previous release directory for rollback.
6. `php scripts/migrate.php`. If it stops, do not mark a migration as applied by
   hand; go to "A migration failed" below.
7. `curl -fsS https://<APP_URL>/health.php` returns `{"status":"ok"}`.
8. `php scripts/preflight.php --production`: no new failures compared with staging.
9. Repeat the hand smoke checks from staging.
10. Re-enable the cron lines. After five minutes `php scripts/scheduler_status.php`
    exits 0.
11. Watch the PHP error log and the job logs for 30 minutes, then close the window
    and record: tag, start and end times, migration times, checks, who approved.

## Rollback or forward fix

There are no "down" migrations; MySQL schema changes are not transactional.

- **The new code misbehaves and its migrations only added tables or defaulted
  columns:** switch back to the previous release directory. The old code ignores
  the additions. Leave the schema as it is and fix forward.
- **A migration failed part-way:** keep the site in maintenance and preserve the
  pre-upgrade backup. Compare the affected schema and data with every statement
  in the migration; do not assume that `migrate.php` will record a partially
  applied file. This runner records a migration only after the entire SQL file
  succeeds, so rerunning a partial file may fail on statements already applied.
  The release owner must choose restore-from-backup or an explicitly reviewed,
  migration-specific repair. For a manual repair, first verify which statements
  and backfills completed, apply only the missing intended changes, compare the
  resulting schema/data with the complete migration's expected outcome, and
  only then insert that exact filename into `schema_migrations`. Record the
  repair and verification before resuming the runner. Never mark a migration
  applied merely to silence an error. The synthetic recovery drill
  `php scripts/migration_failure_recovery_test.php` documents the tested
  migration-027 duplicate-column case; it is not a substitute for a backup or
  inspection of a real database.
- **Data was changed wrongly:** restore. Stop cron, restore the SQL dump into an
  **empty** database, restore `storage/` from the same backup, point `.env` at the
  restored database, redeploy the previous release, check health, re-enable cron.
  Anything written after the backup is lost; the owner decides whether that is
  acceptable before the restore starts.

`scripts/backup_restore_drill.php` rehearses a restore against the local development
database only; it refuses to run anywhere else.

## Sign-off checklist

- [ ] Commit tagged, diff reviewed by someone other than the operator
- [ ] Local suites and preflight pass (results saved without secrets)
- [ ] Staging rehearsal done, migration time measured
- [ ] Backup taken, checked and stored off the host
- [ ] Owner approved the window and the rollback plan
- [ ] Post-release checks and scheduler health recorded

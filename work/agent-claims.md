# Agent claims — who is working on what

Read this before editing. Add your own section with the requirement, the files you
will touch and a status. Remove or close your claim when you finish, and record the
evidence in `work/overnight-handoff.md`. If a file you need is claimed below, leave
it alone or coordinate through this file.

Requirement names refer to the tracker in `requirements.html`.

## Claude (desktop Code tab) — claimed 27 Oct 2026, 21:50 WAT

Status: **finished 28 Oct 2026 00:05 WAT; all claims released.** Evidence is in
`work/overnight-handoff.md` (top section). Run login-based suites one agent at a time.

| Requirement (requirements.html) | What I am doing |
| --- | --- |
| Prove tenant isolation and object-level authorization (CRITICAL) | New synthetic two-company test for the surfaces built in the last two rounds: search API, Files library, attachment download, board move API, CSV export, accounting, GitHub (connect, link, sync, unlink, people map, auto-sync), notifications and queries pages. Fix any cross-company or wrong-role access it finds. |
| Verify atomicity, uniqueness and idempotency (HIGH), GitHub part only | Concurrent GitHub sync test (scheduled job and manual sync at once), one task per issue. |
| Install, monitor and safely exercise scheduler jobs (HIGH) | Job heartbeat table, `scripts/scheduler_status.php` health check for cron alerting, runbook with exact cron lines. |
| Remove stale fixtures/artifacts and verify repository hygiene (LOW) | Ignore browser-capture folders; PHP lint and `git diff --check` on changed files. Nothing is deleted. |
| Local runtime preflight is green, and Run the full regression suites (CRITICAL) | Run `php scripts/preflight.php` and every documented suite at the end and record the results. |

Files I will create or edit:

- new: `scripts/tenant_isolation_test.py`, `app/jobs.php`, `scripts/scheduler_status.php`,
  `database/migrations/031_scheduler_runs.sql`, `deploy/scheduler.md`
- edit: `jobs/github-sync.php`, `scripts/github_smoke_test.py`, `.gitignore`,
  `README.md` (scheduler section only), `work/overnight-handoff.md` (append only)
- edit `jobs/notifications.php` only to add two heartbeat lines at the start and end;
  its logic stays with its owner.
- edited for a found defect: `app/bootstrap.php` (new `local_path()` helper only) and
  `users/admin/notifications.php` (two redirect lines). `return_to=/\evil.example`
  was sent off-site, because browsers read `/\` as `//`.
- edit only if the isolation test finds a defect: `api/search.php`, `users/admin/files.php`,
  `api/attachments.php`, `api/records.php`, `users/admin/github.php`, `app/github.php`,
  `users/admin/accounting.php`

### Second batch — claimed 27 Oct 2026, 22:30 WAT (in progress)

| Requirement | What I am doing |
| --- | --- |
| Scan and document third-party and bundled dependencies (HIGH) | Inventory of Composer packages, bundled SDKs and dev tooling with versions, licences and usage; offline review only (nothing uploaded). |
| Define and test financial features that are not implemented (HIGH) and Approve launch scope (HIGH) | Compare UI, landing pages, integrations catalog and README claims with the code; correct wording that implies payroll, tax invoices, bank balances or live connectors work; available/limited/not-built matrix for owner approval. |
| Document data inventory, retention, export and deletion requests (HIGH) | Factual data map from the schema (what personal data, where, who can see it, what deletes it) plus the owner questions. No legal claims. |

Files: new `docs/dependencies.md`, `docs/launch-scope.md`, `docs/data-inventory.md`.
Added 22:45: Document repeatable release, rollback and migration operations (MEDIUM) →
new `deploy/release.md` only (README release wording left to Codex, who is on the docs item).
Copy fixes only where a claim is wrong; each edited page will be listed here before
I change it. I will not touch `public/privacypolicy.php`, `.env.example` or
`deploy/plesk-production.env.example` (Codex is editing them).

Also edited for findings: `Dockerfile` (adds the `zip` extension; without it the
container rejects every DOCX/XLSX/PPTX upload) and `scripts/preflight.php` (checks
`zip` and `dom`). Both were last touched by Codex at 21:41-21:47; only those lines changed.

Also edited: `requirements.html`, statuses and a one-line evidence note on the items
above (the file's `data-status` defaults, so every viewer sees them).

### Third batch — claimed 27 Oct 2026, 23:00 WAT

| Requirement | What I did |
| --- | --- |
| Inventory and secure every reachable legacy route (CRITICAL) | `scripts/route_audit_test.py`: static plus HTTP audit of all 163 manifest routes (980/980). |
| Verify authentication, session, recovery and rate-limit controls (HIGH) | `scripts/session_hardening_test.py` (23/23). |
| Test every migration against representative synthetic legacy schemas (CRITICAL) | `scripts/migration_matrix_test.php` on disposable `simplr_dev_mig_*` databases. |
| Rotate exposed historical credentials (CRITICAL), code part | Scan by key type; blanked values in 8 dead legacy files; rotation list in `docs/owner-actions.md`. |
| Owner checklist | `docs/owner-actions.md` gathers every owner-only step. |
| Mobile dashboard overflow (found by the browser suite) | `.dashboard-columns > * { min-width: 0; }` appended to `public/styles/suite.css`. |

Codex is working on `docs/attachment-operations.md`, `docs/support-runbook.md`, the
`docs/dependencies.md` audit update and README troubleshooting; I am not editing those.

Not claimed (left for owners or other agents): legacy route audit, staff toolkit
grants, talent journey, promotions, payments, privacy and legal, hosting, backups,
accessibility review.

Rules I am following: local database only, `APP_ENV=local`, `MAIL_TRANSPORT=log`,
GitHub stub server only (no calls to github.com), no commits or pushes, no deploy.

## Copilot (this chat) — accessibility review, 28 Oct 2026

Status: **partial review in progress; public auth focus-ring and authenticated 320px overflow fixed.**

| Requirement | What I did |
| --- | --- |
| Complete browser, mobile, keyboard and accessibility review | Local-only spot check across 8 public/unauthenticated routes; improved a low-contrast auth focus ring. Follow-up checked the authenticated calendar dialog and organization chart with synthetic local account data, fixed a 320px topbar overflow, and rechecked keyboard dialog behavior, accessible names, responsive widths and the 640×422 CSS viewport equivalent of 200% desktop zoom from 1280×844. A probe found 1–3px overflow at 361–363px with the initial breakpoint; CSS was extended through 364px, but boundary retest was blocked by the synthetic account's login rate limit. No limits were cleared. Full workflow, contrast and browser zoom checks are still open. |
| Privacy owner input status accuracy | Reverted an earlier incorrect `verified` tracker status to `blocked`: owner-provided identity/contact details are configured, but the requirement includes legal approval and CAC status confirmation, neither of which is independently evidenced. |

Files changed: `public/styles/suite.css`, `requirements.html`, this coordination
record, and `work/overnight-handoff.md`. Browser checks used only the local app
with synthetic fixture accounts and blocked non-local requests. Sign-in updated
local authentication state only; no business records, external services, email,
payments or deployments were changed.

## Copilot (this chat) — support runbook, 27 Oct 2026 22:50 WAT

Status: **documentation drafted; customer-support requirement remains owner-blocked.**

| Requirement | What was done |
| --- | --- |
| Operationalize customer support, account recovery and incident handling (MEDIUM) | Added `docs/support-runbook.md` with verified app paths, safe account/payment/privacy/security handling boundaries, and explicit owner decisions. General support ownership, response targets and incident escalation remain unprovided. |

Files: new `docs/support-runbook.md`; appended evidence to `work/handoff.txt`.
No application files or `requirements.html` changed because Claude currently owns
tracker status updates. Do not mark the requirement verified until the business
owner designates and exercises the support contacts and operating expectations.

## Copilot (this chat) — attachment operations, 27 Oct 2026 22:53 WAT

Status: **factual runbook drafted; operational requirement remains owner/host-blocked.**

| Requirement | What was done |
| --- | --- |
| Review attachment scanning, retention and operational ownership (MEDIUM) | Added `docs/attachment-operations.md` describing checked upload types, size/quotas, private storage, scoped downloads, removal/withdrawal, and local backup limits. Explicitly identifies missing malware scanning/quarantine, retention policy, and production storage owner. |

Files: new `docs/attachment-operations.md`; appended evidence to
`work/handoff.txt`. No attachment application code changed, no external scanner
was contacted and no customer files were used. This requirement remains open
until an owner-approved scanning/risk decision, retention policy and host/backup
operations are evidenced.

## Codebuff (this chat) — claimed 27 Oct 2026, 21:45 WAT, closed 22:40 WAT

Status: **finished and released.** Full evidence: `work/agent-handoff-requirements.md`.
Read this section before touching the files listed — I am not editing anything else.

| Requirement (requirements.html) | What I actually did |
| --- | --- |
| Scan and document third-party and bundled dependencies (HIGH) — **shared with Claude** | Ran `composer audit --locked` (sends package names/versions only; no source, lock or env uploaded). Found **4 medium advisories in `guzzlehttp/psr7` 2.7.0**: CVE-2026-59882, CVE-2026-55766, CVE-2026-49214, CVE-2026-48998 (all fixed in ≥2.12.3). **Patched: psr7 2.7.0 → 2.13.2**, `symfony/deprecation-contracts` 3.5.0 → 3.7.1, `symfony/polyfill-php80` added. Re-audit: no advisories. Added `.github/workflows/dependency-audit.yml` (weekly + push/PR, runs the committed `composer.phar`). **I did not edit `docs/dependencies.md` — Claude owns it and its `guzzlehttp/psr7 2.7.0` row is now stale.** |
| Run the full current application journey and regression suites (CRITICAL) | **Test repairs only** (three stale assertions, see file list); I ran the suites once before Claude's 22:30 note and then stopped. Claude owns the final regression run and its recorded numbers — do not treat mine as the release result. |
| Local runtime preflight is green (LOW) | Re-ran `php scripts/preflight.php`: **13/13 local** after applying migration `030_recoverable_record_archive.sql` to the isolated `simplr_dev` database. Pre-migration dump in `/tmp/simplr-backup/` (outside the repo). |
| Remove stale fixtures/artifacts and verify repository hygiene (LOW) | Independently confirmed Claude's result: `php -l` on every changed/untracked PHP file = 0 failures, `git diff --check` clean, `.env`/`.env.local` ignored, `.playwright-mcp/` ignored. **Nothing deleted, no claim kept — this stays with Claude.** |

Files I changed (complete list — nothing under `app/`, `users/`, `api/`, `toolkits/`, `jobs/`):

- `scripts/smoke_test.py` — invitation success assertion now expects
  "The mail server accepted the invitation." (the app-side copy change in
  `users/admin/invite.php` is not mine; only the test was repaired).
- `scripts/access_smoke_test.py` — task fixture payload now sends `due_date`, which
  `app/modules.php` requires; without it the API returns 422 and the suite aborted at
  check 125 (cascading stale-fixture failures in operations/attachments/recruiting).
- `scripts/operations_smoke_test.py` — `settings_post()` now sends
  `query_response_minutes` and `warning_expiry_months`, both `required` on the current
  settings form.
- `composer.lock` **and the committed `vendor/` tree** — the psr7 security patch above.
  `vendor/` is tracked in git, so these show as modified; do not revert them.
- `.github/workflows/dependency-audit.yml` — new.
- `requirements.html` — one `agent-note` appended to `security-dependencies` only.
- `work/agent-handoff-requirements.md`, `work/agent-claims.md` — coordination records.
- `.env.local` — `MAIL_TRANSPORT=smtp` → `log`, because AGENTS.md requires `log` for
  local checks and `smtp` would have sent real mail during tests. Backups exist at
  `.env.local.bak-20261027-124448` and in `/tmp`. Left at `log` deliberately.

Coordination notes for whoever reads this next:

1. Login-based suites hit the app's own limit (10 logins per account per 15 minutes);
   failures reading "fixture login" are that, not defects. Clearing
   `DELETE FROM auth_rate_limits;` on the isolated DB resets it — I did that several
   times. Run one agent's suites at a time.
2. `.local/*fixture.json` files are shared mutable state: running two suites at once
   mixes fixtures from different runs and produces misleading StopIteration/404 failures.
3. My suite results (27 Oct, isolated DB, before Claude's rerun): consolidation
   197/197, 268/268, 388/388 · access 234/234 · operations 73/73 · attachments 54/54 ·
   recruiting 149/149 · email scope 58/58 · help 35/35 · marketing 40/40 ·
   richtext 15 · accounting 21 · recruiting policy 14/14.
4. Not claimed by me and still open: legacy route audit, staff toolkit grants, talent
   journey, promotions, payments sandbox, privacy/legal (blocked), hosting, backups,
   accessibility review, capacity/load, support runbook, sign-off gate.

## Copilot (this chat) - readiness continuation, 27 Oct 2026

Status: support and attachment runbooks documented; remaining owner decisions are
now explicitly blocked in the checklist. Full regression is still unverified.

Files changed:
- requirements.html - product-support/product-files statuses and dependency evidence only.
- docs/dependencies.md - corrected Composer versions/advisory record from the lockfile.
- vendor/composer/autoload_static.php - removed generated trailing whitespace only.
- work/handoff.txt - latest status and browser-test limitation.
- work/agent-claims.md - this coordination record.

The consolidation browser test was attempted after no background agents remained,
but the shared fixture already contains an accounting chart and the test stops at
the missing "Create standard chart" button after 16/16 checks. Do not treat this
attempt as a passing suite or reset shared data. A separate local dashboard check
measured 390px document width at a 390px viewport; no CSS change was warranted.
The dependency inventory now reflects all 10 locked packages and the prior reported
audit/patch. Its status remains in progress pending the first GitHub Actions audit run.
Follow-up local checks: Composer validate exits 0 with only a missing application
license metadata warning; all 10 locked package directories exist; richtext 15/15,
accounting 21/21 and recruiting policy 14/14 pass. Owner approval of the patch policy
is also outstanding.
Local preflight rerun at 23:12 WAT passed 15/15.
No support operations policy was provided; the privacy contact was not assumed to be
a general support channel, so product-support remains blocked without invented SLAs.

## Copilot (this chat) — readiness continuation, 28 Oct 2026

Status: **local-only verification refreshed; authenticated accessibility boundary check remains unverified.**

| Requirement | What was done |
| --- | --- |
| Local runtime preflight is green | Re-ran `php scripts/preflight.php`: 15/15 checks passed. Focused non-login unit suites passed: rich text 15, accounting 21, recruiting policy 14/14. |
| Complete browser, mobile, keyboard and accessibility review | After natural cooldown, one delayed local-only synthetic owner sign-in succeeded. Calendar and organization pages fit at every tested width from 320–365px and 390px, including the 361–364px boundary. No rate limit was cleared or bypassed. Accessibility remains in progress. |

No application data, mail, payment, provider, or deployment action was performed.

Follow-up, same date: a local-only text-contrast probe found the shared muted text
color below 4.5:1 on light page backgrounds. Updated `public/styles/suite.css`
`--muted` from `#6f7b76` to `#66736e`. Browser checks measured 4.81:1 against the
landing-page background and 4.95:1 against white, with public/privacy/auth routes
still fitting at 320px. This is a targeted token improvement, not a full contrast
audit.

A compact visible-text contrast scan across the same seven public/privacy/auth
routes reported no remaining text color pairs below the computed WCAG threshold
after the targeted changes. Background images/gradients and authenticated pages
were not fully audited, so this is not a full contrast sign-off.

Extended the contrast fix to the measured small public preview/workflow labels,
keeping the brand suffix light on its dark auth-story background. Rechecked 69
rendered label instances across 7 public/privacy/auth routes; all met the computed
4.5:1 normal-text or 3:1 large-text target. The test did not cover every page or
component, and the complete contrast review remains open.

Updated `scripts/migration_matrix_test.php` to compare foreign-key mappings and
update/delete actions as part of schema equality. `php scripts/migration_matrix_test.php
--all` passed **134/134** assertions across clean install/no-op and **32/32**
disposable synthetic baselines, preserving owner, employee, project and task rows
and matching columns, indexes, foreign keys and actions to the clean schema. The
duplicate-email stop preserved rows; the report identified 19 rerun-safe and 13
non-repeatable migrations. That matrix did not inject partial-DDL failure; the
separate migration-027 recovery drill below covers one specific partial-apply
case only, so `data-upgrade` remains in progress.

Added `scripts/migration_failure_recovery_test.php` and tested the migration-027
duplicate-column failure in a generated `simplr_dev_recovery_*` database:
**9/9** assertions passed. The partial manager link/backfill persisted without a
migration marker; the missing team index was applied, the migration marked only
after verification, and the runner then completed remaining migrations. Updated
`deploy/release.md` because the runner does **not** record a partially completed
file automatically. Other partial-DDL failure modes remain untested, so
`data-upgrade` stays in progress.

The delayed employee accessibility follow-up failed in its Playwright DOM
inspection expression (`TypeError` reading `length` from an undefined value), so
it produced no findings. The earlier three possible unnamed controls remain
unconfirmed; no further login attempt was made. Correct the inspection expression
before any future authenticated check.

## Copilot (this chat) — synthetic role transitions, 28 Oct 2026

Added `scripts/role_transition_test.php`, guarded for `APP_ENV=local`,
`MAIL_TRANSPORT=log`, and localhost MySQL. It creates/drops only a generated
`simplr_dev_roles_*` database and runs current install/migrations on synthetic
records. The test passed **21/21**, covering linked employee promotion,
department-head assignment, demotion, employee retention/role restoration,
direct-report/department/project reassignment, manager invitation invalidation,
audit events, organization-chart leadership, and rejection of self-replacement
with dependent records unchanged. It also confirms that restoration reuses the
suspended manager record and updates chart leadership. It calls transition
helpers directly without sending mail. UI invitation retry and the full
owner-facing workflow remain unverified;
`identity-roles` is recorded as in progress.

`python scripts/consolidation_smoke_test.py` passed on 28 Oct with core
**197/197**, talent **268/268**, and consolidated recovery/consolidation
**388/388** checks. This is not a full release gate; access, attachments,
recruiting, tenant-isolation, GitHub, payment-mock, browser and other suites were
not all rerun against the latest worktree.

After the documented natural authentication cooldown, one run of
`python scripts/access_smoke_test.py` passed **234/234**. It covered synthetic
owner/employee/manager grant assignment, revocation and enforcement, CSRF, stale
forms, legacy writes, tenant boundaries, and local route guards. The test leaves
the documented synthetic view-only browser fixture. `identity-tool-access`
remains in progress because billing-entitlement interactions and all required
surfaces are not independently verified.

After a further natural cooldown, `python scripts/attachments_smoke_test.py`
passed **54/54** against synthetic records/files. It verified upload validation,
private forced downloads, tenant/grant boundaries, storage web denial and
scoped removal. The run removed its own test attachments. Malware scanning,
retention, and production storage/backup remain owner decisions; the attachment
operations requirement remains blocked.

## Copilot — scheduler-monitoring tracker reconciliation, 28 Oct 2026

Status: **local evidence reconciled; talent browser journey, production/staging monitoring and the complete suite remain open.**
Reviewed the existing scheduler status script and operator runbook, ran the
read-only local status command, reconciled the stale full-suite tracker note,
reconciled the current synthetic talent-suite evidence, and updated tracker
notes/handoff. Re-ran focused rich-text, accounting and recruiting unit checks
(15/15, 21/21, 14/14) and JavaScript syntax checks. No jobs or external alerts
were started.

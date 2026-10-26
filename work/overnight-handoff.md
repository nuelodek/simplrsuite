# Simplr Suite overnight continuation

User request: inspect and improve the whole application overnight so it becomes
presentable and suitable for launch. User explicitly chose **all modules equally**.
No user authorization for subagents, deployment, external messages, or real
financial transactions. Continue coding autonomously without routine questions.

Historical heartbeat: `simplr-suite-overnight-improvements`, now **paused**.
Overnight authorization ended at 07:00 Africa/Lagos on 23 October 2026. Subsequent
work below was explicitly requested by the human. Do not reactivate the automation
unless requested. Do not mark the application production-ready without evidence.

## Latest human-requested continuation: recruiting (24 October 2026)

The human asked “hi, please next.” Completed the next concrete journey in the
canonical workspace: employer job publishing → talent application → employer
review/stage update → candidate tracking/withdrawal. The overnight automation
remains paused. This was a daytime continuation, not a resumed heartbeat.

- Read the reference recruiter's mock dashboards, `displaytalent.php`, talent
  submission/profile upload code and `ziprecruiter.php`. These contain useful
  professional-profile fields and workflow categories, but no complete working
  application journey. No reference files or credentials were copied/changed.
- Migration `016_recruiting_journeys.sql` adds job postings and application
  submission metadata. The existing `applications` register remains the employer's
  source of truth, including manual legacy records. Applied only to isolated local
  `simplr_dev`; apply through the migration runner on a deployment host later.
- Job drafts are private; publishing creates a public role with location, work
  arrangement, employment type, requirements and optional timezone-aware deadline.
  Closing/deadline expiry stops applications. Recruiting billing entitlement
  covers jobs and review; inactive subscriptions stop applications when enforced.
- `/jobs/` is searchable. `/users/talents/apply.php?job=...` preserves the intended
  job through sign-in/account verification and profile completion. Applicants
  confirm sharing an immutable profile/contact-email snapshot and optional PDF.
  Directory visibility stays independent. Server derives account/company/job/stage;
  duplicate submissions, stale job/profile versions and forged identity are blocked.
- PDF resumes reuse private `record_attachments` storage, validation and workspace
  quota. Only the applicant and authorized employer reviewers can download them;
  other talents/tenants and public storage URLs cannot. Employers cannot replace
  or delete applicant-submitted resumes through the generic attachment API.
- `/users/admin/application.php?id=...` supports tenant-scoped review with view/edit
  grants, CSRF, stale-version protection and audited stages. Generic applicant edits
  redirect to review; generic API cannot overwrite submitted identity/profile.
- `/users/talents/applications.php` shows only that account's applications/stages.
  Withdrawal requires confirmation and a current version, closes review and revokes
  future employer resume downloads. It does not erase submitted records or copies
  already downloaded. One application per job; no reapplication after withdrawal.
- Talent/business navigation and promotional copy connect these journeys. Shared
  forms remain responsive; role details, candidate consent and employer review
  inspected at mobile width. Legal privacy notice remains a draft for owner review.

Verification in this continuation:
- `python scripts/recruiting_smoke_test.py`: **107/107**.
- `node scripts/recruiting_browser_test.cjs`: **35/35**, including browser job
  publication, return after login, submission, employer stage change, candidate
  status, withdrawal and six screens at 1280/390px. Test jobs are closed afterward.
- `php scripts/recruiting_policy_test.php`: **10/10**, deadlines/timezone/billing
  gates with no provider calls or entitlement mutations.
- Existing core + talent + recovery consolidation: **387/387** (run 1792799420).
  Existing fixture files restored after this run to preserve other regression links.
- Attachments: **54/54**; marketing: **40/40**. Local preflight **13/13**.
- Reviewed PHP syntax: **186/186 files**; recruiting JavaScript/Python syntax and
  `git diff --check` passed. Scoped Apache verification helper stopped; preview on
  port 8000 still answers health, jobs, talent landing and employee login with 200.
- All **285** inventoried `simplr24` reference files remain unchanged.
- Screenshots and detailed JSON reports are under `work/test-results/recruiting-*`.
  Tests use synthetic example.test accounts, inert PDFs, local logged mail and no
  external services. No provider transactions, deployment, real email or messaging.

Host blocker: C: repeatedly fluctuated between zero and about 35 MB free. One
interrupted save truncated `app/modules.php`; recovered it from the pre-consolidation
archive plus exact applied patch history, then reran the core journeys successfully.
Subsequent saves used temporary files plus replacement. No empty reviewed PHP files
remain. Asked the user to free at least 1 GB while preserving both Simplr folders.
Three completed, disposable `simplr_restore_20261023_*` verification databases were
removed to recover space; their backup archives/reports remain in `.local/backup-drills`.
The pre-migration restore drill verified 88 tables, 1,165 rows and one attachment.
No actual workspace database or user record was deleted. Avoid another restore drill
or large dependency installation until disk space is available.

Remaining launch dependencies from the previous checkpoint still apply: production
hosting/DNS/HTTPS, real email configuration, approved business/privacy details and
provider activation with test-mode end-to-end evidence. Direct talent messaging,
interview scheduling, resume parsing/scanning and a retention/deletion workflow are
not implemented. File downloads explicitly disclose that antivirus scanning is absent.
Do not call the entire product production-ready. Main preview remains on port 8000.

## Active daytime continuation (23 October 2026)

### Latest verified checkpoint: Paystack + Flutterwave

The human added **Flutterwave for USD alongside Paystack for NGN**, then explicitly
directed us back to the `simplr24` pricing reference. Do not ask for new USD prices:
`users/admin/subpro.php` in that read-only checkout defines NGN prices, conversion
and provider selection. The server now carries over those NGN prices and the
0.00063 USD/NGN fallback as a clearly displayed, configurable fixed conversion
(`BILLING_USD_RATE`), not a live FX quote. No source credentials were copied/called.
See `work/payment-launch.md` for the exact source lines and activation checklist.
All 285 inventoried historical source files were rechecked and remain unchanged.

- Completed both hosted-checkout adapters, server quotes, provider-bound order
  snapshots/nonces/transaction IDs, verification, webhooks and manual renewal.
  NGN/Paystack and USD/Flutterwave tabs show their totals and connection status.
  Both providers extend the same test/live toolkit entitlement. Original cart
  conversion rounds once: two project months USD 4.41, not USD 4.42. Source Sales
  and CRM offers are combined because they expose the same current record tools.
- Flutterwave v3 verifies the stored tx_ref and decimal USD amount; v3 webhooks
  authenticate verif-hash. Paystack authenticates raw-body HMAC-SHA512. Provider
  event payloads and browser success parameters cannot grant access themselves.
- Migration 012 adds private PDF/PNG/JPEG record attachments and view details.
  Owner/editor uploads/removals and viewer downloads enforce tenant/tool access,
  CSRF and private storage. Server upload limit is reflected in the form. Limits:
  at most 5 MB/file, 20 files/record, 100 MB/company. Forced downloads, no public
  URLs or inline execution. No malware scanning or talent resume upload workflow.
- Migrations 013 and 015 correct global CRM and manager-directory email uniqueness
  to company scope, preserving existing records. Optional CRM emails now store
  NULL so multiple records can omit email. Migration 014 namespaces provider IDs.
  All migrations through 015 applied only to the isolated local database.
- Fixed mobile table overflow caused by absolutely positioned accessible labels
  escaping their scroll container. Fixed corrupted dash/separator text. Browser
  screenshots reviewed for dollar billing, record details and staff mobile table.
- Added read-only scripts/preflight.php and a guarded local backup/restore drill.
  Docker gets required extensions, upload settings and writable storage; Docker
  is unavailable here, so no image build is claimed. Storage must be persistent.
- Privacy notice is visibly a draft with actual configured review date, rather
  than silently showing today's date. Legal name/address/contact answer remains
  pending. The legal prose has not been certified or approved.

Latest passing evidence in work/test-results/:

| Check | Result |
| --- | --- |
| Consolidation/core/talent/recovery | 387/387 |
| Staff permissions/legacy routes/dev + Apache boundary | 217/217 |
| Settings/calendar/reports/legacy record compatibility | 73/73 |
| Internal help/tenant/author controls | 35/35 |
| Private attachments | 54/54 |
| Company-scoped CRM/manager emails and optional blank emails | 58/58 |
| Paystack simulated provider checks | 56/56 |
| Flutterwave simulated provider checks | 46/46 |
| Both provider HTTP guards + concurrent settlement | 40/40 |
| New workspace desktop/mobile/browser billing totals | 83/83 |
| Public/account/consolidation browser journeys | 42/42 |
| Three audience marketing routes | 40/40 |
| Changed/new PHP lint | 185 files, no failures |
| JavaScript syntax / git diff --check | passed |
| Local preflight | 13/13 |

Provider tests use CLI fake transports and obvious fake-key helper configuration;
**no actual provider calls, charges or real emails**. Test helper router is now
reproducible at scripts/payment_test_router.php. Earlier failures were real global
email scoping and table overflow, plus fixture reuse, missing beta relationship
fixtures, and the helper router failing to return the static-file sentinel.
All were fixed and the affected suites rerun. During the final core rerun, the
three existing synthetic browser fixture JSONs were checkpointed/restored so
module fixtures remained aligned. A concurrent payment fixture run collided with
that refresh; both CLI payment suites and HTTP checks were rerun sequentially
afterward and passed. Do not count preliminary failed runs as successes.

The final backup drill restored into new database
`simplr_restore_20261023_213000_64e71c`: **88 tables, 1,165 rows, matching extended
table checksums and one attachment hash**, source unchanged. Private dump/copies
are in `.local/backup-drills/20261023_213000_64e71c`; temporary client credentials
were removed. Earlier restored test database is also retained. No real database
was overwritten or dropped. This is not a production backup/retention service.

Production preflight intentionally reports 11 missing checks: production mode,
four real HTTPS origins, SMTP, live Paystack, live Flutterwave, paid editing
enforcement, privacy owner details and reviewed notice. Production work also
needs key rotation, DNS/certificates, real provider test checkout/webhook delivery,
Flutterwave USD collection approval, rate/pricing review, customer entitlement
migration, monitoring/reconciliation and production restore/retention procedures.
Automatic refunds/disputes/recurring billing and talent applications/messaging
remain unavailable. Unreviewed legacy routes are blocked, not fully implemented.

Main preview remains http://127.0.0.1:8000. The overnight automation remains
**PAUSED**. Work is uncommitted and unpushed, including all prior user changes.
No deployment, external messages, source-tree writes or subagents.
Owned test helpers on ports 8001 and 8002 were stopped after verification; the
main preview health endpoint still returns HTTP 200. Consolidated evidence is
`work/test-results/daytime-verification.json`.

### Earlier daytime implementation notes (superseded where stated above)

The human explicitly asked to continue after the morning report, then said
"lets go on". This is active authorized coding; the automation stays PAUSED.
All earlier changes are preserved. No deploy, real email, payment or subagents.

- Added migration 007: role/company/staff/module view/edit grants and workspace
  audit records. Admin Staff access page, immediate permission checks on API and
  forms, transactional grant recheck on record writes, stale access-form checks.
  Old tools_assigned rows stay intact and are shown for explicit permission review.
  Admin-only directory/invitation/permission operations remain restricted.
- Replaced unsafe legacy task/team/lead updates and data lookups with shared
  permission/tenant/CSRF/validation/version checks. Staff toolkit URLs now open
  permission-aware modern tools; read-only forms hide add/edit controls.
- Added explicit app/routes.php manifest and shared app/http.php dispatcher for
  PHP development and Apache. Non-reviewed PHP URLs, provider demos, direct uploads,
  source and private paths return 404 without executing. Existing files are retained
  in the repo. This CONTAINS unreviewed routes; it does not implement those features.
- API supports versioned updates; developer guide updated for staff permissions.
- 387/387 prior consolidation checks passed after the access/routing changes,
  evidence consolidation-smoke-1792747982.json. New access suite passed 217/217,
  checking grant/revoke/view/edit across two companies and roles, old mutation
  routes, private paths, GET/POST on dev PHP AND isolated local Apache.
- Native Apache test process started using .local/apache-test.conf on loopback
  8001, independent of normal AMPPS config. Syntax passed and routes tested.
  Main preview remains loopback 8000. PHP health is healthy. No production host
  changes. Docker missing mbstring was fixed but image not built yet.
- Migrations 008 workspace settings and 009 event timezone applied locally.
  Added settings, events CRUD/calendar/ICS, module-permission-scoped reports/CSV.
  Currency is explicit, no exchange conversion. Event timezones persist; old
  events require choosing timezone before ICS. CSV escapes formula-like cells.
- Legacy task numeric project references now resolve safely in staff views/forms;
  edits sync the two historical project-manager columns. Compatibility fixture
  temporarily simulates an old missing-FK schema on a single local connection,
  then restores the synthetic task reference in finally. No real data touched.
- Operations tests passed 73/73; help author/tenant/CSRF/reply/version workflow
  passed 35/35; new workspace Chromium responsive/navigation checks passed 57/57.
  Earlier failures were fixture reuse (unchanged settings, duplicate blank lead
  email, existing grants) and simulating an old schema with the new project FK.
  Test fixtures were repaired without weakening application guards.
- Added migration 010 and /users/help.php internal owner/staff request/reply flow,
  owner resolution, private per-author scope, pagination, audit activity UI.
- Human chose no payments briefly, then explicitly corrected: "launch with
  Paystack". Paystack IS now launch scope. Privacy contact/legal details question
  is still pending. Do not reuse old credentials or make real provider calls.
- Paystack implementation underway: migration 011, server-owned NGN toolkit price
  catalog from original subpro.php (Sales & CRM combined to avoid duplicate sale),
  hosted checkout, manual prepaid 1–12 month renewals, server verification, HMAC
  webhook, deduped nonce/order/transaction IDs, per-mode entitlements. Reading and
  exports remain available after expiry; editing enforced when BILLING_ENFORCE=true.
  Main local environment remains unconfigured/disabled; no config values changed.
- Paystack CLI provider simulation passed 51/51; no real provider traffic or
  charges. HTTP boundary, concurrent settlement, docs and browser checks pending.
- Browser screenshot review found captures caught mobile sidebar mid-transition;
  screenshot calls now disable animations. Re-capture and visually recheck.
- Safe attachments and broader recruitment/messaging remain unfinished. Do not
  claim entire release ready. Provider test checkout on a deployed HTTPS host,
  newly issued keys, DNS, SMTP, privacy details, backups remain launch inputs.

## Final checkpoint (23 October 2026, 08:00 Lagos)

### Later user-authorized consolidation and promotional pages

The human subsequently requested selective consolidation from `simplr24`, then
three promotional entry points. This was ordinary active work after the overnight
window; the overnight automation remains paused. `simplrsuite` is now explicitly
the canonical workspace in `AGENTS.md` and README. See `work/consolidation.md` for
the complete source comparison, port decisions, and host configuration.

- Source found at `C:/Program Files/Ampps/www/simplr24`, HEAD `43079aa`, with
  pre-existing uncommitted changes. All 285 inventoried source files remain
  byte-identical after this work; no source credentials or customer data imported.
- Reused and adapted the toast component, four-role recovery entry point (adding
  secure employee/manager password recovery), and developer documentation route.
  Most source application code already existed in this repository. Unsafe or
  unfinished subscription/payment and dashboard experiments were not imported.
- Applied migration `006_staff_password_recovery.sql` locally. Staff recovery
  explicitly binds role, company, directory identity and auth record, and checks
  the current password stamp. Duplicate emails across companies/roles are tested.
- Added `/landing/business/`, `/landing/talents/`, `/landing/work/` with separate
  messaging, colors and account journeys. Root hostname routing supports
  `business.simplr.com`, `talents.simplr.com`, `work.simplr.com`. The Work audience
  is employees/managers. Host routing uses an exact allowlist; private `/work/`
  remains blocked. HTTPS/DNS/hosting was not changed.
- Added optional APP_BUSINESS_URL / APP_TALENT_URL / APP_WORK_URL origins for
  promotional and account-mail links. Existing APP_URL remains the mail fallback.
- Final verification: **387/387** consolidation HTTP checks (includes core and
  talent), **40/40** marketing routing/configuration checks, **42/42** actual
  Chromium browser checks, **19 changed PHP files** linted without errors. Node
  syntax and `git diff --check` passed. Source hashes and pre-consolidation ZIP
  validity passed. Final HTTP evidence: `consolidation-smoke-1792743433.json`;
  browser: `consolidation-browser.json`; marketing: `marketing-smoke.json`.
- Actual desktop/mobile screenshots for every audience are in
  `work/test-results/landing-{business,talents,work}-{desktop,mobile}.png`.
  Headless Chromium automation worked despite the earlier CUA initialization
  failure. All five newly viewed audience screenshots showed coherent layout;
  automated desktop/mobile overflow and action visibility checks passed for all.
- Changes remain local and uncommitted, including all preserved overnight work.
  Launch blockers listed below still apply. Staff password recovery is now done;
  broader staff toolkit permissions and production integrations are still open.

The overnight window has ended. Automation `simplr-suite-overnight-improvements`
was successfully updated to **PAUSED**. Do not resume overnight work on later
heartbeats. Final verification of the active talent change was completed after the
deadline. A new human request asks for dashboard images; use the saved screenshots
below, clearly labelled as the overnight test build.

Changes since the earlier checkpoint:

- Replaced broken talent authentication with shared styled signup/sign-in,
  email verification, resend, recovery, single-use hashed one-hour tokens,
  password hashing, rate limits, CSRF, session rotation and reset invalidation.
  Client-submitted account IDs and verified status cannot select identity.
- Added migration `005_talent_workspace.sql`, applied to local `simplr_dev`.
  New `talent_portfolios` are account-owned, private by default, and published
  only by explicit selection. Historical `talents` data remains untouched and
  is not automatically imported or published. This is a compatibility gap to
  address with owner-reviewed migration, not data loss.
- Added professional profile editor, safe HTTPS portfolio links, escaped output,
  input limits and optimistic edit conflict handling. Unsupported uploads return
  an explicit validation error. Applications, resumes and messaging remain
  unavailable in the new talent workspace and are labelled accordingly.
- Added `/users/admin/talent-directory.php`: verified admins only, opted-in
  verified talent only, search and pagination, no sign-in email disclosure.
  Making a profile private immediately removes it from results. Recruiting
  navigation points to this directory. Talent navigation is role-specific.
- Replaced old talent login/signup/verification/profile submission/list routes
  with adapters to the new paths. Other legacy talent routes remain unreviewed.
- Talent sign-out returns to talent sign-in. Corrected an encoding artifact in
  the generic workspace search placeholder after screenshot inspection.

Verification:

- `python scripts/talent_smoke_test.py`: **267/267 passed**. Includes a fresh
  197-check core run, then talent signup/verification/login/reset, role isolation,
  account ownership, private/listed/unlisted visibility, email privacy, XSS,
  CSRF, stale update, unsafe link and unsupported upload rejection, logout.
- Standalone core run also passed **197/197** before talent checks. All test
  fixtures are synthetic `example.test` accounts with local file mail only.
- Full PHP syntax check completed: **379 files, zero errors**, stored in
  `work/test-results/php-lint.json`. The count includes some bundled PHP sources
  outside excluded dependency directories. Final logout and workspace edits also
  passed individual lint. Node syntax and `git diff --check` passed; Git reported
  only normal LF/CRLF conversion notices. Latest HTTP evidence is
  `work/test-results/talent-smoke-1792737079.json` (**267/267**).
- Existing screenshot evidence: `work/test-results/dashboard-desktop.png`
  and `projects-mobile.png`. Desktop contains a deliberately escaped XSS test
  project title; mobile screenshot predates the search-placeholder text fix.
  These are actual browser captures, not generated mockups. Earlier desktop and
  mobile interaction checks passed. New talent browser visual QA is still pending:
  CUA failed to initialize with `failed to write kernel assets ... path specified`.
- Local PHP and dedicated MySQL services were restarted after they were found
  stopped. Existing datadir and persisted credentials reused, with NO init-file
  reset and no data import. `/health.php` returned `status: ok`.

Launch blockers remain: direct legacy endpoints and uploads are incompletely
audited; full staff toolkit permissions are missing; payment/banking/payroll
providers are unconfigured/unverified; committed credentials need rotation;
legacy data compatibility, privacy contact placeholders and deployment/backup
validation remain. This is a materially improved local build, **not a certified
production-ready release**. No deployment, push, real mail or financial operation
was performed. Changes remain in the shared working tree on the branch below.

## Earlier checkpoint (23 October 2026, about 00:40 Lagos)

Repository: `C:/Users/HP/simplrsuite`; branch `codex/production-readiness`, created
from initial `main` commit `2dbaf7c`. Changes are not committed or pushed. Original
user-owned untracked `start-server.ps1` was preserved and its PHP invocation was
updated to use `router.php`. `.env` remains on disk, but its removal from tracking
is staged and it is now ignored. Never print its values. Do not reset these changes.

## Implemented

- New shared `app/bootstrap.php`, database/auth/mail helpers, environment config,
  escaping, CSRF, safe exceptions, session rotation, rate limits, typed tenant
  checks, hashed expiring single-use account tokens. Production errors do not
  disclose connection details. Existing three DB adapters use this configuration.
- Rebuilt public `index.php`, admin account screens and overview with forest green,
  lime, and neutral design system in `public/styles/suite.css`; local SVG icons,
  responsive shell, real metrics, honest empty states. Marketing preview explicitly
  says it is illustrative. No random balance/KPI widgets or false testimonials.
- `app/modules.php` and `users/admin/workspace.php`: 21 allowlisted record areas:
  projects, tasks, departments, managers, employees, teams, leads, customers,
  contacts, accounts, deals, invoice register, expenses, ledger, inventory,
  applications, incidents, compliance, products, office assets, objectives.
  Search/pagination, create/edit, input validation, relationship tenant checks,
  HTML escaping, and optimistic version conflict protection. No deletes added.
- `/api/records.php` GET paginated reads and POST creates, verified admin cookie
  session plus CSRF required; it is NOT a public API-key/OAuth integration.
- Admin signup ignores client-supplied status/company IDs; verification and reset
  use real routes, expiry, token hashes, and mail abstraction. Session password
  fingerprint invalidates sessions after password reset.
- Staff invitations (`users/admin/invite.php`, `users/accept-invitation.php`),
  hashed 24-hour tokens, user-chosen passwords, recipient binding, no plaintext
  password email. Employee/manager sign-ins repaired and restyled. Staff workspace
  shows their own assigned work; employees may update only assigned task status.
  Manager login no longer overwrites every department's manager. Shared admin
  session helper now rejects employee/manager identities.
- 17 legacy create endpoints migrated through `app/legacy-record.php` (admin
  department/project/task/team/manager/employee, legacy invite/create-directory,
  finance add endpoints, CRM adds, project toolkit adds). Company IDs come from
  session; validation and CSRF shared. Uploads explicitly rejected here until
  secure attachments are implemented. These adapters return JSON.
- 20 old read/page URLs redirect to the protected modern record pages (admin
  employee/team/task lists, CRM pages, finance lists/pages, project dashboard/list).
  These currently require admin; employee toolkit access remains to implement.
- Integration availability page accurately distinguishes local email log,
  configured but unverified SMTP, and unavailable payments/payroll/banking.
- Schema-only `database/schema.sql` derived from historical dump with NO records;
  fresh-install compatibility fixes for MySQL 8.4 (`DEFAULT (curdate())`, unique
  referenced identifiers). Migrations 001 accounts, 002 unique business email,
  003 objectives, 004 staff invitations. CLI migrator refuses installing into a
  nonempty DB and does not merge/delete duplicate accounts.
- Router/Apache private-file restrictions, upload execution deny rules, Docker
  mysqli/pdo_mysql and Apache configuration, `.dockerignore`, health endpoint,
  new 404 and responsive mobile route, README with setup/limitations.

## Verification so far

- Fresh schema installed successfully on isolated MySQL 8.4.5. No old data dump
  imported. Original records, integrations, and live environments untouched.
- Latest `python scripts/smoke_test.py`: **197/197 checks passed**. Exercises all
  21 record areas, signup/verification/reset, session rotation/invalidation,
  tenant isolation, XSS escaping, invalid dates/amounts, cross-company references,
  optimistic update conflict, staff invitations and one-time use, staff vs admin
  permissions, task updates, private paths/assets, legacy reader/write adapters.
- Earlier 142/177-check passes retained; one intermediate failure was MySQL
  collation in manager ID comparison. Fixed using binary comparison for numeric
  legacy ID fallback. Latest pass includes the fix.
- Initial full lint: 190 original PHP files passed. Later 203 passed. A new full
  lint is running in exec session 16398 as of this checkpoint; collect its result.
  It writes `work/test-results/php-lint.json`. JS node check passed. `git diff
  --check` follows in the same command; inspect final output.
- Actual in-app browser desktop sign-in and dashboard inspected. At 390x844,
  verified no whole-page horizontal overflow, navigation opens/closes, and editing
  a synthetic project persisted. Caught/fixed an overbroad router rule that blocked
  `/public/scripts/suite.js`; static asset assertions were added to smoke tests.
  Mobile sidebar now uses inert when closed, close/backdrop controls, Escape,
  focus restoration and keyboard trapping. CSS contrast/readability improved.
- Screenshots in ignored `work/test-results/`; dashboard-desktop.png is an earlier
  screenshot with synthetic XSS test title. Browser record id=1 was renamed via UI
  to `Workspace launch` afterward. New screenshot should be captured if needed.

## Local services and test workflow

- PHP on `127.0.0.1:8000`, process launched hidden with working directory repo and
  `php -S 127.0.0.1:8000 -t . router.php`. Logs `.local/php-*.log`. Log request URLs
  contain test tokens; don't print raw logs without redacting token query values.
- Dedicated MySQL process on loopback `127.0.0.1:3307`, binary
  `C:/tools/mysql/mysql-8.4.5-winx64/bin/mysqld.exe`, datadir `.local/mysql`.
  Generated root password in ignored `.env.local`, APP_ENV local, MAIL_TRANSPORT
  log, DB_DATABASE **simplr_dev**. Do NOT print credentials. This process is NOT
  an existing live database. Initial partial install DB simplr_local is unused.
- MySQL launch had local credential reset via `.local/reset-root.sql` (private);
  the current process used `--init-file` for that reset. Don't change other MySQL
  instances or restart with another application's data dir.
- Mail test outbox `storage/mail/*.json`; test fixtures and auth secrets ignored.
- Smoke tests create synthetic companies, emails at example.test, and records;
  retain them for review. Rate limits are real: repeated rapid runs can reach
  signup/IP limits, so wait the 15-minute window instead of weakening app limits.
- Latest successful test browser credentials are `.local/browser-fixture.json`
  (synthetic only). Browser tab id 1 in IAB/browser 2 is signed into an earlier
  synthetic workspace. If session persists, reuse. Viewport was 390x844; reset
  before finishing. CUA docs must be reloaded after context reset per tool rules.
- No Docker executable available; container build/Apache rules not runtime tested.

## Next priorities — significant work remains

1. **Complete old-route coverage.** Many original independent PHP endpoints are
   still unaudited and may have no authorization, tenant checks, CSRF, output
   escaping, or trusted provider verification. New UI protections do not fix those
   direct URLs. Enumerate remaining routes and migrate/secure them, especially
   update/delete, team/tool assignment, fetch/read, uploads, messages and payment
   callbacks. Do not falsely claim the whole app is safe. Keep supported behavior
   and replace obsolete sample pages with coherent routes; don't merely hide links.
2. **Talent journey** is still old/broken (`users/talents/db.php` is absent; legacy
   signup/verify paths disagree with schema). Implement equivalent modern secure
   talent signup/login/verification/recovery/profile with correct ownership,
   validated safe uploads and clear recruitment visibility. Do not expose private
   applicant data globally or invent hiring decisions. Public site links there.
3. **Assigned toolkit permissions for staff** are unfinished. New employee/manager
   workspace is a scoped task/project view; existing full toolkit capability has
   not been migrated into it. Admin adapters require admin and do not yet support
   the prior assigned-tool model. Add explicit assignment/entitlement checks and
   appropriate role-scoped toolkit access; test with multiple companies and roles.
4. **Providers:** old bank/payment/Marqeta/Flutterwave/Paystack scripts still exist
   with hardcoded/sample credentials and unsafe/unverified flows. Never execute
   live requests using these keys. Remove hardcoded credentials, use server-side
   configuration, documented APIs, timeouts and honest failures; verify amounts,
   currency, identity, signatures, idempotency and tenant binding. Use provider
   test mode/mocks without transactions. Full activation needs real owner/provider
   setup. Old committed credentials need rotation before launch. Do not merely
   rename placeholders as functioning integrations.
5. Remaining functional coverage: events/meetings/calendar, messages, support,
   subscriptions/entitlements, reports, safe attachments, actual payroll processing,
   profile/settings and workspace currency/timezone. Invoice register is NOT a
   legally compliant invoice generator. Old records and relationships must remain
   usable. Existing legacy project/task references mix numeric and unique IDs;
   new paths consistently use projects.unique_id for tasks.project_id.
6. UI/data polish: field/grid responsiveness, useful list filters (legacy filtered
   task pages currently redirect to the all-tasks list), clearer money currency,
   accessible statuses, complete talent/staff/mobile browser verification. Add
   assets/behavior checks when changing routes. New shell is intentionally smaller
   than the old 4,000-line placeholder-heavy admin page; fill real feature gaps.
7. Deployment verification: test migrations against realistic *synthetic* old
   schemas/records, Docker/Apache rules (not yet run), PHP extensions for providers,
   private-file restrictions on actual hosting, monitoring, backup/restore and
   upgrade instructions. Don't deploy/push without appropriate authorization.
8. Existing privacy page has address/phone placeholders and a JavaScript-generated
   fake last-updated date. Do not invent legal business/contact details. Flag exact
   owner inputs for the morning and improve template handling honestly.

Preserve all completed work, test evidence, user files, and source history. No
routine approval questions. Report material new failures or real external
dependencies. Morning report must distinguish local verification from production
readiness and provider activation.

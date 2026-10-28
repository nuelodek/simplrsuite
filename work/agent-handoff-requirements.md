# Agent handoff — requirements.html work (Codebuff)

**Owner agent:** Buffy / Codebuff. Started 27 Oct 2026 21:45 WAT, closed 22:40 WAT.
**Purpose:** a durable record of what this agent did and did not do, so concurrent
agents do not duplicate work, overwrite each other's edits, or double-count evidence.
The coordination index for the whole tree is `work/agent-claims.md`; this file is my
detail. Read both before touching the files listed below.

Status vocabulary matches the tracker: `pending` / `in-progress` / `blocked` / `verified`.
Note that `requirements.html` stores status changes in this browser's localStorage, so a
status set in the page is not shared between machines or agents — the `data-status`
default and the one-line `agent-note` spans inside each item are what other viewers see,
and the real evidence lives in these `work/` files.

## Requirement items: what I did, what I released

| Item id | Points | Final state | Contribution |
|---|---:|---|---|
| `quality-local-preflight` | 2 | verified (by earlier work; re-confirmed by me) | `php scripts/preflight.php` → **13/13 local checks** after applying migration `030_recoverable_record_archive.sql` to the isolated `simplr_dev` database. Pre-migration dump taken to `/tmp/simplr-backup/` (outside the repo, never committed). |
| `quality-full-suite` | 7 | **released to Claude** | I ran the suites once and repaired three stale assertions, then stopped when Claude's claim (22:30) said the final regression run was in progress. Claude owns the recorded release numbers; mine below are point-in-time. |
| `maintenance-housekeeping` | 2 | **released to Claude** | Independently confirmed: `php -l` on every changed/untracked PHP file → 0 failures, `git diff --check` clean, `.env`/`.env.local` and `.playwright-mcp/` ignored. Nothing deleted. |
| `security-dependencies` | 4 | in-progress (shared with Claude) | Ran the live advisory scan, applied the patch, added CI scanning. Claude owns `docs/dependencies.md`, which I did not edit. |
| `security-secret-rotation` | 6 | **not claimed by me** | `.github/workflows/secret-scan.yml` already exists (created 17:14, before I started). Owner/provider credential rotation remains an owner action. |
| Everything else | — | not claimed | See "Not claimed" below. |

## The dependency work in detail (`security-dependencies`)

- `composer audit --locked` (the requirement's own allowed method: a package-manager
  check that sends package names and versions only — no source, lock file or env file)
  reported **4 medium advisories against `guzzlehttp/psr7` 2.7.0**, the transitive
  Sentry dependency:
  - CVE-2026-59882 — host confusion via weak URI host validation (fixed ≥ 2.12.3)
  - CVE-2026-55766 — CRLF injection in HTTP start-line serialization (fixed ≥ 2.12.1)
  - CVE-2026-49214 — CRLF injection via URI host component (fixed ≥ 2.10.2)
  - CVE-2026-48998 — host confusion via authority reinterpretation (fixed ≥ 2.10.2)
- Offline review in `docs/dependencies.md` had concluded 2.7.0 was safe (it compared
  only against CVE-2023-29197, fixed in 2.4.5). **That row is now stale** — Claude
  should refresh it to 2.13.2.
- Applied `php composer.phar update guzzlehttp/psr7 --with-dependencies`:
  `guzzlehttp/psr7` 2.7.0 → **2.13.2**, `symfony/deprecation-contracts` 3.5.0 → 3.7.1,
  `symfony/polyfill-php80` v1.43.0 added. `composer.json` is untouched, so
  `content-hash` is unchanged; `composer validate` passes.
- **`vendor/` is tracked in git**, so this shows as a modified tree in `git status`
  (`composer.lock` plus `vendor/composer/*`, `vendor/guzzlehttp/*`, `vendor/symfony/*`).
  Do not revert it; it is the fix.
- Repeatable scanning: new `.github/workflows/dependency-audit.yml` — runs
  `php composer.phar audit --locked` on push, pull request, weekly schedule and manual
  dispatch, `contents: read` only. This satisfies the "repeatable scanning in CI or
  release checks" half of the definition of done. It has not yet run on GitHub, so CI
  execution is unverified until the next push.
- Licence review of the Composer tree: all 9 locked packages are MIT. Bundled:
  PHPMailer 6.9.1 is LGPL-2.1 (licence file kept with the code), the Flutterwave
  `PHP-v3-master/` SDK is MIT and unused by application code, `mediawiki-php-excimer/`
  is empty and unused.
- Verification after the patch: `require vendor/autoload.php` resolves `Sentry\init`
  and `GuzzleHttp\Psr7\Message`, a PSR-7 response renders correctly, and the non-login
  unit suites still pass (rich text 15/15, accounting 21/21, recruiting policy 14/14).

## Test repairs I made (stale assertions, not product defects)

1. `scripts/smoke_test.py` — the invitation success copy in `users/admin/invite.php`
   changed to "The mail server accepted the invitation." (that app change is **not**
   mine); the assertion was still looking for "Invitation sent.". The invitation mail
   itself was confirmed written to `storage/mail`, so only the copy was stale.
2. `scripts/access_smoke_test.py` — the synthetic task payload did not send `due_date`,
   which `app/modules.php` marks required, so `POST /api/records.php?module=tasks`
   returned 422 and the suite aborted at check 125. That abort left
   `.local/access-browser-fixture.json` stale, which then failed
   `operations_smoke_test`, `attachments_smoke_test` and `recruiting_smoke_test`
   downstream. Added `'due_date': '2026-11-20'`.
3. `scripts/operations_smoke_test.py` — `settings_post()` did not send
   `query_response_minutes` / `warning_expiry_months`, both `required` on the current
   settings form (added with the alerts/queries work), so the conflict-preparation post
   returned 422. Added the form defaults (`4320`, `12`).

## Complete list of files I changed

Nothing under `app/`, `users/`, `api/`, `toolkits/`, `jobs/` or `public/` was modified
by me — those trees belong to other agents working concurrently.

- `scripts/smoke_test.py`, `scripts/access_smoke_test.py`,
  `scripts/operations_smoke_test.py` — the three repairs above.
- `composer.lock` and the committed `vendor/` tree — dependency patch.
- `.github/workflows/dependency-audit.yml` — new.
- `requirements.html` — one `agent-note` appended to the `security-dependencies` item.
- `work/agent-claims.md` — my coordination section appended.
- `work/agent-handoff-requirements.md` — this file.
- `.env.local` — `MAIL_TRANSPORT=smtp` → `MAIL_TRANSPORT=log`. AGENTS.md requires
  `log` for local checks, and `smtp` would have made the suites send real mail. Backups:
  `.env.local.bak-20261027-124448` (already present) and a copy in `/tmp`. **Left at
  `log` deliberately** — tell me if it must go back.

## Evidence: suites run locally (isolated DB, `APP_ENV=local`, `MAIL_TRANSPORT=log`)

Taken 27 Oct ~21:50–22:25, i.e. before Claude's final regression run at 22:30.

- preflight 13/13 local
- consolidation smoke **197/197, 268/268, 388/388**
- access **234/234**, operations **73/73**, attachments **54/54**, recruiting **149/149**
- email scope **58/58**, help **35/35**, marketing **40/40**
- rich text **15/15**, accounting **21/21**, recruiting policy **14/14**
- `github_smoke_test.py` not run by me — Claude owns it and was editing it at 22:01
  (it needs the local stub server; my one attempt hit connection-refused mid-edit).

## Pitfalls found while verifying (so the next agent does not chase them)

1. **Login rate limits.** The app allows 10 logins per account per 15 minutes
   (`rate_limit()` in `app/auth.php`), keyed on action + IP + email. Repeated suite runs
   turn real failures into `AssertionError: … fixture login` with HTTP 429. Clear with
   `DELETE FROM auth_rate_limits;` on the isolated database, or wait the window.
2. **Shared fixture files.** `.local/browser-fixture.json` and
   `.local/recovery-browser-fixture.json` are rewritten by each run; two suites running
   at once mix fixtures from different companies and produce `StopIteration` and
   phantom 404s. Run suites sequentially, one agent at a time.
3. **No PHP/`/tmp` interop for Windows Python.** Git Bash `/tmp` is not visible to
   native Python — pipe tool output into Python on stdin instead of writing temp files.
4. Statuses in `requirements.html` are localStorage-backed; edit `data-status` plus an
   `agent-note` if other agents must see a change.

## Not claimed by me (open for other agents or owners)

Legacy route audit, staff toolkit grants, talent journey, promotions/demotions,
payments sandbox and live gates, privacy/legal facts, hosting and HTTPS origins,
backup/restore objectives, accessibility review, capacity/load budgets, support runbook,
launch-scope sign-off.

Owner-blocked items stay `blocked`: `privacy-owner-input`, `payments-live-gates`, and
the credential-rotation half of `security-secret-rotation`.

## Rules this agent followed

1. Isolated local database only, `APP_ENV=local`, `MAIL_TRANSPORT=log`; no real mail.
2. No deployment, no commits, no pushes, no provider contact, no financial transactions.
3. No secret values printed anywhere — only package names, versions and CVE ids.
4. Nothing deleted from the working tree; all other agents' edits left untouched.
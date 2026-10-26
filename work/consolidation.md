# Simplr24 consolidation and audience landing pages

Requested on 23 October 2026: reuse the solid work in `simplr24`, with
`C:/Users/HP/simplrsuite` as the single active source of truth. The subsequent
request adds promotional pages for business, talent, and work subdomains.

## Source comparison

Historical source: `C:/Program Files/Ampps/www/simplr24`, HEAD `43079aa`.
Destination baseline: `2dbaf7c`, with the existing overnight changes preserved.
Both working trees contained uncommitted changes before consolidation. The
historical source was read only, including its uncommitted dashboard/auth edits.

Comparing 258 application/configuration/documentation files in the source with
the destination's original commit, ignoring CRLF/LF differences:

- 225 already match the original destination code.
- 14 differ from their original destination counterpart.
- 19 are absent from that original commit. A few are already present as ignored
  files or under another extension in the destination working tree.

Thus the older project, CRM, accounting, recruiting, and toolkit implementations
are mostly already present. Copying the source tree over this one would replace
the newer authentication, tenant checks, forms, and working public pages.

Evidence: `work/test-results/consolidation-code-comparison.json` and
`consolidation-before.json`. A private source-code snapshot of the pre-consolidation
destination is retained in `.local/consolidation-before.zip`. Environment files,
database dumps, uploaded documents, and dependency directories were excluded.
This is a source recovery snapshot, not a database backup.

## Decisions and integrated work

| Source work | Destination decision |
| --- | --- |
| `public/scripts/toast.js`, `public/styles/toast.css` | Consolidated the ToastManager interface and notification layout into the shared shell. Added safe text nodes, live-region semantics, persistent errors, pause on focus/hover, focus restoration, and no native `alert` replacement. Server-rendered feedback still works without JavaScript. |
| `users/forgetpassword.php`, `send_reset_email.php`, `reset_password.php` | Recovered the four-role recovery journey. Business and talent use their tested existing flows. New staff recovery binds each hashed, expiring, single-use token to role, auth record, directory identity, company, email, and current password. It never selects the first email match across account tables. |
| Employee/manager login recovery links | Integrated into the current shared login pages. Staff can recover accounts without asking an administrator to replace their password. |
| `users/developers/documentation.php` | Consolidated the route and guide structure, replacing nonexistent bearer-key APIs with the implemented session/CSRF record API. The old `.html` entry points here. |
| Signup/verification UI updates | Existing canonical implementation retained: server-issued identity, verified sessions, hashed expiring tokens, consistent forms and scoped local mail. |
| Task modal and dashboard variants | Current validated task form and dashboard retained. Source experiments include unscoped project queries, duplicate/placeholder panels, and debug alerts; these were not reintroduced. |
| `subpro.php` subscription redesign | Preserved as a historical reference. Its client checkout/provider flow needs server verification and configuration before reuse; no live checkout was enabled. |
| `processtools.php` mail changes | Existing centralized mail configuration retained; direct native `mail()` calls were not imported. |
| `users/admin/recordpayment.php` | Empty source file; no implementation to recover. |
| Additional Composer packages | No blind dependency or vendor copy. Existing environment/mail helpers cover the ported flows. The source's Psalm setup references a nonexistent `src/` app structure. |
| Prototype homepages, dashboard copies, demo files | Historical source remains intact; no extra active or publicly routable app copies were introduced. |

Migration `006_staff_password_recovery.sql` was applied to the isolated local
database. No historical customer data or source credentials were imported.
Shared files have no runtime dependency on the historical checkout.

## Three promotional entry points

| Intended hostname | Local preview | Audience and primary action |
| --- | --- | --- |
| `business.simplr.com` | `/landing/business/` | Business owners: create a workspace or sign in. |
| `talents.simplr.com` | `/landing/talents/` | Professionals: create a private profile, then optionally list it. |
| `work.simplr.com` | `/landing/work/` | Invited employees and managers: select the appropriate sign-in. |

The root `index.php` selects the page using an exact hostname allowlist. Each
audience also has its own `landing/<audience>/index.php` entry point. Templates,
styles, authentication and backend remain shared in this repository. The Work
preview does not expose the private `/work/` handoff/evidence directory.

Pages have separate messaging, visual accents, titles, descriptions, canonical
URLs, Open Graph metadata, and cross-audience navigation. Local previews use
local links and `noindex, nofollow`. Product illustrations are explicitly labelled
as illustrative; no invented customers, metrics, or enabled provider claims.

Before publishing, configure HTTPS virtual hosts for all three domains against
this same document root, DNS and certificates, and set `APP_BUSINESS_URL`,
`APP_TALENT_URL`, `APP_WORK_URL` to their HTTPS origins. These configured origins
also choose the appropriate audience for account-email links. Unset origins keep
the existing `APP_URL` fallback for account mail. Session cookies stay host-only;
this change does not create cross-domain single sign-on. Apache/container hosting
still requires deployment validation. No DNS or hosting change was made here.

## Verification

- `python scripts/consolidation_smoke_test.py`: core + talent + staff recovery,
  including same-email accounts in different companies and different roles,
  unknown-account responses, forged identity fields, token expiry/reuse, password
  and session invalidation, API guide and notification asset routes.
- `python scripts/marketing_smoke_test.py`: preview routes, exact hostname
  selection, intended account destinations, SEO metadata, unknown-host handling,
  private work-directory restrictions and configured email-link audiences.
- `node scripts/consolidation_browser_test.cjs`: actual Chromium desktop/mobile
  flows, notification XSS protection, timing/focus/accessibility, JavaScript-free
  recovery, updated project feedback, staff sign-in, and all three promotional
  pages. External requests are blocked in this isolated test browser.
- Screenshots and JSON results: `work/test-results/`. Source integrity, backup
  validation, changed PHP lint and whitespace results are recorded in
  `consolidation-final.json` and the handoff. Do not count the preliminary failing
  runs as passes: a test initially requested a CSRF token from an account-choice
  page; another used a nonexistent Playwright method; the Work preview initially
  collided with the private-directory router rule. These were corrected and rerun.

## Still outside this consolidation

Subsequent daytime work recovered the source subscription prices and the
NGN/Paystack, USD/Flutterwave choice from `users/admin/subpro.php` into server-owned
billing. The source conversion fallback is now a displayed fixed configuration;
the source client currency API and its credentials were not reused. Payment
verification, staff toolkit grants and legacy HTTP containment have since been
implemented and locally tested. Details and remaining live activation gates:
`work/payment-launch.md` and the latest checkpoint in `work/overnight-handoff.md`.
The 285 original source hashes were rechecked after this work with zero changes.

Historical remainder list at the original consolidation checkpoint:

Live payment/subscription verification, bank/payroll integrations, complete
staff toolkit permissions, legacy direct-route review, legal/privacy details,
production credential rotation, and deployment/backup verification remain launch
blockers described in the overnight handoff. The source comparison reduces
duplicate work; it does not certify the application as production-ready.

## Recruiting continuation — 24 October 2026

Reviewed the reference `toolkits/talent_mgt/recruiter.php`, `displaytalent.php`,
`users/talents/submit_talent.php`, `talentpool.php` and `ziprecruiter.php`. Preserved
useful concepts (professional skills, experience, location, applicant stages),
while implementing the missing job-to-application journey in canonical `app/`
helpers and the existing applications register. The reference contains mock data,
unsafe uploads/raw resume paths and an external jobs prototype; it does not provide
an operational application journey to copy. No credentials, historical applicants
or unconsented profiles were imported. Rechecked all 285 recorded source hashes:
unchanged. See the latest handoff for evidence and current limitations.

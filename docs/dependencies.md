# Dependency inventory

What Simplr Suite runs on, where each piece comes from and what is known about it.
Reviewed offline on 27 Oct 2026 from `composer.lock`, the bundled folders and the
page sources; no source, lock file or private configuration was uploaded. The
approved `composer audit --locked` check found four medium advisories in
`guzzlehttp/psr7` 2.7.0; it was updated to 2.13.2 and the repeat audit reported no
advisories. The scan sends package names and versions to Packagist. A weekly and
push/PR GitHub Actions workflow now repeats the scan, but has not yet run on GitHub.

## Runtime platform

| Component | Version checked | Notes |
| --- | --- | --- |
| PHP | 8.2.27 locally, `php:8.2-apache` in `Dockerfile` | Needs `mysqli`, `mysqlnd`, `mbstring`, `openssl`, `curl`, `fileinfo`, `session`, `zip`, `dom`. `scripts/preflight.php` checks all nine. |
| MySQL | 8.0.39 locally | Migrations use MySQL 8 syntax; MariaDB-only syntax was removed from 018. |
| Apache | Ampps 2.4 locally (port 8001 test helper), Debian Apache in the container | `.htaccess` plus `router.php` enforce the route manifest in `app/routes.php`. |

**Fixed today:** the container did not install the `zip` extension. The code guards
`ZipArchive` with `class_exists`, so nothing crashed, but every Word, Excel and
PowerPoint upload would have been rejected as unrecognised. `Dockerfile` now installs
`libzip-dev` and `zip`, and preflight checks for it. The container build itself has
not been run (no Docker on this machine).

## Composer packages (`composer.json` → `composer.lock`, committed in `vendor/`)

| Package | Version | Licence | Used by | Notes |
| --- | --- | --- | --- | --- |
| sentry/sentry | 4.9.0 | MIT | Only `users/admin/sentryinitialize.php` (blocked route, see below) | Not loaded by any reachable page. |
| guzzlehttp/psr7 | 2.13.2 | MIT | Sentry dependency | Updated from 2.7.0 after four medium advisories (CVE-2026-59882, CVE-2026-55766, CVE-2026-49214 and CVE-2026-48998); the post-update audit reported no advisories. |
| psr/http-factory | 1.1.0 | MIT | Sentry dependency | Interfaces only. |
| psr/http-message | 2.0 | MIT | Sentry dependency | Interfaces only. |
| psr/log | 3.0.2 | MIT | Sentry dependency | Interfaces only. |
| jean85/pretty-package-versions | 2.0.6 | MIT | Sentry dependency | |
| ralouphie/getallheaders | 3.0.3 | MIT | Sentry dependency | Polyfill. |
| symfony/options-resolver | 7.1.1 | MIT | Sentry dependency | |
| symfony/deprecation-contracts | 3.7.1 | MIT | Sentry dependency | Updated with the PSR-7 advisory fix. |
| symfony/polyfill-php80 | 1.43.0 | MIT | Symfony dependency | Added with the PSR-7 advisory fix. |

The whole Composer tree exists only for Sentry, and no live page uses Sentry. Either
wire error reporting deliberately (see the monitoring item in `requirements.html`)
or remove the package. That is an owner decision.

## Bundled libraries (copied into the repository)

| Folder | Version | Licence | Used by the app? | Notes |
| --- | --- | --- | --- | --- |
| `PHPMailer-master/` | 6.9.1 | LGPL-2.1 | Yes, `app/mail.php` for SMTP | No advisory known for 6.9.1; 6.9.3 and later exist. Updating means replacing the folder and running the invitation and alert mail tests in log mode. LGPL allows this use; keep its licence file with the code. |
| `PHP-v3-master/` | Flutterwave v3 SDK (no version file) | MIT | No. `app/flutterwave.php` is the app's own adapter | Dead code in deployments. Its own tests are not application tests. Candidate for removal. |
| `mediawiki-php-excimer/` | empty folder | — | No | Leftover; safe to delete after a look. |

## Browser-side libraries

The current workspace (`app/view.php`, `public/scripts/*.js`, `public/styles/*`)
loads no third-party script or stylesheet. Legacy pages still in the repository load
these from CDNs. All of them return 404 through the route manifest on both the PHP
dev router and Apache (checked on 27 Oct), so visitors cannot reach them today:

| Library | Version | Found in | Concern if a page were re-enabled |
| --- | --- | --- | --- |
| pdf.js (cdnjs) | 2.11.338 | `users/employees/talentmessages.php`, `talentmsg.php`, `users/talents/talentmessages.php` | **CVE-2024-4367**, script execution from a crafted PDF; fixed in 4.2.67. |
| jQuery (code.jquery.com) | 3.6.0 | same three pages | Older release; no known exploitable advisory in normal use. |
| socket.io client | 4.0.0 | `users/admin/messages.php` | Old; the server side it expects does not exist here. |
| Unit components | `latest` (unpinned) | `users/admin/createusbankaccount.php`, `users/talents/unitbankdemo.php` | Unpinned third-party script on a banking page. |
| Google Tag Manager | tag `G-L5LD0KTXZS` | `users/employees/talentmessages.php` | Analytics on a private messaging page; privacy notice would need to say so. |
| Font Awesome | 5.15.3 and 6.0.0-beta3 | legacy pages and two toolkit pages | Beta build in use. |
| Flutterwave inline checkout | `checkout.flutterwave.com/v3.js` | `users/admin/renewsubscription.php`, `subpro.php` (blocked) | The live billing flow uses server-side redirects instead. |

**Credential found:** `users/admin/sentryinitialize.php` has a Sentry DSN written into
the source (and therefore Git history), with 100% trace and profile sampling. A DSN
lets anyone send events into that Sentry project. Add it to the owner's
credential-rotation list (revoke the key in Sentry) together with the payment keys
already listed there. The value is not repeated here.

## Development and test tooling (not deployed)

| Tool | Version | Where | Notes |
| --- | --- | --- | --- |
| Node.js | 24.21.0 | developer machine | Browser tests and `node --check`. |
| Playwright | 1.60.0 | `C:/Users/HP/node_modules` (outside the repository) | Not pinned by any `package.json` in this repo, so CI cannot reproduce the browser suites yet. |
| Python | 3.12 | developer machine | Smoke tests use the standard library only. |
| Gitleaks | 8.30.0 | `.github/workflows/secret-scan.yml` | Scans new commits on push and pull request. |

## Recommended policy (for owner approval)

1. Pin every dependency to an exact version, browser libraries included (no `latest`).
2. The GitHub Actions workflow runs `composer audit --locked` weekly and on
   pushes/pull requests; monthly, review PHPMailer releases and this file.
3. Security fixes in a runtime dependency ship within a week; others with the next release.
4. Before deleting `PHP-v3-master/`, `mediawiki-php-excimer/` and the blocked legacy
   pages that load old CDN scripts, confirm nobody needs them.
5. Add a `package.json` pinning Playwright so CI can run the browser suites.

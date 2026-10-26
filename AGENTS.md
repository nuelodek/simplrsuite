# Canonical Simplr Suite workspace

`C:/Users/HP/simplrsuite` is the active source of truth, as requested by the user.
The historical `C:/Program Files/Ampps/www/simplr24` checkout is a read-only reference.
Do not implement changes there, point runtime includes at it, or overwrite this
workspace wholesale with that checkout. Its original work and uncommitted changes
must remain intact. Selective consolidation evidence is in `work/consolidation.md`.

Use the shared `app/` authentication, database, mail, validation and view helpers.
Verify roles, company ownership and CSRF on mutations. New migrations belong in
`database/migrations/`; never import historical data dumps or copy credentials.
Preserve user changes. Existing legacy routes still need review before deployment.

Local checks use an isolated MySQL database and `APP_ENV=local`, `MAIL_TRANSPORT=log`.
`python scripts/consolidation_smoke_test.py` includes the core and talent checks.
`node scripts/consolidation_browser_test.cjs` exercises notifications and navigation
in an isolated local browser. PHP lint and `git diff --check` cover the edited files.
The bundled payment SDK's tests do not substitute for application journey tests.

Do not deploy, send real email, or execute financial transactions as part of these
checks. The overnight automation is paused; do not reactivate it unless requested.

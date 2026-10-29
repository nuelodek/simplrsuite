# QuickBooks Online connection

The QuickBooks connector currently supports Intuit OAuth authorization, encrypted
access/refresh-token storage, automatic access-token refresh, and read-only
verification of the authorized company. It does not import, export, or modify
accounting records.

## Configure the Intuit app

1. Create an Intuit Developer account and app using the
   [QuickBooks developer getting-started guide](https://developer.intuit.com/app/developer/qbo/docs/get-started/start-developing-your-app).
2. Enable the QuickBooks Online Accounting API for the app and create a sandbox
   company using Intuit's [sandbox setup guide](https://developer.intuit.com/app/developer/qbo/docs/develop/sandboxes).
3. Register the exact redirect URI shown on `/users/admin/quickbooks.php`:
   `{APP_URL}/users/admin/quickbooks.php`.
4. Set `QUICKBOOKS_CLIENT_ID` and `QUICKBOOKS_CLIENT_SECRET` in the server
   environment. Never put credentials in source control or expose the secret in
   the workspace UI.
5. Set `QUICKBOOKS_ENVIRONMENT=sandbox` for development. The connector uses the
   Intuit sandbox API host in this mode.
6. After sandbox testing and production approval, set
   `QUICKBOOKS_ENVIRONMENT=production` with `APP_ENV=production` and an HTTPS
   `APP_URL`. Production mode is deliberately unavailable on local or HTTP
   origins.
7. As a workspace admin, open Integrations → QuickBooks Online → Set up
   connection, then authorize the intended QuickBooks company at Intuit.

The connector currently uses the REST Accounting API's Company Info endpoint
for company verification; it does not yet synchronize accounting entities.

The app requests only the `com.intuit.quickbooks.accounting` scope. OAuth state is
session-bound and expires after ten minutes. The callback validates the returned
company ID against Intuit's Company Info endpoint before saving anything.
Tokens are encrypted at rest using the application's `APP_KEY`; production must
set and securely back up that key.

## Verify or disconnect

The connection page can re-check the company with the read-only Company Info API
and refresh expired access tokens using the stored refresh token. Disconnect
removes the saved workspace tokens and requests Intuit-side token revocation. If
Intuit cannot confirm revocation, the page tells the admin to remove Simplr from
the Intuit account's connected-app settings.

No real provider call is part of local tests. Tests use an injected CLI-only
transport with synthetic responses.

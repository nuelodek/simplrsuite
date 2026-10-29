# Xero connection

The Xero connector supports OAuth authorization, encrypted access/refresh-token
storage, automatic token refresh, organization selection, and read-only
verification of the selected organization. It does not import, export, or modify
accounting records.

## Configure the Xero app

1. Create an app in the [Xero developer portal](https://developer.xero.com/).
2. Register the exact redirect URI shown on `/users/admin/xero.php`:
   `{APP_URL}/users/admin/xero.php`.
3. Set `XERO_CLIENT_ID` and `XERO_CLIENT_SECRET` in the server environment.
   Never put credentials in source control or expose the secret in the workspace
   UI.
4. As a workspace admin, open Integrations → Xero → Set up connection, authorize
   the Xero account, and select the organization to associate with the workspace.
5. To test safely, use a Xero demo organization from a Xero demo company/account.
   The integration uses Xero's standard OAuth and Accounting API hosts for both
   demo and live organizations; there is no separate API sandbox hostname.
6. Production connections require `APP_ENV=production` and an HTTPS `APP_URL`.

The connector requests only `accounting.settings.read` and `offline_access`.
OAuth state is session-bound and expires after ten minutes. The callback lists
the organizations allowed by Xero; the selected organization is checked through
the read-only Organisation endpoint before tokens are saved. Tokens are
encrypted at rest with the application's `APP_KEY`; production must set and
securely back up that key.

## Verify or disconnect

The admin connection page can re-check each organization and refresh expired
access tokens using the rotating refresh token. Refresh tokens that have not
been used for 60 days may expire and require reconnecting. Disconnect removes
local tokens and requests Xero-side token revocation. If Xero cannot confirm
revocation, remove Simplr from the account's connected-app settings.

No real provider call is part of local tests. Tests use an injected CLI-only
transport with synthetic responses.

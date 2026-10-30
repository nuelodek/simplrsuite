# Google Workspace account connection

The initial Google Workspace connector verifies an individual Google account
using OpenID Connect. It does not access Workspace resources or retain OAuth
tokens.

## Configure the Google OAuth client

1. Create an OAuth client in the [Google Cloud Console](https://console.cloud.google.com/).
2. Register the exact redirect URI shown on `/users/admin/google-workspace.php`:
   `{APP_URL}/users/admin/google-workspace.php`.
3. Set `GOOGLE_WORKSPACE_CLIENT_ID` and `GOOGLE_WORKSPACE_CLIENT_SECRET` in the
   server environment. Never put credentials in source control or expose the
   secret in the workspace UI.
4. As a workspace admin, open Integrations → Google Workspace → Set up
   connection, then authorize the individual Google account.
5. Production connections require `APP_ENV=production` and an HTTPS `APP_URL`.

The connector requests only `openid`, `email` and `profile`, with online access
and no incremental-scope expansion. It uses the short-lived access token to
retrieve the OpenID Connect userinfo response, requires a verified email, and
then discards the token. Only the Google subject hash and verified email are
stored in the workspace connection record.

Disconnect removes the local identity link. The app does not retain a token to
revoke Google-side access; remove Simplr from the account's third-party
connections in Google Account settings to revoke that grant. This connector
does not access Calendar, Drive, Gmail, contacts, users or groups; it does not
use domain-wide delegation; and it does not synchronize data.

No live Google credentials or provider calls are part of local tests. The
connector tests use injected CLI-only transports with synthetic responses:
`php scripts/google_workspace_test.php`.

# Authentication Security Configuration

This release verifies Google Sign-In ID tokens on the Laravel server. The mobile app must send a Google ID token, not a client-supplied email or Google account ID.

## 1. Google OAuth client

Create an OAuth 2.0 Client ID of type **Web application** in the Google Cloud project used by Clipkeo. Keep the existing Android OAuth client configured with the app package name and signing certificate fingerprints.

Use the same Web Client ID in both places:

- Laravel environment: `GOOGLE_CLIENT_ID=<WEB_CLIENT_ID>`
- Flutter build: `--dart-define=GOOGLE_SERVER_CLIENT_ID=<WEB_CLIENT_ID>`

Example:

```bash
# Run the Flutter app
flutter run --dart-define=GOOGLE_SERVER_CLIENT_ID=your-web-client-id.apps.googleusercontent.com

# Build Android APK
flutter build apk --dart-define=GOOGLE_SERVER_CLIENT_ID=your-web-client-id.apps.googleusercontent.com
```

Do not commit OAuth client secrets, service-account keys, or other credentials to Git. The Web Client ID is an identifier, not a client secret.

If the Web Client ID is missing or does not match, Google Sign-In will be rejected intentionally.

## 2. Sanctum token lifetime

Laravel now defaults personal access tokens to 43,200 minutes (30 days). Set this on the server if a different lifetime is required:

```dotenv
SANCTUM_TOKEN_EXPIRATION=43200
```

After changing server environment values, refresh Laravel's configuration cache:

```bash
php artisan config:clear
php artisan config:cache
```

Users may need to sign in again when their existing token is expired or revoked. Password reset revokes all existing Sanctum tokens for that account.

## 3. Production transport

Use HTTPS for the production API. Do not transmit Google ID tokens, passwords, OTPs, or bearer tokens over plain HTTP on a public network.

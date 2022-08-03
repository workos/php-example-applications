# php-directory-sync-example

An example PHP app demonstrating SSO with the [WorkOS PHP SDK](https://github.com/workos/workos-php).

## Dependencies

Composer - [Link](https://getcomposer.org/)

## Setup

1. Clone the repo and install the dependencies by running the following:

   ```bash
   git clone git@github.com:workos/php-example-applications/php-directory-sync-example
   cd php-example-applications/php-directory-sync-example
   composer i
   ```

2. Follow the instructions [here](https://docs.workos.com/sso/auth-flow) on setting up an SSO connection. The redirect URL for the example app if used as is will be http://localhost:8000/auth/callback.

3. Create a file at the root of this project called .env and declare the WorkOS API Key, WorkOS Client ID, and Webhooks Secret.

```WORKOS_API_KEY="your_api_key"
WORKOS_CLIENT_ID="your_client_id"
WORKOS_WEBHOOKS_SECRET="your_webhook_secret"
```

## Running the app

Use the following command to run the app:

```bash
php -S localhost:8000 router.php
```

Once running, navigate to the following URL for a demonstration on the SSO workflow: http://localhost:8000.

## Test Webhooks

4. WorkOS sends Webhooks as a way of managing updates to Directory Sync connections. The Webhooks section of the WorkOS Dashboard allows you to send test webhooks to your application. The Test Webhooks section of this application allows you to visualize the validated webhooks directly in this application in real-time. [Please review the tutorial here](https://workos.com/blog/test-workos-webhooks-locally-ngrok) for details on how this can be done locally. The Webhooks secret variable, for which the value can be obtained in the WorkOS dashboard, must be filled out on `variables.php` to use this feature. 
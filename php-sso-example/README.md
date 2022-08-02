# php-sso-example

An example PHP app demonstrating SSO with the [WorkOS PHP SDK](https://github.com/workos/workos-php).

## Dependencies

Composer - [Link](https://getcomposer.org/)

## Setup

1. Clone the repo and install the dependencies by running the following:

   ```bash
   git clone git@github.com:workos/php-example-applications/php-sso-example
   cd php-example-applications/php-sso-example
   composer i
   ```

1. Follow the instructions [here](https://docs.workos.com/sso/auth-flow) on setting up an SSO connection. The redirect URL for the example app if used as is will be http://localhost:8000/auth/callback.

1. Edit lines 14 of `router.php` to declare the connectionID.

4. Create a new file called `.env` and enter your API Key and Client ID from the WorkOS Dashboard. 

```
WORKOS_API_KEY="your_api_key"
WORKOS_CLIENT_ID="your_client_id"
```

## Running the app

Use the following command to run the app:

```bash
php -S localhost:8000 router.php
```

Once running, navigate to the following URL for a demonstration on the SSO workflow: http://localhost:8000.

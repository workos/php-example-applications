# php-sso-example

An example PHP app demonstrating SSO with the [WorkOS PHP SDK](https://github.com/workos/workos-php).

## Dependencies

Composer - [Link](https://getcomposer.org/)

## Setup

1. Clone the repo and install the dependencies by running the following:

   ```bash
   git clone git@github.com:workos/php-example-applications
   cd php-example-applications/php-mfa-example
   composer i
   ```

1. Follow the instructions [here](https://docs.workos.com/mfa/) on setting up MFA.

1. Edit lines 9-10 of `router.php` to declare the WorkOS API Key, and WorkOS Client ID

## Running the app

Use the following command to run the app:

```bash
php -S localhost:8000 router.php
```

Once running, navigate to the following URL for a demonstration on the SSO workflow: http://localhost:8000.

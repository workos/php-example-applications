# php-admin-portal-example

An example PHP app demonstrating Admin Portal with the [WorkOS PHP SDK](https://github.com/workos-inc/workos-php).

## Dependencies

Composer - [Link](https://getcomposer.org/)

## Setup

1. Clone the repo and install the dependencies by running the following:

   ```bash
   git clone git@github.com:workos-inc/php-example-applications/php-admin-portal-example
   cd php-example-applications/php-admin-portal-example
   composer i
   ```

1. Follow the instructions [here](https://workos.com/docs/admin-portal/guide) on setting up an Admin Portal session.

1. Edit lines 9-10 of `router.php` to declare the WorkOS API Key, and WorkOS Client ID.

## Running the app

Use the following command to run the app:

```bash
php -S localhost:8000 router.php
```

Once running, you'll be prompted for an Organization ID, simply plug this in and you'll be redirected to a live admin portal session for that organization.

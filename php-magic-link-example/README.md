# php-magic-link-example

An example PHP app demonstrating Magic Link with the [WorkOS PHP SDK](https://github.com/workos-inc/workos-php).

## Dependencies

Composer - [Link](https://getcomposer.org/)

## Setup

1. Clone the repo and install the dependencies by running the following:

   ```bash
   git clone git@github.com:workos-inc/php-example-applications/php-magic-link-example
   cd php-example-applications/php-magic-link-example
   composer i
   ```

1. Follow the instructions [here](https://workos.com/docs/magic-link/guide) on setting up Magic Link.

1. Edit lines 9-10 of `router.php` to declare the WorkOS API Key, and WorkOS Client ID.

## Running the app

Use the following command to run the app:

```bash
php -S localhost:8000 router.php
```

Once running, you'll be prompted for an email address, simply plug this in and you'll be sent a magiclink that will redirect you to a success page.

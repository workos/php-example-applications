# php-directory-sync-example

An example PHP app demonstrating Directory Sync with the [WorkOS PHP SDK](https://github.com/workos-inc/workos-php).

## Dependencies

Composer - [Link](https://getcomposer.org/)

## Setup

1. Clone the repo and install the dependencies by running the following:

   ```bash
   git clone git@github.com:workos-inc/php-directory-sync-example
   composer i
   ```

1. Follow the instructions [here](https://workos.com/docs/directory-sync/guide) on setting up a Directory Sync connection.

1. Edit lines 9-11 of `router.php` to declare the WorkOS API Key, WorkOS Client ID, connectionID, and/or Domain.

## Running the app

Use the following command to run the app:

```bash
php -S localhost:8000 router.php
```

Once running, navigate to the following URL for a demonstration on the Directory Sync listUsers and listGroups API functions: http://localhost:8000.

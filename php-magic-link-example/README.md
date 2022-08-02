# php-magic-link-example

An example PHP app demonstrating Magic Link with the [WorkOS PHP SDK](https://github.com/workos/workos-php).

## Dependencies

Composer - [Link](https://getcomposer.org/)

## Setup

1. Clone the repo and install the dependencies by running the following:

   ```bash
   git clone git@github.com:workos/php-example-applications/php-magic-link-example
   cd php-example-applications/php-magic-link-example
   composer i
   ```

2. Follow the instructions [here](https://workos.com/docs/magic-link/guide) on setting up Magic Link.

3. Create a new file called `.env` and enter your API Key and Client ID from the WorkOS Dashboard. 

```
WORKOS_API_KEY="your_api_key"
WORKOS_CLIENT_ID="your_client_id"
```

## Running the app

Use the following command to run the app:

```bash
php -S localhost:8000 router.php
```

Once running, you'll be prompted for an email address, simply plug this in and you'll be sent a magiclink that will redirect you to a success page.

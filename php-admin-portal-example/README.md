# php-admin-portal-example

An example PHP app demonstrating Admin Portal with the [WorkOS PHP SDK](https://github.com/workos/workos-php).

## Dependencies

Composer - [Link](https://getcomposer.org/)

## Setup

1. Clone the repo and install the dependencies by running the following:

   ```bash
   git clone git@github.com:workos/php-example-applications
   cd php-example-applications/php-admin-portal-example
   composer i
   ```

2. Follow the instructions [here](https://workos.com/docs/admin-portal/guide) on setting up an Admin Portal session.

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

Once running, you'll be prompted for an Organization Name, a space separated list of domains associated with that domain, and if you'd like to launch the SSO or Directory Sync Admin Portal flow. 

The app logic will check if there is a matching Organization already based on the domains entered and if not it will create a new one. Simply plug this in and you'll be redirected to a live admin portal session for that organization. 



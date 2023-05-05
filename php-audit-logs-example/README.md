# php-audit-logs-example

An example PHP application demonstrating how to use the [WorkOS PHP SDK](https://github.com/workos/workos-php) to send and retrieve Audit Log events. This example is not meant to show a real-world example of an Audit Logs implementation, but rather to show concrete examples of how events can be sent using the PHP SDK.

Note: PHP 8 or higher is required to use this example application

## Dependencies

Composer - [Link](https://getcomposer.org/)

## Setup

1. Clone the repo and install the dependencies by running the following:

   ```bash
   git clone git@github.com:workos/php-example-applications
   cd php-example-applications/php-audit-logs-example
   composer i
   ```

2. Create a new file called `.env` and enter your API Key and Client ID from the WorkOS Dashboard. Add your system username from your computer to generate the path to the downloads folder in line 189 in router.php. Note: do not add '' to the username in your .env file. 

```
WORKOS_API_KEY='your_api_key'
WORKOS_CLIENT_ID='your_client_id'
PATH_USERNAME=username
```

## Running the app

Use the following command to run the app:

```bash
php -S localhost:8000 router.php
```

Once running, navigate to the following URL for a demonstration on the SSO workflow: http://localhost:8000.


## Audit Logs Setup with WorkOS

3. Follow the [Audit Logs configuration steps](https://workos.com/docs/audit-logs/emit-an-audit-log-event/sign-in-to-your-workos-dashboard-account-and-configure-audit-log-event-schemas) to set up the following 5 events that are sent with this example:

Action title: "user.signed_in" | Target type: "team"
Action title: "user.logged_out" | Target type: "team"
Action title: "user.organization_set" | Target type: "team"
Action title: "user.organization_deleted" | Target type: "team"
Action title: "user.connection_deleted" | Target type: "team"

4. Next, take note of the Organization ID for the Org which you will be sending the Audit Log events for. This ID gets entered into the splash page of the example application.

5. Once you enter the Organization ID and submit it, you will be brought to the page where you'll be able to send the audit log events that were just configured. You'll also notice that the action of setting the Organization triggered an Audit Log already. Click the buttons to send the respective events.

6. To obtain a CSV of the Audit Log events that were sent for the last 30 days, click the "Export Events" tab. Downloading the events is a 2 step process. First you need to create the report by clicking the "Generate CSV" button. Then click the "Access CSV" button to download a CSV of the Audit Log events for the selected Organization for the past 30 days.

## Need help?

If you get stuck and aren't able to resolve the issue by reading our API reference or tutorials, you can reach out to us at support@workos.com and we'll lend a hand.

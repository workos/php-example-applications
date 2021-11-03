<?php

require __DIR__ . "/vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

//Set API Key, ClientID, Connection, and/or domain
$WORKOS_API_KEY = "";
$WORKOS_CLIENT_ID = "";
$WORKOS_CONNECTION_ID = "";
$CUSTOMER_DOMAIN = "";

// Setup html templating library
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

// Configure WorkOS with API Key and Client ID 
\WorkOS\WorkOS::setApiKey($WORKOS_API_KEY);
\WorkOS\WorkOS::setClientId($WORKOS_CLIENT_ID);

// Convenient function for throwing a 404
function httpNotFound() {
    header($_SERVER["SERVER_PROTOCOL"] . " 404");
    return true;
}

// Routing
switch (strtok($_SERVER["REQUEST_URI"], "?")) {
    case (preg_match("/\.css$/", $_SERVER["REQUEST_URI"]) ? true: false): 
        $path = __DIR__ . "/static/css" .$_SERVER["REQUEST_URI"];
        if (is_file($path)) {
            header("Content-Type: text/css");
            readfile($path);
            return true;
        }
        return httpNotFound();
// /auth page is what will run the getAuthorizationUrl function


/* There are 5 parameters for the GetAuthorizationURL Function
Domain, Redirect URI, State, Provider, and Connection
These can be read about here: https://workos.com/docs/reference/sso/authorize/get
We recommend using Connection (pass a connectionID) as opposed to Domain so in this example
I am passing domain as an empty string */

    case ("/auth"):
        $authorizationUrl = (new \WorkOS\SSO())
            ->getAuthorizationUrl(
                $CUSTOMER_DOMAIN, //domain as empty string
                'http://localhost:8000/auth/callback', //redirectURI
                [], //state array, also empty
                null, //Provider which can remain null unless being used
                $WORKOS_CONNECTION_ID //connection which is the WorkOS Connection ID
            );
            
        header('Location: ' . $authorizationUrl, true, 302);
        return true;
// /auth/callback page is what will run the getProfileAndToken function and return it
    case ("/auth/callback"):
        $profile = (new \WorkOS\SSO())->getProfileAndToken($_GET["code"]);

        header("Content-Type: application/json");
        echo json_encode($profile);
        return true;
 
        // home and /login will display the login page       
    case ("/"):
    case ("/login"):
        echo $twig->render("login.html.twig");
        return true;

    default:
        return httpNotFound();
}
<?php

require __DIR__ . "/vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Set API Key, ClientID, and Organization ID
$WORKOS_API_KEY = $_ENV['WORKOS_API_KEY'];
$WORKOS_CLIENT_ID = $_ENV['WORKOS_CLIENT_ID'];
$WORKOS_ORGANIZATION_ID = $_ENV['WORKOS_ORGANIZATION_ID'];


// Setup html templating library
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

// Configure WorkOS with API Key and Client ID
\WorkOS\WorkOS::setApiKey($WORKOS_API_KEY);
\WorkOS\WorkOS::setClientId($WORKOS_CLIENT_ID);

// Convenient function for throwing a 404
function httpNotFound()
{
    header($_SERVER["SERVER_PROTOCOL"] . " 404");
    return true;
}

// Routing
switch (strtok($_SERVER["REQUEST_URI"], "?")) {
    case (preg_match("/\.css$/", $_SERVER["REQUEST_URI"]) ? true : false):
        $path = __DIR__ . "/static/css" .$_SERVER["REQUEST_URI"];
        if (is_file($path)) {
            header("Content-Type: text/css");
            readfile($path);
            return true;
        }
        return httpNotFound();

    case (preg_match("/\.png$/", $_SERVER["REQUEST_URI"]) ? true : false):
        $path = __DIR__ . "/static/images" .$_SERVER["REQUEST_URI"];
        if (is_file($path)) {
            header("Content-Type: image/png");
            readfile($path);
            return true;
        }
        return httpNotFound();

        // /auth route is what will run the getAuthorizationUrl function

        /* There are 6 parameters for the GetAuthorizationURL Function
        Domain (deprecated), Redirect URI, State, Provider, Connection and Organization
        These can be read about here: https://workos.com/docs/reference/sso/authorize/get
        We recommend using Connection (pass a connectionID) */

    case ("/auth"):

        $loginType = $_POST['login_method'];

       

        // Set the organization or provider based on the login type
        if ($loginType == "saml") {
            $authorizationUrl = (new \WorkOS\SSO())
            ->getAuthorizationUrl(
                null, //domain is deprecated, use organization instead
                'http://localhost:8000/callback', //redirectURI
                [], //state array, also empty
                null, //Provider which can remain null unless being used
                null, //Connection which is the WorkOS Organization ID,
                $WORKOS_ORGANIZATION_ID //organization ID, to identify connection based on organization ID,
            );            
        } else {
            $authorizationUrl = (new \WorkOS\SSO())
            ->getAuthorizationUrl(
                null, //domain is deprecated, use organization instead
                'http://localhost:8000/callback', //redirectURI
                null, //state array, also empty
                $loginType, //Provider which can remain null unless being used
            );                    
        }

        header('Location: ' . $authorizationUrl, true, 302);
        return true;

        // /callback route is what will run the getProfileAndToken function and return it

    case ("/callback"):
        $profile = (new \WorkOS\SSO())->getProfileAndToken($_GET["code"]);
        $first_name = $profile->raw['profile']['first_name'];

        session_start();
        $_SESSION['first_name'] = $first_name;
        $_SESSION['profile'] = $profile;
        $_SESSION['isactive'] = true;

        header('Location: ' . '/', true, 302);

        return true;

        // / route renders the login page if no user set, logged in page if user is set
    case ("/"):
        session_start();
        if (isset($_SESSION['first_name'])) {
            echo $twig->render("login_successful.html.twig", ['raw_profile' => json_encode($_SESSION['profile'], JSON_PRETTY_PRINT), 'first_name' => $_SESSION['first_name']]);
        } else {
            echo $twig->render("login.html.twig");
        }
        return true;

        // /logout clears and ends the session
    case ("/logout"):
        session_start();
        session_unset();
        session_destroy();
        header('Location: ' . '/', true, 302);
        return true;

    default:
        return httpNotFound();
}

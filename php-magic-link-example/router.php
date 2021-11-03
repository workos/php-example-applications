<?php

require __DIR__ . "/vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

//Set API Key, ClientID, Connection, and/or domain
$WORKOS_API_KEY = "";
$WORKOS_CLIENT_ID = "";

// Setup html templating library
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

// Configure WorkOS with API Key and Client ID 
\WorkOS\WorkOS::setApiKey($WORKOS_API_KEY);
\WorkOS\WorkOS::setClientId($WORKOS_CLIENT_ID);

// // Convenient function for throwing a 404
// function httpNotFound() {
//     header($_SERVER["SERVER_PROTOCOL"] . " 404");
//     return true;
// }

// Convenient function for redirecting to  URL
function Redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}

// Routing
switch (strtok($_SERVER["REQUEST_URI"], "?")) {
    case (preg_match("/\.css$/", $_SERVER["REQUEST_URI"]) ? true: false): 
        $path = __DIR__ . "/static/css" .$_SERVER["REQUEST_URI"];
        if (is_file($path)) {
           // header("Content-Type: text/css");
            header("Content-Type: image/png");
            readfile($path);
            return true;
        }
        return httpNotFound();

    //Declare main and /login routes which renders templates/generate.html
    case ("/"):
        echo $twig->render("generate.html");
        return true; 

    case ("/callback"):
        $code = $_GET["code"];
        echo $twig->render("success.html");
        $profileAndToken = (new \WorkOS\SSO())->getProfileAndToken($code);

        // Use the information in `profile` for further business logic.
        $profile = $profileAndToken->profile;
        echo json_encode($profile);
        return true;
 
    case ("/passwordless-auth"):
        // Email of the user to authenticate
        echo $twig->render("email-sent.html");
        $email = $_POST["email"];
        $passwordless = new \WorkOS\Passwordless();

        // Generate a session for passwordless
        $session = $passwordless->createSession(
            $email,
            'http://localhost:8000/callback',
            null,
            'MagicLink',
            null,
            null
        );

        // Send an email to the user via WorkOS with the link to authenticate
        $passwordless->sendSession($session);

    // all other routes don't return anything
    default:
        return true;
}
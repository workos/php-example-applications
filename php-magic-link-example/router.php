<?php

require __DIR__ . "/vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Set API Key, ClientID, and Connection
$WORKOS_API_KEY = $_ENV['WORKOS_API_KEY'];
$WORKOS_CLIENT_ID = $_ENV['WORKOS_CLIENT_ID'];

// Setup html templating library
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

// Configure WorkOS with API Key and Client ID
\WorkOS\WorkOS::setApiKey($WORKOS_API_KEY);
\WorkOS\WorkOS::setClientId($WORKOS_CLIENT_ID);

// Convenient function for redirecting to  URL
function Redirect($url, $permanent = false)
{
    if (headers_sent() === false) {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}

// Routing
switch (strtok($_SERVER["REQUEST_URI"], "?")) {
    case (preg_match("/\.css$/", $_SERVER["REQUEST_URI"]) ? true : false):
        $path = __DIR__ . "/static/css" .$_SERVER["REQUEST_URI"];
        if (is_file($path)) {
            // header("Content-Type: text/css");
            header("Content-Type: image/png");
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

        //Declare main and /login routes which renders templates/generate.html
    case ("/"):
        echo $twig->render("generate.html"); 
        return true;

    case ("/callback"):
        $code = $_GET["code"];

        $profileAndToken = (new \WorkOS\SSO())->getProfileAndToken($code);

        // Use the information in `profile` for further business logic.
        $profile = json_encode($profileAndToken->profile, JSON_PRETTY_PRINT);
        echo $twig->render("success.html", ['profile' => $profile]);
        return true;

    case ("/passwordless-auth"):
        // Email of the user to authenticate

        $email = $_POST["email"];
        $passwordless = new \WorkOS\Passwordless();

        // Generate a session for passwordless
        $session = $passwordless->createSession(
            $email,
            'http://localhost:8000/callback',
            '',
            'MagicLink',
            '',
            null
        );

        // Send an email to the user via WorkOS with the link to authenticate
        $passwordless->sendSession($session);
        $link = $session->link;
        echo $twig->render("email-sent.html", ['link' => $link, 'email' => $email]);

        // all other routes don't return anything
        // no break
    default:
        return true;
}

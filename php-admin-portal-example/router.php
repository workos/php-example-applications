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

// Convenient function for throwing a 404
function httpNotFound() {
    header($_SERVER["SERVER_PROTOCOL"] . " 404");
    return true;
}

// Convenient function for redirecting to  URL
function Redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}

// Convenient function to transform an object to an associative array
function objectToArray($d) 
{
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    } else {
        // Return array
        return $d;
    }
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

    case (preg_match("/\.png$/", $_SERVER["REQUEST_URI"]) ? true: false): 
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
    case ("/portal"):
        $orgId = $_POST['org'];
        $sessionIntent = $_POST['intent_selector'];
        $linkPayloadObject = (new \WorkOS\Portal()) -> generateLink($orgId, $sessionIntent);
        $linkPayloadArray = objectToArray($linkPayloadObject);
        $linkPayloadArrayRawData = $linkPayloadArray['raw'];
        $finalLink = $linkPayloadArrayRawData['link'];
        Redirect($finalLink, false);
        return true;
//else return  HTTP 404 Error
    default:
        return httpNotFound();
}
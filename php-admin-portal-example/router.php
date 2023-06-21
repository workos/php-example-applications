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

// Setup html templating library
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

// Configure WorkOS with API Key and Client ID
\WorkOS\WorkOS::setApiKey($WORKOS_API_KEY);

// Convenient function for throwing a 404
function httpNotFound()
{
    header($_SERVER["SERVER_PROTOCOL"] . " 404");
    return true;
}

// Convenient function for redirecting to  URL
function Redirect($url, $permanent = false)
{
    if (headers_sent() === false) {
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
        echo $twig->render("index.html");
        return true;

    case ("/provision_enterprise"):
        //use a session for the org_id
        session_start();
        $org_id;
        $organization_name = $_POST["org"] ?? null;
        $organization_domains = $_POST["domain"] ?? null;

        $orgs = (new \WorkOS\Organizations())->listOrganizations(domains: [$organization_domains]);

        if (!empty($orgs["data"])) {
            $org_id = $orgs["data"][0]["id"];
        } else {
            $organization = (new \WorkOS\Organizations())->createOrganization(
                name: $organization_name,
                domains: [$organization_domains]
            );
            // $org_id = $organization["id"];
            $organizationArr = objectToArray($organization);
            $organizationArrRaw = $organizationArr['raw'];
            $org_id = $organizationArrRaw["id"];
        }
        $_SESSION['org_id'] = $org_id;
        echo $twig->render("org_logged_in.html");
        return true;

    case ("/launch_admin_portal"):
        $value = $_GET['value'];
        session_start();
        $linkPayloadObject = (new \WorkOS\Portal()) -> generateLink($_SESSION['org_id'], $value);
        $linkPayloadArray = objectToArray($linkPayloadObject);
        $linkPayloadArrayRawData = $linkPayloadArray['raw'];
        $finalLink = $linkPayloadArrayRawData['link'];
        Redirect($finalLink, false);
        return true;
        //else return  HTTP 404 Error

    default:
        return httpNotFound();
}

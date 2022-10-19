<?php 

require __DIR__ . "/vendor/autoload.php";
include './auditLogEvents.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Set API Key, ClientID, and Connection
$WORKOS_API_KEY = $_ENV['WORKOS_API_KEY'];
$WORKOS_CLIENT_ID = $_ENV['WORKOS_CLIENT_ID']; 
$WORKOS_CONNECTION_ID = "conn_01GDXFAY0TACJK0BMK3WXFFHR6";


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


//debugging function DELETE LATER
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);
    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
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


// /set_org route is what will set organization
    case ("/"):
    case ("/login"):
    case ("/logout"):
        echo $twig->render("login.html.twig");
        return true;

    case ("/set_org"):
        $organizationId = $_POST['org'];
        $organization = (new \WorkOS\Organizations()) -> getOrganization($organizationId);
        $orgPayloadArray = objectToArray($organization);
        $orgPayloadArrayRawData = $orgPayloadArray['raw'];
        $finalOrgId = $orgPayloadArrayRawData['id'];
        echo $twig->render("send_events.html.twig", ['org_id' => $finalOrgId]);
        return true;

    default:
        return httpNotFound();
} 

?>

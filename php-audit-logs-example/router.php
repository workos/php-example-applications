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

//Routing
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


//set_org route is what will set organization
    case ("/"):
    case ("/login"):
    case ("/logout"):
        echo $twig->render("login.html.twig");
        return true;

    case ("/set_org"):
        $organizationId = $_POST["org"] ?? "";
        $organization = (new \WorkOS\Organizations()) -> getOrganization($organizationId);
        $orgPayloadArray = objectToArray($organization);
        $orgPayloadArrayRawData = $orgPayloadArray['raw'];
        $finalOrgId = $orgPayloadArrayRawData["id"] ?? "";
        $orgName = $orgPayloadArrayRawData["name"] ?? "";
        session_start();
        $_SESSION['id'] = $finalOrgId;
        $_SESSION['name'] = $orgName;
        echo $twig->render("send_events.html.twig", ['org_id' => $_SESSION['id'], 'org_name' => $orgName]); 
        return true;

//send_event
    case ("/send_event"):
        session_start();
        $payload = file_get_contents("php://input");
        $eventId = $payload[6];
        $event;
        if($eventId === '0'){
            $event = $user_signed_in;
        } else if($eventId === '1'){
            $event = $user_logged_out;
        } else if($eventId === '2'){
            $event = $user_organization_deleted;
        } else if($eventId === '3'){
            $event = $user_connection_deleted;
        }

        $orgId = $_SESSION['id'];
        $orgName = $_SESSION['name'];

        $auditLogsEvent = (new \WorkOS\AuditLogs()) -> createEvent(
            organizationId: $orgId,
            event: $event
        );

        echo $twig->render("send_events.html.twig", ['org_id' => $_SESSION['id'], 'org_name' => $_SESSION['name']]); 
        return true;

//export_events
    case ("/export_events"):
        session_start();
        $payload = file_get_contents("php://input");
        $orgId = $_SESSION['id'];
        $orgName = $_SESSION['name'];
        echo $twig->render("export_events.html.twig", ['org_id' => $orgId, 'org_name' => $orgName]); 
        return true;




    default:
        return httpNotFound();

} 

?>

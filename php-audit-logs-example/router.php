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

// Convenient function for redirecting to  URL
function Redirect($url, $permanent = false)
{
    if (headers_sent() === false) {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
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

//List Organizations
    case ("/"):
    case ("/login"):
        session_start();
        $before = $_GET['before'] ?? "";
        $after = $_GET['after'] ?? "";
        $listOrganizations = new WorkOS\Organizations();
        [$before, $after, $currentPage] = $listOrganizations->listOrganizations(
            limit: 5,
            before: $before,
            after: $after,
            order: null
        );
        $organizations = $currentPage;
        echo $twig->render("login.html.twig", ['organizations' => $organizations, 'after' => $after, 'before' => $before]);
        return true;

//set_org
    case ("/set_org"):
        session_start();
        $organizationId = $_GET["id"] ?? "";
        $organization = (new \WorkOS\Organizations()) -> getOrganization($organizationId);
        $orgPayloadArray = objectToArray($organization);
        $orgPayloadArrayRawData = $orgPayloadArray['raw'];
        $finalOrgId = $orgPayloadArrayRawData["id"] ?? "";
        $orgName = $orgPayloadArrayRawData["name"] ?? "";
        $_SESSION['id'] = $finalOrgId;
        $_SESSION['name'] = $orgName;
        $rangeEnd = (new \DateTime('now',new \DateTimeZone("UTC")))->format(\DateTime::ATOM);
        $rangeStart = (new \DateTime('-1 month',new \DateTimeZone("UTC")))->format(\DateTime::ATOM);
        echo $twig->render("send_events.html.twig", ['org_id' => $_SESSION['id'], 'org_name' => $orgName, 'rangeStart' => $rangeStart, 'rangeEnd' => $rangeEnd]); 
        return true;

//send_event
    case ("/send_event"):
        session_start();
        $action = $_POST['event-action'];
        $version = $_POST['event-version'];
        $actorName = $_POST['actor-name'];
        $actorType = $_POST['actor-type'];
        $targetName = $_POST['target-name'];
        $targetType = $_POST['target-type'];
        $event = [ 
            "action" => $action,
            "occurred_at" => date("c"),
            "version" => (int)$version,
            "actor" => [
                "id" => "user_01GBNJC3MX9ZZJW1FSTF4C5938",
                "name" => $actorName,
                "type" => $actorType,
            ],
            "targets" => [
                [
                    "id" => "team_01GBNJD4MKHVKJGEWK42JNMBGS",
                    "name" => $targetName,
                    "type" => $targetType,
                ],
            ],
            "context" => [
                "location" => "123.123.123.123",
                "user_agent" => "Chrome/104.0.0.0",
            ],
        ];

        $orgId = $_SESSION['id'];
        $orgName = $_SESSION['name'];

        $auditLogsEvent = (new \WorkOS\AuditLogs()) -> createEvent(
            organizationId: $orgId,
            event: $event
        );

        echo $twig->render("send_events.html.twig", ['org_id' => $_SESSION['id'], 'org_name' => $_SESSION['name']]); 
        return true;

//generate_csv
    case ("/get_events"):
        session_start();
        $payload = file_get_contents("php://input");
        $orgId = $_SESSION['id'];
        $orgName = $_SESSION['name'];
        $event = $_POST["event"] ?? "";
        $dateNow = (new \DateTime('now',new \DateTimeZone("UTC")))->format(\DateTime::ATOM);
        $dateMonth = (new \DateTime('-1 month',new \DateTimeZone("UTC")))->format(\DateTime::ATOM);
        $auditId;

        if($event === "generate_csv"){
            $createExport = (new \WorkOS\AuditLogs()) -> createExport(
                organizationId: $orgId,
                rangeStart: $dateMonth,
                rangeEnd: $dateNow
            );
            $orgPayloadArray = objectToArray($createExport);
            $orgPayloadArrayRawData = $orgPayloadArray['raw'];
            $auditId = $orgPayloadArrayRawData["id"] ?? "";
            $_SESSION['Auditid'] = $auditId;
        } 
        
        if($event === "access_csv"){
            $fetchExport = (new \WorkOS\AuditLogs()) -> getExport(
                $_SESSION['Auditid']
            );
            $orgPayloadArray = objectToArray($fetchExport);
            $orgPayloadArrayRawData = $orgPayloadArray['raw'];
            $url = $orgPayloadArrayRawData["url"] ?? "";
            $source = file_get_contents($url);
            //Add your path below to Download CSV to your computer's downloads folder
            file_put_contents('/Users/[YOUR USERNAME HERE]/Downloads/auditlogs.csv', $source);
        }
        echo $twig->render("send_events.html.twig", ['org_id' => $orgId, 'org_name' => $orgName, 'rangeStart' => $dateNow, 'rangeEnd' => $dateMonth]); 
        return true;

//events
    case ("/events"):
        session_start();
        $intent = $_GET['intent'];
        $orgId = $_SESSION['id'];
        $linkPayloadObject = (new \WorkOS\Portal())->generateLink(
            organization: $orgId,
            intent: $intent
        );
        $linkPayloadArray = objectToArray($linkPayloadObject);
        $linkPayloadArrayRawData = $linkPayloadArray['raw'];
        $finalLink = $linkPayloadArrayRawData['link'];
        Redirect($finalLink, false);
        echo $twig->render("send_events.html.twig", ['org_id' => $orgId]); 
        return true;


//change_org
    case ("/logout"):
        session_start();
        $_SESSION['organizations'] = null;
        $_SESSION['before'] = null;
        $_SESSION['after'] = null;
        Redirect('/', false);
        echo $twig->render("login.html.twig");
        return true;



    default:
        return httpNotFound();

} 

?>

<?php

require __DIR__ . "/vendor/autoload.php"; 
error_reporting(E_ALL ^ E_WARNING);
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

include './variables.php';

// Setup html templating library
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Set API Key, ClientID, and Connection
$WORKOS_API_KEY = $_ENV['WORKOS_API_KEY'];
$WORKOS_CLIENT_ID = $_ENV['WORKOS_CLIENT_ID'];
$WORKOS_WEBHOOKS_SECRET = $_ENV['WORKOS_WEBHOOKS_SECRET'];

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

        // home and /login will display the login page
    case ("/"):
        session_start();
        $before = $_GET['before'] ?? "";
        $after = $_GET['after'] ?? "";
        $directoriesList = new WorkOS\DirectorySync();
        [$before, $after, $currentPage] = $directoriesList->listDirectories(
            limit: 5,
            before: $before,
            after: $after,
            order: null
        );
        $parsedDirectories = $currentPage;
        echo $twig->render("login.html.twig", ['directories' => $parsedDirectories, 'after' => $after, 'before' => $before]);
        return true;

        //Directory endpoint
    case ("/directory"):
        session_start();
        $directoryId = htmlspecialchars($_GET["id"]);
        $directory = (new \WorkOS\DirectorySync())
            ->getDirectory(
                $directoryId
            );
        $parsed_directory = json_encode($directory, JSON_PRETTY_PRINT);
        $directoryPayloadArray = objectToArray($directory);
        $directoryPayloadArrayRawData = $directoryPayloadArray['raw'];
        $directoryName = $directoryPayloadArrayRawData["name"] ?? "";
        $directoryType = $directoryPayloadArrayRawData["type"] ?? "";
        $directoryDomain = $directoryPayloadArrayRawData["domain"] ?? "";
        $directoryCreated = $directoryPayloadArrayRawData["created_at"] ?? "";
        $_SESSION['id'] = $directoryId;
        echo $twig->render('directory.html.twig', ['directory' => $parsed_directory, 'id' => $_SESSION['id'], 'name'=> $directoryName, 'type'=>$directoryType, 'domain'=>$directoryDomain, 'created_at'=>$directoryCreated]);
        return true;

        //Groups & Users endpoint for listGroups & listUsers function
    case ("/users"):
        session_start();
        $directoryId = $_GET["id"];
        $directory = (new \WorkOS\DirectorySync())
        ->getDirectory(
            $directoryId
        );
        $parsed_directory = json_encode($directory, JSON_PRETTY_PRINT);
        $directoryPayloadArray = objectToArray($directory);
        $directoryPayloadArrayRawData = $directoryPayloadArray['raw'];
        $directoryName = $directoryPayloadArrayRawData["name"] ?? "";
        $_SESSION['directoryName'] = $directoryName;
        [$before, $after, $users] = (new \WorkOS\DirectorySync())
            ->listUsers(
                $directoryId
            );
        $usersArr = objectToArray($users);
        echo $twig->render('users.html.twig', ['users' => $usersArr, 'name' => $_SESSION['directoryName'], 'directory' => $directoryId]); 
        return true;

    case ("/groups"):
        session_start();
        $directoryId = $_GET["id"];
        $directory = (new \WorkOS\DirectorySync())
        ->getDirectory(
            $directoryId
        );
        $parsed_directory = json_encode($directory, JSON_PRETTY_PRINT);
        $directoryPayloadArray = objectToArray($directory);
        $directoryPayloadArrayRawData = $directoryPayloadArray['raw'];
        $directoryName = $directoryPayloadArrayRawData["name"] ?? "";
        $_SESSION['directoryName'] = $directoryName;
        [$before, $after, $groups] = (new \WorkOS\DirectorySync())
            ->listGroups(
                $directoryId
            );
        $groupsArr = objectToArray($groups);
        echo $twig->render('groups.html.twig', ['groups' => $groupsArr, 'name' => $_SESSION['directoryName'], 'directory' => $directoryId]); 
        return true;


        //Webhooks endpoint
    case ("/webhooks"):
        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER["HTTP_WORKOS_SIGNATURE"];

        if ($payload && $sigHeader) {
            $webhook = (new \WorkOS\Webhook())
            ->constructEvent($sigHeader, $payload, $WORKOS_WEBHOOKS_SECRET, 180);

            error_log(print_r($WORKOS_ASCII, true));
            error_log(print_r("↓↓↓↓ PRE-VALIDATION ↓↓↓↓", true));
            error_log(print_r($payload, true));
            error_log(print_r("↓↓↓↓ POST-VALIDATION ↓↓↓↓", true));
            error_log(print_r($webhook, true));

            echo $twig->render('webhooks.html.twig', ['webhook' => json_encode($data)]);
            return true;
        }

        echo $twig->render('webhooks.html.twig');
        return true;

        // Any other endpoint returns a 404
    default:
        return httpNotFound();
}

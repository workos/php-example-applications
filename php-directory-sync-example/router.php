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

        //Users endpoint for listUsers function, simply prints first 10 users to the page
    case ("/users"):
        $directoryId = htmlspecialchars($_GET["id"]);
        $usersList = (new \WorkOS\DirectorySync())
            ->listUsers(
                $directoryId
            );
        $users = json_encode($usersList, JSON_PRETTY_PRINT);
        echo $twig->render('users.html.twig', ['users' => $users]);
        return true;

        //Groups endpoint for listGroups function, simply prints groups to the page
    case ("/groups"):
        $directoryId = htmlspecialchars($_GET["id"]);
        $groupsList = (new \WorkOS\DirectorySync())
            ->listGroups(
                $directoryId
            );
        $groups = json_encode($groupsList, JSON_PRETTY_PRINT);
        echo $twig->render('groups.html.twig', ['groups' => $groups]);
        return true;

        //Directory endpoint
    case ("/directory"):
        $directoryId = htmlspecialchars($_GET["id"]);
        $directory = (new \WorkOS\DirectorySync())
            ->getDirectory(
                $directoryId
            );
        $parsed_directory = json_encode($directory, JSON_PRETTY_PRINT);
        echo $twig->render('directory.html.twig', ['directory' => $parsed_directory, 'id' => $directoryId]);
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

        // home and /login will display the login page
    case ("/"):
    case ("/login"):
        $directoriesList = (new \WorkOS\DirectorySync())
            ->listDirectories();
        $parsedDirectories = $directoriesList[2];
        echo $twig->render("login.html.twig", ['directories' => $parsedDirectories]);
        return true;

        // Any other endpoint returns a 404
    default:
        return httpNotFound();
}

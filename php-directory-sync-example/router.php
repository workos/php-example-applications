<?php

require __DIR__ . "/vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

//Set API Key, ClientID, Connection, and/or domain
$WORKOS_API_KEY = "";
$WORKOS_CLIENT_ID = "";
$WORKOS_DIRECTORY_ID = "";

// Setup html templating library
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

// Configure WorkOS with API Key and Client ID 
\WorkOS\WorkOS::setApiKey($WORKOS_API_KEY);
\WorkOS\WorkOS::setClientId($WORKOS_CLIENT_ID);

// Convenient function for throwing a 404
function httpNotFound() {
    header($_SERVER["SERVER_PROTOCOL"] . " 404");
    return true;
}

// Routing
switch (strtok($_SERVER["REQUEST_URI"], "?")) {
    case (preg_match("/\.css$/", $_SERVER["REQUEST_URI"]) ? true: false): 
        $path = __DIR__ . "/static/css" .$_SERVER["REQUEST_URI"];
        if (is_file($path)) {
            header("Content-Type: text/css");
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

    //Users endpoint for listUsers function, simply prints first 10 users to the page
    case ("/users"):
        $usersList = (new \WorkOS\DirectorySync())
            ->listUsers(
                $WORKOS_DIRECTORY_ID
            ); 
        $users = json_encode($usersList);
        echo $twig->render('users.html.twig', ['users' => $users]);
        return true;
        
    //Groups endpoint for listGroups function, simply prints groups to the page
    case ("/groups"):
        $groupsList = (new \WorkOS\DirectorySync())
            ->listGroups(
                $WORKOS_DIRECTORY_ID
            );        
        $groups = json_encode($groupsList);
        echo $twig->render('groups.html.twig', ['groups' => $groups]);
        return true;
 
    // home and /login will display the login page       
    case ("/"):
    case ("/login"):
        echo $twig->render("login.html.twig");
        return true;
    // Any other endpoint returns a 404 
    default:
        return httpNotFound();
}
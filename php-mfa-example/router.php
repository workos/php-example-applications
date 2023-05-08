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

// Configure WorkOS with API Key and Client ID
\WorkOS\WorkOS::setApiKey($WORKOS_API_KEY);
\WorkOS\WorkOS::setClientId($WORKOS_CLIENT_ID);

//function to redirect to a URL
function Redirect($url, $permanent = false)
{
    if (headers_sent() === false) {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}

// Convenient function for throwing a 404
function httpNotFound()
{
    header($_SERVER["SERVER_PROTOCOL"] . " 404");
    return true;
}

function objectToArray($obj) {
    $arr = [];
    $reflection = new ReflectionClass($obj);
    $properties = $reflection->getProperties();
    foreach ($properties as $property) {
        $name = $property->getName();
        $property->setAccessible(true);
        $value = $property->getValue($obj);
        if (is_object($value)) {
            $value = objectToArray($value);
        }
        $arr[$name] = $value;
    }
    return $arr;
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

        // '/' route is what will be the home page, allow users to get started creating an MFA factor.

    case ("/"):
        session_start();
        $_SESSION['factorArray'];
        echo $twig->render("index.html.twig", ['factors' => $_SESSION['factorArray']]);

        return true;
    
    case ("/clear_session"):
        session_start();
        $_SESSION['factorArray'] = [];
        Redirect('/', false);
        echo $twig->render("index.html.twig");
        return true;

        // '/factor_details' enrolls and displays the factor that was selected in the previous step. Allows user
        //  to use to authenticate the factor
    
    case ("/enroll_factor"):
        session_start();
        echo $twig->render("enroll_factor.html.twig");
        return true;
    
    case ("/enroll_new_factor"): 
        session_start();
        $factor_type = $_POST["type"];
        $new_factor;
        if (!isset($_SESSION['factorArray'])) {
            $_SESSION['factorArray'] = array();
        }
    
        if ($factor_type == "sms") {
            $factor_type = "sms";
            $phoneNumber = $_POST["phone_number"];
            $USnumber = "+1" . $phoneNumber;
            $new_factor = (new \WorkOS\MFA())
                ->enrollFactor(
                    type: $factor_type,
                    phoneNumber: $USnumber
                );
        } elseif ($factor_type == "totp") {
            $factor_type = "totp";
            $totpIssuer = $_POST['totp_issuer'];
            $totpUser = $_POST['totp_user'];
            $new_factor = (new \WorkOS\MFA())
                ->enrollFactor(
                    type: $factor_type,
                    totpIssuer: $totpIssuer,
                    totpUser: $totpUser
                );
        }
        $data = objectToArray($new_factor);
        array_push($_SESSION['factorArray'], $data);

        echo $twig->render("index.html.twig", ['factors' => $_SESSION['factorArray']]);
        return true;
    

    case ("/factor_detail"):
        session_start();
        $id = $_GET['id'];
        $factor = null;
        foreach ($_SESSION['factorArray'] as $f) {
            $item = $f['values']['id'];
            if($item === $id){
                $factor = $f;
            }
        }
        $_SESSION['current_factor'] = $factor;
        $_SESSION['id'] = $id;
        echo $twig->render("factor_detail.html.twig", ['factor' => $_SESSION['current_factor'], "title" => 'Factor Detail']);
        return true;

        //  '/challenge_factor' will allow the user to select to challenge the factor they created by inputting
        //   what should be the correct code
    case ("/challenge_factor"):
        session_start();
        echo $twig->render("challenge_factor.html.twig");

        if (isset($_POST['sms_message'])):
            $smsMessage = $_POST['sms_message'];
        else:
            $smsMessage = null;
        endif;

        $challengeFactor = (new \WorkOS\MFA()) -> challengeFactor($_SESSION['id'], $smsMessage);
        $challengeFactorArr = objectToArray($challengeFactor);
        $_SESSION['authentication_challenge_id'] = $challengeFactorArr['values']['id'];
        return true;

    case ("/challenge_success"):
        session_start();
        echo $twig->render("challenge_success.html.twig",['title' => 'Challenge Success']);
        return true;

//     'challenge_success' will display whether or not the user successfully passed the challenge, and allow a return to
//      the home page.

    case ("/verify_factor"):
        session_start();

        if (isset($_POST['code-1'])){
            $codeArray = [];
            foreach($_POST as $key => $value) {
                array_push($codeArray, $value);
            }
            $code = implode($codeArray);
        }else{
            $code = null;
         }

        $verifyFactor = (new \WorkOS\MFA()) -> verifyChallenge($_SESSION['authentication_challenge_id'], $code);
        $verifyFactorArr = objectToArray($verifyFactor);
        $valid = $verifyFactorArr['values']['valid'];
        $authFactor = $_SESSION['current_factor'];
        $expires_at = $verifyFactorArr['values']["challenge"]['expires_at'];
        $created_at = $verifyFactorArr['values']["challenge"]['created_at'];
        echo $twig->render("challenge_success.html.twig", ['authentication_factor_id' => $authFactor, 'valid' => $valid, 'created_at' => $created_at, 'updated_at' => $expires_at]);
        return true;

        // no break
    default:
        return httpNotFound();
}

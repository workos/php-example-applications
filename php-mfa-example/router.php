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

        echo $twig->render("enroll_factor.html.twig");

        return true;

        // '/factor_details' enrolls and displays the factor that was selected in the previous step. Allows user
        //  to use to authenticate the factor

    case ("/factor_detail"):
        session_start();

        $factorType = $_POST['type'];

        if (isset($_POST['phone_number'])):
            $phoneNumber = $_POST['phone_number'];
        else :
            $phoneNumber = null;
        endif;

        if (isset($_POST['totp_issuer'])) {
            $totpIssuer = $_POST['totp_issuer'];
        }

        if (isset($_POST['totp_user'])) {
            $totpUser = $_POST['totp_user'];
        }

        if ($factorType == "sms") {
            $newFactor = (new \WorkOS\MFA()) -> enrollFactor($factorType, null, null, $phoneNumber);
            $phoneNumber = $newFactor->sms['phone_number'];
            // $environment = $newFactor->environmentId;
            $type = $newFactor->type;
        }

        if ($factorType == "totp") {
            $newFactor = (new \WorkOS\MFA()) -> enrollFactor($factorType, $totpIssuer, $totpIssuer, null);
            $type = $newFactor->type;
            // $environment = $newFactor->environmentId;
            $qrCode = $newFactor->totp['qr_code'];
        }

        if (!isset($_SESSION['factor_list'])) {
            $_SESSION['factor_list'] = array();
            array_push($_SESSION['factor_list'], $newFactor);
        } else {
            array_push($_SESSION['factor_list'], $newFactor);
        }

        $authenticationFactorId = $newFactor->id;
        $createdAt = $newFactor->createdAt;

        $_SESSION['authentication_factor_id'] = $authenticationFactorId;

        if ($type == 'sms'):
            echo $twig->render("factor_detail.html.twig", ['factor_list' => json_encode($_SESSION['factor_list']), 'phone_number' => $phoneNumber, 'type' => $type, 'authentication_factor_id' => $authenticationFactorId, 'code' => "{{code}}", 'created_at' => $createdAt]);
        elseif ($type == 'totp'):
            echo $twig->render("factor_detail.html.twig", ['factor_list' => json_encode($_SESSION['factor_list']), 'type' => $type, 'authentication_factor_id' => $authenticationFactorId, 'qr_code' => $qrCode, 'code' => "{{code}}", 'created_at' => $createdAt]);
        endif;

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

        $challengeFactor = (new \WorkOS\MFA()) -> challengeFactor($_SESSION['authentication_factor_id'], $smsMessage);

        $_SESSION['authentication_challenge_id'] = $challengeFactor->id;

        return true;

//     'challenge_success' will display whether or not the user successfully passed the challenge, and allow a return to
//      the home page.

    case ("/challenge_success"):
        session_start();

        if (isset($_POST['code-1'])):
            $codeArray = [];
            foreach($_POST as $key => $value) {
                array_push($codeArray, $value);
            }
            $code = implode($codeArray);

        else:
            $code = null;
        endif;

        $verifyFactor = (new \WorkOS\MFA()) -> verifyFactor($_SESSION['authentication_challenge_id'], $code);

        $valid = json_encode($verifyFactor->valid);

        echo $twig->render("challenge_success.html.twig", ['authentication_factor_id' => json_encode($_SESSION['authentication_factor_id']), 'valid' => $valid]);

        // no break
    default:
        return httpNotFound();
}

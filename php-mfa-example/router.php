<?php

require __DIR__ . "/vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

//Set API Key, ClientID, and Connection
$WORKOS_API_KEY = "sk_test_a2V5XzAxRkI3VzVZWVpUUktQMkJGUTE4QldYMEVOLG1NMVJzd3gzU0NRS2lhWU0wc21rRW1tUVc";
$WORKOS_CLIENT_ID = "project_01EGKAEB7G5N88E83MF99J785F";

// Setup html templating library
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

// Configure WorkOS with API Key and Client ID 
\WorkOS\WorkOS::setApiKey($WORKOS_API_KEY);
\WorkOS\WorkOS::setClientId($WORKOS_CLIENT_ID);

//function to redirect to a URL
function Redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}

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

// /auth route is what will run the getAuthorizationUrl function

/* There are 6 parameters for the GetAuthorizationURL Function
Domain (deprecated), Redirect URI, State, Provider, Connection and Organization
These can be read about here: https://workos.com/docs/reference/sso/authorize/get
We recommend using Connection (pass a connectionID) */


    // /callback route is what will run the getProfileAndToken function and return it
    

    // / route renders the login page if no user set, logged in page if user is set
    case ("/"):
        session_start();  
        session_unset();
        session_destroy();

        echo $twig->render("list_factors.html.twig");
    

        return true;
    
    // /logout clears and ends the session
    case ("/enroll_factor_details"):
        session_start();
        echo $twig->render("enroll_factor.html.twig");  
        return true;

    case ("/factor_detail"):
        session_start();
        $factorType = $_POST['type'];
        if (isset($_POST['phone_number'])):
            $phoneNumber = $_POST['phone_number'];
        else :
            $phoneNumber = NULL;
        endif;
        if (isset($_POST['totp_issuer']))
            $totpIssuer = $_POST['totp_issuer'];
        if (isset($_POST['totp_user']))
            $totpUser = $_POST['totp_user'];
        //echo $factorType;
        
        if ($factorType == "sms") {
            $newFactor = (new \WorkOS\MFA()) -> enrollFactor($factorType, null, null, $phoneNumber);
            $phoneNumber = $newFactor->sms['phone_number'];
            $environment = $newFactor->environmentId;
            $type = $newFactor->type;}

        if ($factorType == "totp"){
            $newFactor = (new \WorkOS\MFA()) -> enrollFactor($factorType, $totpIssuer, $totpIssuer, null);
            $type = $newFactor->type;
            $environment = $newFactor->environmentId;
            $qrCode = $newFactor->totp['qr_code'];}
            
        if (!isset($_SESSION['factor_list'])) {
            $_SESSION['factor_list'] = array();
            array_push($_SESSION['factor_list'], $newFactor);
        }
        else {
            array_push($_SESSION['factor_list'], $newFactor);
        }

        $authenticationFactorId = $newFactor->id;

        $_SESSION['authentication_factor_id'] = $authenticationFactorId;
        if($type == 'sms'):
        echo $twig->render("factor_detail.html.twig", ['factor_list' => json_encode($_SESSION['factor_list']), 'phone_number' => $phoneNumber, 'environment' => $environment, 'type' => $type, 'authentication_factor_id' => $authenticationFactorId, 'code' => "{{code}}"]);
        elseif($type == 'totp'):
        echo $twig->render("factor_detail.html.twig", ['factor_list' => json_encode($_SESSION['factor_list']), 'environment' => $environment, 'type' => $type, 'authentication_factor_id' => $authenticationFactorId, 'qr_code' => $qrCode, 'code' => "{{code}}"]);
        endif;
        
        return true;

        case ("/challenge_factor"):
            session_start();
            echo $twig->render("challenge_factor.html.twig");
            
            if (isset($_POST['sms_message'])):
                $smsMessage = $_POST['sms_message'];
            else:
                $smsMessage = NULL;
            endif;

            $challengeFactor = (new \WorkOS\MFA()) -> challengeFactor($_SESSION['authentication_factor_id'], $smsMessage);
            $_SESSION['authentication_challenge_id'] = $challengeFactor->id;
            return true;   
        
        case ("/challenge_success"):
            session_start();

            if (isset($_POST['code'])):
                $code = $_POST['code'];
            else:
                $code = NULL;
            endif;

            $verifyFactor = (new \WorkOS\MFA()) -> verifyFactor($_SESSION['authentication_challenge_id'], $code);
            $valid = json_encode($verifyFactor->valid); 
            echo $twig->render("challenge_success.html.twig", ['authentication_factor_id' => json_encode($_SESSION['authentication_factor_id']), 'valid' => $valid]);
    default:
        return httpNotFound();
}
<?php

$debug = preg_match("/debug/", $_SERVER['SCRIPT_FILENAME']);
require "regexs.php";
require "mail/core.php";


$action = getRes('action');
$response = array();
$credinals = json_decode(file_get_contents("credinals.json"), true);
$connect = mysqli_connect($credinals['mySQL']['host'], $credinals['mySQL']['account']['user'], $credinals['mySQL']['account']['password'],
    $debug ? $credinals['mySQL']['account']['database'] . "debug" : $credinals['mySQL']['account']['database'],
    $credinals['mySQL']['port']);
if (!$connect) {
    $response['error']['text'] = "Error on database server";
    $response['error']['code'] = 0x01;
} else {
    $connect->query("SET NAMES 'utf8'");
    require "account.php";
    require "groups.php";

    switch ($action) {
        case "login":
        {
            $login = getRes('login');
            $password = getRes('password');
            $user = getUser($login, $connect);
            if ($user == null) {
                $response['error']['text'] = "Wrong username or email";
                $response['error']['code'] = 0x05;
            } else {
                if (password_verify($password, $user['password'])) {
                    $response['response']['user_info'] = $user;
                } else {
                    $response['error']['text'] = "Wrong password";
                    $response['error']['code'] = 0x04;
                }
            }
            break;
        }
        case "register":
        {
            /*login, email, password, names, spam*/
            $login = getRes('login');
            $email = getRes('email');
            $password = getRes('password');
            $names = getRes('names');
            $spam = getRes('spam');
            $user = getUser($login, $connect);
            if ($user == null) {
                $user = getUser($email, $connect);
                if ($user == null) {
                    if (preg_match($loginRegex, $login)
                        && filter_var($email, FILTER_VALIDATE_EMAIL)
                        && preg_match($nameRegex, $names)
                        && strlen($password) >= 6 && strlen($password) <= 32) {
                        $token = generateToken();
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        registerUser($connect, $login, $email, $password_hash, $names, $spam, $token);
                        $user = getUser($login, $connect);
                        $response['response']['user_data'] = $user;
                        initMail();
                        setAddress($user['email']);
                        sendMail(0);
                    } else {
                        $response['error']['text'] = "Wrong user data";
                        $response['error']['code'] = 0x06;
                    }
                } else {
                    $response['error']['text'] = "This email is already registered";
                    $response['error']['code'] = 0x07;
                }
            } else {
                $response['error']['text'] = "The user is already registered";
                $response['error']['code'] = 0x08;
            }
            break;
        }
        case "getGroups": {
            $token=getRes('token');
            $name=getRes('name');
            $city=getRes('city');
            $building=getRes('building');
            $confirmed=getRes('confirmed');
            if(checkToken($connect, $token)) {
                $groups=getListOfGroups($connect, $name, $city, $building, $confirmed);
                $response['response']['group_list']=$groups;
            } else {
                $response['error']['text'] = "Invalid token";
                $response['error']['code'] = 0x09;
            }
            break;
        }
        case "joingroup":{
            $token=getRes('token');
            $groupId=getRes('groupid');
        }
        case null:
        case "":
        {
            $response['error']['text'] = "Specify an action";
            $response['error']['code'] = 0x02;
            break;
        }
        default:
        {
            $response['error']['text'] = "Unknown action";
            $response['error']['code'] = 0x03;
            break;
        }
    }
    $connect->close();
}
echo json_encode($response);

function getRes($name)
{
    global $debug;
    if ($debug) {
        return $_GET[$name];
    } else return $_POST[$name];
}
function generateToken($length = 64) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
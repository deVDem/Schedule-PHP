<?php

$debug = preg_match("/debug/", $_SERVER['SCRIPT_FILENAME']);
require "regexs.php";
require "mail/core.php";


$action = getRes('action');
$response = array(); // answer from server
$credinals = json_decode(file_get_contents("credinals.json"), true);
$connect = mysqli_connect(
    $credinals['mySQL']['host'],
    $credinals['mySQL']['account']['user'],
    $credinals['mySQL']['account']['password'],
    $debug ? $credinals['mySQL']['account']['database'] . "debug" : $credinals['mySQL']['account']['database'],
    $credinals['mySQL']['port']);
if (!$connect) {
    goError("Error on database server", 0x01);
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
                goError("Wrong username or email", 0x05);
            } else {
                if (password_verify($password, $user['password'])) {
                    $response['response']['user_info'] = $user;
                } else {
                    goError("Wrong password", 0x04);
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
                        goError("Wrong user data", 0x06);
                    }
                } else {
                    goError("This email is already registered", 0x07);
                }
            } else {
                goError("The user is already registered", 0x08);
            }
            break;
        }
        case "getGroups":
        {
            $token = getRes('token');
            $name = getRes('name');
            $city = getRes('city');
            $building = getRes('building');
            $confirmed = getRes('confirmed');
            if (checkToken($connect, $token)) {
                $groups = getListOfGroups($connect, $name, $city, $building, $confirmed);
                $response['response']['group_list'] = $groups;
            } else {
                goError("Invalid token", 0x09);
            }
            break;
        }
        case "joinGroup":
        {
            $token = getRes('token');
            $groupId = getRes('groupId');
            $user = getUser($token, $connect);
            if ($user == null) {
                goError("Invalid token", 0x0A);
            } else {
                if ($user['groupId'] == -1 || $groupId == -1) {
                    $groupList = getListOfGroups($connect, null, null, null, null);
                    if ($groupId != -1) {
                        $error = true;
                        foreach ($groupList as $group) {
                            if ($group['id'] == $groupId) $error = false;
                        }
                        if ($error) {
                            goError("This group is not exist", 0x0C);
                        } else {
                            if (changeGroup($connect, $user['id'], $groupId) == true)
                                $response['response']['success'] = changeGroup($connect, $user['id'], $groupId);
                            else {
                                goError("Error on database server", 0x0D);
                            }
                        }
                    } else {
                        if ($user['groupId'] == -1 && $groupId == -1) {
                            goError("You have already left the group", 0x0E);
                        } else {
                            if (changeGroup($connect, $user['id'], $groupId) == true)
                                $response['response']['success'] = changeGroup($connect, $user['id'], $groupId);
                            else {
                                goError("Error on database server", 0x0D);
                            }
                        }
                    }
                } else {
                    goError("You already in group", 0x0B);
                }
            }
            break;
        }
        case null:
        case "":
        {
            goError("Specify an action", 0x02);
            break;
        }
        default:
        {
            goError("Unknown action", 0x03);
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

function generateToken($length = 64)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function goError($message, $code) {
    global $response;
    $response['error']['text'] = $message;
    $response['error']['code'] = $code;
}
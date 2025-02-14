<?php

$debug = $_GET['h'];
require "regexs.php";
require "mail/core.php";


$action = getRes('action');
$response = array(); // answer from server
$credinals = json_decode(file_get_contents("D:\Sites\credinals.json"), true);
$connect = mysqli_connect(
    $credinals["mySQL"]["host"],
    $credinals["mySQL"]["account"][0]["user"],
    $credinals["mySQL"]["account"][0]["password"],
    $credinals["mySQL"]["account"][0]["database"],
    $credinals["mySQL"]["port"]);
if (!$connect) {
    goError("Error on database server", 0x01);
} else {
    $connect->query("SET NAMES 'utf8'");
    require "account.php";
    require "groups.php";
    require "images.php";
    
    switch ($action) {
        case "login":
        {
            $login = getRes('login');
            $password = getRes('password');
            $token = getRes('token');
            $user = getUser($login, $connect);
            if ($user == null) {
                goError("Wrong username or email", 0x05);
            } else {
                if (password_verify($password, $user['password']) || $user['token'] == $password) {
                    $user['password']=null;
                    $user['date_created']=null;
                    $response['response']['user_data'] = $user;
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
                        $user['password']=null;
                        $user['date_created']=null;
                        $response['response']['user_data'] = $user;
                        initMail();
                        setAddress($user['email']);
                        sendMail(0, array("https://api.devdem.ru/apps/schedule/" . getDebugStr() . "activation_mail?key=" . generateKeyForUser($connect, $user['id'], "mail_activation")));
                    } else {
                        goError("Wrong user data", 0x06);
                    }
                } else {
                    goError("This email is already registered", 0x07);
                }
            } else {
                goError("The user is already registered: ", 0x08);
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
            $id=getRes('groupId');
            $full = getRes('full');
            if (checkToken($connect, $token)) {
                $groups = getListOfGroups($connect, $name, $city, $building, $confirmed, $id);
                $response['response']['group_list'] = $groups;
                if($full && $id !=-1 && $id!=null) {
                    $response['response']['users']=getUsersListFromGroup($connect, $id);
                } else if($full) {
                    goError("Specify groupId", 0x14);
                }
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
                if ($groupId == "" || $groupId == null) {
                    goError("Type a group id", 0x0F);
                } else {
                    if ($user['groupId'] == -1 || $groupId == -1) {
                        $groupList = getListOfGroups($connect, null, null, null, null, null);
                        if ($groupId != -1) {
                            $error = true;
                            foreach ($groupList as $group) {
                                if ($group['id'] == $groupId) $error = false;
                            }
                            if ($error) {
                                goError("This group is not exist", 0x0C);
                            } else {
                                if (changeGroup($connect, $user['id'], $groupId) == true)
                                    $response['response']['success'] = true;
                                else {
                                    goError("Error on database server", 0x0D);
                                }
                            }
                        } else {
                            if ($user['groupId'] == -1 && $groupId == -1) {
                                goError("You have already left the group", 0x0E);
                            } else {
                                if (changeGroup($connect, $user['id'], $groupId) == true)
                                    $response['response']['success'] = true;
                                else {
                                    goError("Error on database server", 0x0D);
                                }
                            }
                        }
                    } else {
                        goError("You already in group", 0x0B);
                    }
                }
            }
            break;
        }
        case "getLessons":
        {
            $groupId = getRes('groupId');
            $token = getRes('token');
            $lessons = getLessons($connect, $groupId);
            $user = getUser($token, $connect);
            if ($user == null) {
                goError("Invalid token", 0x11);
                break;
            }
            if ($groupId == "" || $groupId == null) {
                goError("Type group id", 0x13);
                break;
            }
            if ($groupId != $user['groupId']) { // TODO: система полномочий для модерации других групп
                goError("No permissions for this action", 0x12);
                break;
            }
            if ($lessons == null) {
                goError("No lessons available for this group", 0x10);
                break;
            } else {
                $response['response']['lessons'] = $lessons;
            }
            break;
        }
        case "getNotifications":{
            $groupId=getRes('groupId');
            $token=getRes('token');
            $user=getUser($token, $connect);
            if($user==null) {
                goError("Invalid token", 0x14);
                break;
            }
            if($groupId=="" || $groupId==null) {
                goError("Type group id", 0x15);
                break;
            }
            if($groupId != $user['groupId']) { // TODO: полномочия
                goError("No permissions for this action", 0x16);
                break;
            }
            $response['response']['notifications']=getNotifications($connect, $groupId);
            break;
        }
        case "restorePass": {
            $email=getRes('email');
            $user=getUser($email, $connect);
            if($user==null) {
                goError("No user found", 0x53);
                break;
            }
            initMail();
            setAddress($user['email']);
            sendMail(1, array("https://api.devdem.ru/apps/schedule/restore?key=" . generateKeyForUser($connect, $user['id'], "restore_keys")));
            $response['response']['success'] = true;
            break;
        }
        case "getImage": {
            $imageId=getRes('imageId');
            if($imageId==null) {
                goError('imageId not found', 0x49);
                break;
            }
            $token=getRes('token');
            $user=getUser($token, $connect);
            if($user==null) {
                goError("User not found", 0x57);
                break;
            }
            $image=getImageFromId($connect, $imageId);
            if($image==null) {
                goError("Image not found", 0x47);
                break;
            }
            if($image['access']=='All') {
                $response['response']['image']=base64_encode(file_get_contents($image['path']));
            } else {
                $owner=getUserById($image['ownerId'], $connect);
                if($user==null) {
                    $response['response']['image']=base64_encode(file_get_contents($image['path']));
                } else if ($user['groupId']==$owner['groupId']) {
                    $response['response']['image']=base64_encode(file_get_contents($image['path']));
                } else goError("Access denied", 0x48);
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
    global $connect;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $newToken = '';
    for ($i = 0; $i < $length; $i++) {
        $newToken .= $characters[rand(0, $charactersLength - 1)];
    }
    $tokens = getAllTokens($connect);
    foreach ($tokens as $token) {
        if ($newToken == $token) return generateToken();
    }
    return $newToken;
}

function goError($message, $code)
{
    global $response;
    $response['error']['text'] = $message;
    $response['error']['code'] = $code;
}

function getDebug()
{
    global $debug;
    return $debug;
}

function getDebugStr()
{
    global $debug;
    return $debug ? "debug/" : "";
}
<?php
$debug=true;

$action = getRes('action');
$response = array();
$credinals = json_decode(file_get_contents("credinals.json"), true);
$connect = mysqli_connect("localhost", $credinals['mySQL']['account'][0]['user'], $credinals['mySQL']['account'][0]['password'], $credinals['mySQL']['account'][0]['database']);
if (!$connect) {
    $response['error']['text'] = "Error on database server";
    $response['error']['code'] = 0x01;
} else {
    $connect->query("SET NAMES 'utf8'");
    require "account.php";

    switch ($action) {
        case "login":
            {
                $login = getRes('login');
                $password = getRes('password');
                $user = checkProfile($login, $connect);
                $response['response']['user_info']=$user;
            }
            break;
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

function getRes($name) {
    global $debug;
    if ($debug) {
        return $_GET[$name];
    } else return $_POST[$name];
}
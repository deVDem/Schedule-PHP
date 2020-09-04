<?php
$key = $_GET['key'];
if ($key != null && $key != "") {
    $credinals = json_decode(file_get_contents("D:\Sites\credinals.json"), true);
    $connect = mysqli_connect(
        $credinals['mySQL']['host'],
        $credinals['mySQL']['account']['user'],
        $credinals['mySQL']['account']['password'],
        $debug ? $credinals['mySQL']['account']['database'] . "debug" : $credinals['mySQL']['account']['database'],
        $credinals['mySQL']['port']);
    if (!$connect) {
        goError("Error on database server", 0x01);
    } else {
        $query = $connect->query("SELECT `keyMail`, `userId`, `expiry` FROM `mail_activation` WHERE `keyMail`=\"$key\"");
        $keyInfo = $query->fetch_assoc();
        if($keyInfo==null) {
            goError("Key not found", 0x03);
        } else {
            $user=getUser($keyInfo['userId'], $connect);
            if($user==null) {
                goError("User not found", 0x04);
            } else {
                $connect->query("UPDATE `users` SET `confirmed`='Yes' WHERE `id`=".$keyInfo['userId']);
                $connect->query("DELETE FROM `mail_activation` WHERE `keyMail` =".'"'.$keyInfo['keyMail'].'"');
                goError("Success. Thank you for confirming your email", 0x05);
            }
        }
    }
} else {
    goError("Specify a key", 0x02);
}

$connect->close();


function goError($message, $code)
{
    echo $message;
    echo "<br/>";
    echo "Code: ".$code;
}

function getUser($id, $connect)
{
    $user = null;
    $query = $connect->query("SELECT * FROM `users` WHERE `id`=\"$id\"");
    while ($row = $query->fetch_assoc()) {
        $user = $row;
    }
    return $user;
}
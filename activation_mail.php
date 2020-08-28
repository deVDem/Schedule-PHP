<?php
echo $_GET['key'];

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

}

$connect->close();
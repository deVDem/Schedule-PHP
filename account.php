<?php
/** @noinspection PhpUndefinedMethodInspection */
function getUser($data, $connect)
{
    $user = null;
    $query = $connect->query("SELECT * FROM `users` WHERE `login`=\"$data\" OR `email`=\"$data\" OR `token`=\"$data\"");
    while ($row = $query->fetch_assoc()) {
        $user = $row;
    }
    return $user;
}


function checkToken($connect, $token) {
    $return=false;
    $query = $connect->query("SELECT * FROM `users` WHERE `token`=\"$token\"");
    while ($row = $query->fetch_assoc()) {
        $return= true;
    }
    return $return;
}

function registerUser($connect, $login, $email, $passwordHash, $names, $spam, $token) {
    return $connect->query("INSERT INTO `users`(`login`, `email`, `password`, `names`, `spam`, `token`) VALUES (\"$login\", \"$email\", \"$passwordHash\", \"$names\", \"$spam\", \"$token\")");
}

function changeGroup($connect, $id, $groupId) {
    return $connect->query("UPDATE `users` SET `groupId`=\"$groupId\" WHERE `id`=$id");
}

function getAllTokens($connect) {
    $tokens = null;
    $i=0;
    $query = $connect->query("SELECT `token` FROM `users`");
    while ($row = $query->fetch_assoc()) {
        $tokens[$i] = $row['token'];
        $i++;
    }
    return $tokens;
}
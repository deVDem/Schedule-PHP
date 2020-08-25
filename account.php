<?php
/** @noinspection PhpUndefinedMethodInspection */
function getUser($login, $connect)
{
    $user = null;
    $query = $connect->query("SELECT * FROM `users` WHERE `login`=\"$login\" OR `email`=\"$login\"");
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
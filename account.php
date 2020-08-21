<?php
function checkProfile($login, $connect)
{
    $user = array();
    $query = $connect->query("SELECT * FROM `users` WHERE `login`=\"$login\"");
    while ($row = $query->fetch_assoc()) {
        $user = $row;
    }
    return $user;
}
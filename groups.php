<?php
function getListOfGroups($connect, $name, $city, $building, $confirmed, $id)
{
    $groups = array();
    $i=0;
    $query = $connect->query("SELECT * FROM `groups` WHERE `name` LIKE \"%$name%\" AND `city` LIKE \"%$city%\" AND `building` LIKE \"%$building%\" AND `confirmed` LIKE \"%$confirmed%\" AND `id` LIKE  \"%$id%\"");
    while ($row = $query->fetch_assoc()) {
        $groups[$i] = $row;
        $i++;
    }
    return $groups;
}

function getUsersListFromGroup($connect, $groupId) {
    $users = array();
    $i=0;
    $query=$connect->query("SELECT `id`, `login`, `names`, `pro` FROM `users` WHERE `groupId`=$groupId");
    while ($user = $query->fetch_assoc()) {
        $users[$i]=$user;
        $i++;
    }
    return $users;
}

function getLessons($connect, $groupId) {
    $lessons = null;
    $i=0;
    $query = $connect->query("SELECT * FROM `lessons` WHERE `groupOwner`=\"$groupId\"");
    while ($row = $query->fetch_assoc()) {
        $lessons[$i]=$row;
        $i++;
    }
    return $lessons;
}

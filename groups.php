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
    $query = $connect->query("SELECT * FROM `lessons` WHERE `groupOwner`=\"$groupId\" ORDER BY `lessons`.`day` ASC, `lessons`.`n` ASC");
    while ($row = $query->fetch_assoc()) {
        $lessons[$i]=$row;
        $i++;
    }
    return $lessons;
}


function getNotifications($connect, $groupId) {
    $notifications = array();
    $i=0;
    $query = $connect->query("SELECT * from `notifications` where `groupId`=$groupId  ORDER BY `notifications`.`date_created` DESC");
    while ($notification = $query->fetch_assoc()) {
        if($notification['ownerId'] != -1) 
        {
            $notification['author']=getUserById($notification['ownerId'], $connect);
            $notification['author']['id']=null;
            $notification['author']['email']=null;
            $notification['author']['token']=null;
            $notification['author']['password']=null;
            $notification['author']['spam']=null;
            $notification['author']['date_created']=null;
            $notification['author']['confirmed']=null;
        }
        $notification['ownerId']=null;
        $notifications[$i]=$notification;
        $i++;
    }
    return $notifications;
}

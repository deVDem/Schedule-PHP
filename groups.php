<?php
function getListOfGroups($connect, $name, $city, $building, $confirmed)
{
    $groups = array();
    $i=0;
    $query = $connect->query("SELECT * FROM `groups` WHERE `name` LIKE \"%$name%\" AND `city` LIKE \"%$city%\" AND `building` LIKE \"%$building%\" AND `confirmed` LIKE \"%$confirmed%\"");
    while ($row = $query->fetch_assoc()) {
        $groups[$i] = $row;
        $groups[$i]['n'] = $i;
        $i++;
    }
    return $groups;
}

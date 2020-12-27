<?php


function getImageFromId($connect, $imageId) {
    $image = null;
    $query = $connect->query("SELECT * FROM `images` WHERE `id`=\"$imageId\"");
    while ($row = $query->fetch_assoc()) {
        $image = $row;
    }
    return $image;
}


?>
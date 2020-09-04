<?php

function getMessage($code)
{
    $errors = array(
        0x01 => "Error on database server",
        0x02 => "Specify an action",
        0x03 => "Unknown action",
        0x04 => "Wrong password",
        0x05 => "Wrong username or email",
        0x06 => "Wrong user data",
        0x07 => "This email is already registered",
        0x08 => "The user is already registered",
        0x09 => "Invalid token",
        0x0A => "Invalid token",
        0x0B => "You already in group",
        0x0C => "This group is not exist",
        0x0D => "Error on database server",
        0x0E => "You have already left the group",
        0x0F => "Type a group id",
        0x10 => "No lessons available for this group",
        0x11 => "Invalid token",
        0x12 => "No permissions for this action",
        0x13 => "Type group id",
        0x14 => "Specify groupId"
    );
    return $errors[$code];
}
<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
$mail = new PHPMailer();
function initMail()
{
    try {
        $credinals = json_decode(file_get_contents("D:\Sites\credinals.json"), true);
        global $mail;
        $mail->CharSet='UTF-8';
        $mail->isSMTP();
        $mail->Host = $credinals['no-replyMail']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $credinals['no-replyMail']['username'];
        $mail->Password = $credinals['no-replyMail']['password'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom($credinals['no-replyMail']['username'], $credinals['no-replyMail']['name']);
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        return $e;
    }
}

function setAddress($address)
{
    try {
        global $mail;
        $mail->addAddress($address);
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        return $e;
    }
}

function sendMail($id, $args)
{
    try {
        global $mail;
        if (file_exists(dirname(__FILE__) . "/templates/$id/subject.txt") &&
            file_exists(dirname(__FILE__) . "/templates/$id/template.php")) {
            $subject = file_get_contents("https://api.devdem.ru/apps/schedule/debug/mail/templates/$id/subject.txt", FILE_USE_INCLUDE_PATH);
            $msgHTML = file_get_contents("https://api.devdem.ru/apps/schedule/debug/mail/templates/$id/template.php?s=".json_encode($args));
            $mail->Subject = $subject;
            $mail->msgHTML($msgHTML);
            return $mail->send();
        } else {
            return new Exception("Server error: No template id $id found");
        }
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        return $e;
    }
}

// debug funcs
function getsubject($id)
{
    if (file_exists(dirname(__FILE__) . "/templates/$id/subject.txt")) {
        $subject = file_get_contents("templates/$id/subject.txt", FILE_USE_INCLUDE_PATH);
        echo $subject;
    } else {
        echo new Exception("Server error: No subject template found");
    }
}

function getMSGHTML($id, $args)
{
    if (file_exists(dirname(__FILE__) . "/templates/$id/template.php")) {
        $debugstr = getDebug() ? "/debug" : "";
        $msgHTML = file_get_contents("https://api.devdem.ru/apps/schedule".$debugstr."/mail/templates/$id/template.php?s=".json_encode($args));
        echo $msgHTML;
    } else {
        echo new Exception("Server error: No template found");
    }
}


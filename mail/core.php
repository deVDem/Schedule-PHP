<?php
use PHPMailer\PHPMailer\PHPMailer;
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
$credinals = json_decode(file_get_contents("./credinals.json"), true);
$mail = new PHPMailer();
$mail->isSMTP();                   // Отправка через SMTP
$mail->Host   = $credinals['no-replyMail']['host'];  // Адрес SMTP сервера
$mail->SMTPAuth   = true;          // Enable SMTP authentication
$mail->Username   = $credinals['no-replyMail']['username'];       // ваше имя пользователя (без домена и @)
$mail->Password   = $credinals['no-replyMail']['password'];    // ваш пароль
$mail->SMTPSecure = 'ssl';         // шифрование ssl
$mail->Port   = 465;               // порт подключения

$mail->setFrom($credinals['no-replyMail']['username'], $credinals['no-replyMail']['name']);    // от кого
$mail->addAddress('admin@devdem.ru'); // кому

$mail->Subject = 'Тест';
$mail->msgHTML("<html><body>
                <h1>Здравствуйте!</h1>
                <p>Это тестовое письмо.</p>
                </html></body>");
// Отправляем
if ($mail->send()) {
    echo 'Письмо отправлено!';
} else {
    echo 'Ошибка: ' . $mail->ErrorInfo;
}
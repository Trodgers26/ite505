<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\Exception.php';
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\PHPMailer.php';
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\SMTP.php';

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'rodgers.timothyj@gmail.com';
        $mail->Password = 'flqh fmxy cdac qjsy';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('your-email@gmail.com', 'Soup Boi Athletics');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true; // Return true on success
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false; // Return false on failure
    }
}
?>


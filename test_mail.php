<?php
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\Exception.php';
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\PHPMailer.php';
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'rodgers.timothyj@gmail.com';
    $mail->Password = '148Pigs17!!';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('your-email@gmail.com', 'ITE505 Team');
    $mail->addAddress('timmrodgers@hotmail.com'); // Add a recipient

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email sent from PHPMailer.';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>


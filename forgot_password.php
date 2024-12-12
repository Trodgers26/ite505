<?php
include 'config.php';
include 'header.php';
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\Exception.php';
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\PHPMailer.php';
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $sql = "SELECT * FROM Users WHERE Email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50)); // Generate a unique token
        $sql = "UPDATE Users SET reset_token='$token', reset_token_expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE Email='$email'";
        if ($conn->query($sql) === TRUE) {
            // Send reset email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'rodgers.timothyj@gmail.com'; // Your Gmail address
                $mail->Password = 'flqh fmxy cdac qjsy'; // Your App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('no-reply@ite505.com', 'Soup Boi Athletics');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "Hello,<br><br>Please click the link below to reset your password:<br><br>";
                $mail->Body   .= "<a href='http://localhost/ite505/reset_password.php?token=$token'>Reset Password</a><br><br>";
                $mail->Body   .= "This link will expire in 1 hour.<br><br>stay soupy,<br>Soup Boi Army";

                $mail->send();
                echo "<div class='success-message'>A password reset link has been sent to your email address.</div>";
            } catch (Exception $e) {
                echo "<div class='error-message'>Failed to send the password reset email. Mailer Error: {$mail->ErrorInfo}</div>";
            }
        } else {
            echo "<div class='error-message'>Error: " . $sql . "<br>" . $conn->error . "</div>";
        }
    } else {
        echo "<div class='error-message'>No user found with that email address.</div>";
    }
}
?>

<main>
    <form method="post" action="forgot_password.php">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        <input type="submit" value="Send Reset Link">
    </form>
</main>

<?php include 'footer.php'; ?>



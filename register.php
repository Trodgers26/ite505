<?php
include 'config.php';
include 'header.php';
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\Exception.php';
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\PHPMailer.php';
require 'C:\xampp\htdocs\ite505\libs\PHPMailer-master\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];

    // Server-side validation for password
    if ($password !== $confirm_password) {
        echo "<div class='error-message'>Passwords do not match.</div>";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        echo "<div class='error-message'>Password must be at least 8 characters long and include at least one number and one symbol.</div>";
    } else {
        // Check if username or email already exists
        $sql = "SELECT * FROM Users WHERE Username=? OR Email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<div class='error-message'>Username or email already exists. Please choose a different one.</div>";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(50)); // Generate a unique token

            $sql = "INSERT INTO Users (Username, PasswordHash, Email, validation_token) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $passwordHash, $email, $token);
            if ($stmt->execute() === TRUE) {
                $userID = $stmt->insert_id; // Get the newly created user ID

                // Insert user profile
                $sql = "INSERT INTO userprofiles (UserID, FirstName, LastName, PhoneNumber, DateOfBirth) VALUES (?, '', '', '', '')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userID);
                if ($stmt->execute() !== TRUE) {
                    echo "<div class='error-message'>Error creating user profile: " . $stmt->error . "</div>";
                }

                // Send validation email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'rodgers.timothyj@gmail.com';
                    $mail->Password = 'flqh fmxy cdac qjsy';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('your-email@gmail.com', 'Soup Boi Athletics Welcome Squad');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Hey, $username! Welcome to Soup Boi";
                    $mail->Body    = "Hello $username,<br><br>You've made the right choice. Smash that link below to verify your account status
                                            and join the Soup Boi army.<br><br>";
                    $mail->Body   .= "<a href='http://localhost/ite505/validate_email.php?token=$token'>Validate Email</a><br><br>";
                    $mail->Body   .= "stay cool,<br>soupboi inc.";

                    $mail->send();
                    echo "<div class='success-message'>Registration successful! A validation email has been sent to $email.</div>";
                } catch (Exception $e) {
                    echo "<div class='error-message'>Registration successful, but the validation email could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
                }

                // Redirect to homepage
                header("Location: index.php");
                exit();
            } else {
                echo "<div class='error-message'>Error: " . $sql . "<br>" . $conn->error . "</div>";
            }
        }
    }
}
?>

<main>
    <form method="post" action="register.php" onsubmit="return validatePassword()">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        <input type="submit" value="Register">
        <p><center>Already have an account? <a href="login.php">Login here</a>.</center></p>
    </form>
</main>

<script>
function validatePassword() {
    var password = document.getElementById("password").value;
    var confirm_password = document.getElementById("confirm_password").value;
    var passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (password !== confirm_password) {
        alert("Passwords do not match.");
        return false;
    } else if (!passwordPattern.test(password)) {
        alert("Password must be at least 8 characters long and include at least one number and one symbol.");
        return false;
    }
    return true;
}
</script>

<?php include 'footer.php'; ?>

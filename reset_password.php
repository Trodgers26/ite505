<?php
include 'config.php';
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Server-side validation for password
    if ($password !== $confirm_password) {
        echo "<div class='error-message'>Passwords do not match.</div>";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        echo "<div class='error-message'>Password must be at least 8 characters long and include at least one number and one symbol.</div>";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Check if the token is valid and not expired
        $sql = "SELECT * FROM Users WHERE reset_token='$token' AND reset_token_expiry > NOW()";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $sql = "UPDATE Users SET PasswordHash='$passwordHash', reset_token=NULL, reset_token_expiry=NULL WHERE reset_token='$token'";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='success-message'>Your password has been reset successfully.</div>";
            } else {
                echo "<div class='error-message'>Error: " . $sql . "<br>" . $conn->error . "</div>";
            }
        } else {
            echo "<div class='error-message'>Invalid or expired token.</div>";
        }
    }
}
?>

<main>
    <form method="post" action="reset_password.php">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
        <label for="password">New Password:</label>
        <input type="password" id="password" name="password" required><br>
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br>
        <input type="submit" value="Reset Password">
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

<?php
include 'config.php';
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = $_POST['username_or_email'];
    $password = $_POST['password'];

    // Check if the input is an email or username
    if (filter_var($username_or_email, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM Users WHERE Email='$username_or_email'";
    } else {
        $sql = "SELECT * FROM Users WHERE Username='$username_or_email'";
    }

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['PasswordHash'])) {
            $_SESSION['UserID'] = $row['UserID'];
            $_SESSION['Username'] = $row['Username'];
            $_SESSION['UserEmail'] = $row['Email']; // Store the user's email in the session
            header("Location: index.php");
            exit();
        } else {
            echo "<div class='error-message'>Invalid password.</div>";
        }
    } else {
        echo "<div class='error-message'>No user found with that username or email.</div>";
    }
}
?>

<main>
    <form method="post" action="login.php">
        <label for="username_or_email">Username or Email:</label>
        <input type="text" id="username_or_email" name="username_or_email" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <input type="submit" value="Login">
        <p><center><a href="forgot_password.php">Forgot Password?</a></center></p>
        <p><center><a href="register.php">Need to Sign Up?</a></center></p>
    </form>
</main>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<?php include 'footer.php'; ?>


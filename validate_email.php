<?php
include 'config.php';
include 'header.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token is valid
    $sql = "SELECT * FROM Users WHERE validation_token='$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $sql = "UPDATE Users SET validation_token=NULL, is_validated=1 WHERE validation_token='$token'";
        if ($conn->query($sql) === TRUE) {
            echo "<div class='success-message'>Your email has been validated successfully.</div>";
        } else {
            echo "<div class='error-message'>Error: " . $sql . "<br>" . $conn->error . "</div>";
        }
    } else {
        echo "<div class='error-message'>Invalid or expired token.</div>";
    }
} else {
    echo "<div class='error-message'>No token provided.</div>";
}

include 'footer.php';
?>



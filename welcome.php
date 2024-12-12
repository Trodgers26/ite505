<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>
    <h1>wassup, <?php echo $_SESSION['Username']; ?>?</h1>
    <a href="logout.php">Logout</a>
</body>
</html>

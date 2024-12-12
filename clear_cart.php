<?php
session_start();
include 'config.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['UserID'];

// Clear the cart for the current user
$sql = "DELETE FROM Carts WHERE UserID='$userID'";
if ($conn->query($sql) === TRUE) {
    header("Location: cart.php");
    exit();
} else {
    die("Error clearing cart: " . $conn->error);
}
?>


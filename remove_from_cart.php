<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['UserID'])) {
        echo "error";
        exit();
    }

    $cartID = $_POST['cartID'];

    // Delete the item from the cart
    $sql = "DELETE FROM Carts WHERE CartID='$cartID' AND UserID='" . $_SESSION['UserID'] . "'";
    if ($conn->query($sql)) {
        echo "success";
    } else {
        echo "error";
    }

    exit();
} else {
    echo "error";
    exit();
}
?>


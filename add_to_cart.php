<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['UserID'])) {
        header('Location: login.php');
        exit();
    }

    $userID = $_SESSION['UserID'];
    $productID = $_POST['productID'];
    $quantity = $_POST['quantity'];
    $size = $_POST['size'];

    // Debug output
    echo "UserID: $userID<br>";
    echo "ProductID: $productID<br>";
    echo "Quantity: $quantity<br>";
    echo "Size: $size<br>";

    // Check if the product is already in the cart
    $sql = "SELECT * FROM Carts WHERE UserID='$userID' AND ProductID='$productID' AND Size='$size'";
    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        // Update the quantity if the product is already in the cart
        $row = $result->fetch_assoc();
        $newQuantity = $row['Quantity'] + $quantity;
        $updateSql = "UPDATE Carts SET Quantity='$newQuantity' WHERE UserID='$userID' AND ProductID='$productID' AND Size='$size'";
        if (!$conn->query($updateSql)) {
            die("Update query failed: " . $conn->error);
        }
    } else {
        // Insert a new row if the product is not in the cart
        $insertSql = "INSERT INTO Carts (UserID, ProductID, Quantity, Size) VALUES ('$userID', '$productID', '$quantity', '$size')";
        if (!$conn->query($insertSql)) {
            die("Insert query failed: " . $conn->error);
        }
    }

    header('Location: cart.php');
    exit();
} else {
    die("Invalid request method.");
}
?>


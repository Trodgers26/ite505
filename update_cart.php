<?php
include 'config.php';
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['UserID'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quantities = $_POST['quantity'];
    $sizes = $_POST['size'];

    foreach ($quantities as $cartID => $quantity) {
        $size = $sizes[$cartID];

        // Check if there is an existing item with the same product ID and size
        $sql = "SELECT * FROM Carts WHERE UserID=? AND ProductID=(SELECT ProductID FROM Carts WHERE CartID=?) AND Size=? AND CartID<>?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisi", $userID, $cartID, $size, $cartID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Merge the quantities
            $existingItem = $result->fetch_assoc();
            $newQuantity = $existingItem['Quantity'] + $quantity;

            // Update the existing item with the new quantity
            $updateSql = "UPDATE Carts SET Quantity=? WHERE CartID=?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ii", $newQuantity, $existingItem['CartID']);
            $updateStmt->execute();

            // Delete the current item
            $deleteSql = "DELETE FROM Carts WHERE CartID=?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $cartID);
            $deleteStmt->execute();
        } else {
            // Update the current item
            $updateSql = "UPDATE Carts SET Quantity=?, Size=? WHERE CartID=?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("isi", $quantity, $size, $cartID);
            $updateStmt->execute();
        }
    }

    header("Location: cart.php");
    exit();
}
?>


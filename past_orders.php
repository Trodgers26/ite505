<?php
include 'config.php';
include 'header.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['UserID'];

// Retrieve order history
$orderSql = "SELECT * FROM orders WHERE UserID=? ORDER BY OrderID DESC";
$stmt = $conn->prepare($orderSql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$orderResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Orders</title>
    <link rel="stylesheet" type="text/css" href="css/product-styles.css">
</head>
<body>
    <div class="container">
        <h2>Past Orders</h2>
        <div class="order-history">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Purchased At</th>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($orderResult->num_rows > 0) {
                        while ($order = $orderResult->fetch_assoc()) {
                            $orderID = $order['OrderID'];
                            $purchasedAt = date('F j, Y', strtotime($order['OrderDate']));
                            $totalAmount = $order['TotalAmount'];

                            // Retrieve the order items
                            $orderItemsSql = "SELECT oi.*, p.ProductName, p.ImagePath FROM orderitems oi JOIN Products p ON oi.ProductID = p.ProductID WHERE oi.OrderID=? ORDER BY oi.OrderItemID ASC";
                            $stmt = $conn->prepare($orderItemsSql);
                            $stmt->bind_param("i", $orderID);
                            $stmt->execute();
                            $orderItemsResult = $stmt->get_result();

                            if ($orderItemsResult->num_rows > 0) {
                                $firstRow = true;
                                while ($item = $orderItemsResult->fetch_assoc()) {
                                    $totalPrice = $item['Quantity'] * $item['Price'];
                                    echo "<tr>";
                                    if ($firstRow) {
                                        echo "<td rowspan='" . $orderItemsResult->num_rows . "'>" . $orderID . "</td>";
                                        echo "<td rowspan='" . $orderItemsResult->num_rows . "'>" . $purchasedAt . "</td>";
                                        $firstRow = false;
                                    }
                                    echo "<td><img src='" . $item['ImagePath'] . "' alt='" . $item['ProductName'] . "' class='product-image'></td>";
                                    echo "<td>" . $item['ProductName'] . "</td>";
                                    echo "<td>" . $item['Quantity'] . "</td>";
                                    echo "<td>$" . $item['Price'] . "</td>";
                                    echo "<td>$" . $totalPrice . "</td>";
                                    echo "</tr>";
                                }
                                echo "<tr><td colspan='7' style='text-align: right;'><strong>Total Amount: $" . $totalAmount . "</strong></td></tr>";
                            }
                        }
                    } else {
                        echo "<tr><td colspan='7'>No order history available.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>



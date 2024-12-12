<?php
include 'config.php';
include 'header.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['UserID'];

// Retrieve the payment intent ID from the session
$paymentIntentId = $_SESSION['payment_intent'] ?? null;

if ($paymentIntentId) {
    // Retrieve the payment intent from Stripe
    require 'stripe_config.php';
    try {
        $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
        if ($paymentIntent->status == 'succeeded') {
            //echo "<div class='success-message'>Thank you for your purchase! Your payment was successful.</div>";

            // Retrieve the order details from the database
            $orderSql = "SELECT * FROM orders WHERE UserID=? AND payment_intent_id=?";
            $stmt = $conn->prepare($orderSql);
            $stmt->bind_param("is", $userID, $paymentIntentId);
            $stmt->execute();
            $orderResult = $stmt->get_result();
            if ($orderResult->num_rows > 0) {
                $order = $orderResult->fetch_assoc();
                $orderID = $order['OrderID'];
                $orderDate = date('F j, Y', strtotime($order['OrderDate']));
                $totalAmount = $order['TotalAmount'];

                // Retrieve the shipping address
                $addressSql = "SELECT * FROM addresses WHERE AddressID=?";
                $stmt = $conn->prepare($addressSql);
                $stmt->bind_param("i", $order['ShippingAddressID']);
                $stmt->execute();
                $addressResult = $stmt->get_result();
                $address = $addressResult->fetch_assoc();

                // Retrieve the user's name from the userprofiles table
                $userProfileSql = "SELECT FirstName, LastName FROM userprofiles WHERE UserID=?";
                $stmt = $conn->prepare($userProfileSql);
                $stmt->bind_param("i", $userID);
                $stmt->execute();
                $userProfileResult = $stmt->get_result();
                $userProfile = $userProfileResult->fetch_assoc();
                $firstName = $userProfile['FirstName'] ?? '';
                $lastName = $userProfile['LastName'] ?? '';

                echo "<div class='container'>";
                echo "<h2>Payment Status</h2>";
                echo "<p>Thank you for your purchase! You will receive an email confirmation shortly.</p>";
                echo "<h3>Order Summary</h3>";
                echo "<p>Order ID: " . $orderID . "</p>";
                echo "<p>Order Date: " . $orderDate . "</p>";
                echo "<p>Total Amount: $" . $totalAmount . "</p>";

                echo "<h3>Shipping Information</h3>";
                echo "<p>Name: " . $firstName . " " . $lastName . "</p>";
                echo "<p>Address: " . $address['AddressLine1'] . " " . $address['AddressLine2'] . "</p>";
                echo "<p>City: " . $address['City'] . "</p>";
                echo "<p>State: " . $address['State'] . "</p>";
                echo "<p>ZIP Code: " . $address['Zip'] . "</p>";
                echo "<p>Country: " . $address['Country'] . "</p>";

                // Retrieve the order items
                $orderItemsSql = "SELECT oi.*, p.ProductName, p.ImagePath FROM orderitems oi JOIN Products p ON oi.ProductID = p.ProductID WHERE oi.OrderID=?";
                $stmt = $conn->prepare($orderItemsSql);
                $stmt->bind_param("i", $orderID);
                $stmt->execute();
                $orderItemsResult = $stmt->get_result();

                echo "<table class='table'>";
                echo "<thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Size</th>
                            <th>Total</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                while ($item = $orderItemsResult->fetch_assoc()) {
                    $totalPrice = $item['Quantity'] * $item['Price'];
                    echo "<tr>
                            <td><img src='" . $item['ImagePath'] . "' alt='" . $item['ProductName'] . "' class='product-image' width='50'></td>
                            <td>" . $item['ProductName'] . "</td>
                            <td>" . $item['Quantity'] . "</td>
                            <td>$" . $item['Price'] . "</td>
                            <td>" . $item['Sizes'] . "</td>
                            <td>$" . $totalPrice . "</td>
                          </tr>";
                }
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            } else {
                echo "<div class='error-message'>Order details not found.</div>";
            }
        } else {
            echo "<div class='error-message'>Payment failed or is still processing. Please check your payment details and try again.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error-message'>Error retrieving payment details: " . $e->getMessage() . "</div>";
    }
    // Clear the payment intent from the session
    unset($_SESSION['payment_intent']);
} else {
    echo "<div class='error-message'>No payment intent ID provided.</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
    <link rel="stylesheet" type="text/css" href="css/product-styles.css">
</head>
<body>
    <div class="container">
        <button type="button" class="button" onclick="window.location.href='index.php'">Return to Home</button>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>
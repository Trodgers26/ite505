<?php
include 'config.php';
// include 'header.php'; // Remove this if it includes HTML content
include 'stripe_config.php'; // Include Stripe configuration
require 'sendEmail.php';

header('Content-Type: application/json');

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function returnJsonError($message) {
    echo json_encode(['error' => $message]);
    exit();
}

function logError($message) {
    error_log($message, 3, __DIR__ . '/error.log'); // Log to a file in the current directory
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($_SESSION['UserID'])) {
        returnJsonError('User not logged in');
    }

    $userID = $_SESSION['UserID'];
    $firstName = $data['first_name'] ?? null;
    $lastName = $data['last_name'] ?? null;
    $addressLine1 = $data['address_line1'] ?? null;
    $addressLine2 = $data['address_line2'] ?? null;
    $city = $data['city'] ?? null;
    $state = $data['state'] ?? null;
    $zip = $data['zip'] ?? null;
    $country = $data['country'] ?? null;
    $paymentIntentId = $data['payment_intent_id'] ?? null;
    $currency = 'USD'; // Assuming the currency is USD

    if (!$paymentIntentId) {
        returnJsonError('Payment Intent ID is missing');
    }

    // Insert shipping address into the addresses table
    $insertAddressSql = "INSERT INTO addresses (UserID, AddressLine1, AddressLine2, City, State, Zip, Country) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertAddressSql);
    if (!$stmt) {
        logError("Error preparing address statement: " . $conn->error);
        returnJsonError("Error preparing address statement: " . $conn->error);
    }
    $stmt->bind_param("issssss", $userID, $addressLine1, $addressLine2, $city, $state, $zip, $country);
    if ($stmt->execute() !== TRUE) {
        logError("Error inserting address: " . $stmt->error);
        returnJsonError("Error inserting address: " . $stmt->error);
    }
    $shippingAddressID = $stmt->insert_id;

    if (!$shippingAddressID) {
        logError("Shipping Address ID not generated.");
        returnJsonError("Shipping Address ID not generated.");
    }

    $sql = "SELECT * FROM Carts WHERE UserID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        returnJsonError("Cart query failed: " . $conn->error);
    }

    $orderDate = date('Y-m-d');
    $insertOrderSql = "INSERT INTO orders (UserID, OrderDate, TotalAmount, ShippingAddressID, payment_intent_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertOrderSql);
    if (!$stmt) {
        logError("Error preparing order statement: " . $conn->error);
        returnJsonError("Error preparing order statement: " . $conn->error);
    }
    $grandTotal = 0;
    $stmt->bind_param("isdss", $userID, $orderDate, $grandTotal, $shippingAddressID, $paymentIntentId);
    if ($stmt->execute() !== TRUE) {
        logError("Error inserting order: " . $stmt->error);
        returnJsonError("Error inserting order: " . $stmt->error);
    }
    $orderID = $stmt->insert_id;

    if (!$orderID) {
        logError("Order ID not generated.");
        returnJsonError("Order ID not generated.");
    }

    $orderDetails = "<table style='width: 100%; border-collapse: collapse;'>";
    $orderDetails .= "<tr style='background-color: #f2f2f2;'>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Image</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Product Name</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Quantity</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Price</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Size</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Total</th>
                      </tr>";

    while ($row = $result->fetch_assoc()) {
        $productID = $row['ProductID'];
        $quantity = $row['Quantity'];
        $size = $row['Size'];

        $productSql = "SELECT Stock, ProductName, Price, ImagePath FROM Products WHERE ProductID=?";
        $productStmt = $conn->prepare($productSql);
        $productStmt->bind_param("i", $productID);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        if (!$productResult) {
            returnJsonError("Product query failed: " . $conn->error);
        }
        $product = $productResult->fetch_assoc();
        if ($quantity > $product['Stock']) {
            returnJsonError("The quantity for product ID $productID exceeds the available stock.");
        } else {
            $newStock = $product['Stock'] - $quantity;
            $updateStockSql = "UPDATE Products SET Stock=? WHERE ProductID=?";
            $updateStockStmt = $conn->prepare($updateStockSql);
            $updateStockStmt->bind_param("ii", $newStock, $productID);
            if (!$updateStockStmt->execute()) {
                returnJsonError("Error updating stock: " . $conn->error);
            }

            $totalPrice = $quantity * $product['Price'];
            $grandTotal += $totalPrice;
            $imageUrl = $product['ImagePath']; // Use public URL stored in the database
            $orderDetails .= "<tr>";
            $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px; text-align: center;'><img src='" . $imageUrl . "' alt='" . $product['ProductName'] . "' width='50'></td>";
            $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px;'>" . $product['ProductName'] . "</td>";
            $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>" . $quantity . "</td>";
            $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>$" . $product['Price'] . "</td>";
            $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>" . $size . "</td>";
            $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>$" . $totalPrice . "</td>";
            $orderDetails .= "</tr>";

            $insertOrderItemSql = "INSERT INTO orderitems (OrderID, ProductID, Quantity, Price, Sizes) VALUES (?, ?, ?, ?, ?)";
            $orderItemStmt = $conn->prepare($insertOrderItemSql);
            if (!$orderItemStmt) {
                returnJsonError("Error preparing statement: " . $conn->error);
            }
            $orderItemStmt->bind_param("iiids", $orderID, $productID, $quantity, $product['Price'], $size);
            if ($orderItemStmt->execute() !== TRUE) {
                returnJsonError("Error inserting order item: " . $stmt->error);
            }
        }
    }

    $orderDetails .= "<tr>
                        <td colspan='5' style='border: 1px solid #ddd; padding: 8px; text-align: right;'><strong>Grand Total:</strong></td>
                        <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'><strong>$" . $grandTotal . "</strong></td>
                      </tr>";
    $orderDetails .= "</table>";

    $updateOrderTotalSql = "UPDATE orders SET TotalAmount=? WHERE OrderID=?";
    $updateOrderTotalStmt = $conn->prepare($updateOrderTotalSql);
    if (!$updateOrderTotalStmt) {
        returnJsonError("Error preparing statement: " . $conn->error);
    }
    $updateOrderTotalStmt->bind_param("di", $grandTotal, $orderID);
    if ($updateOrderTotalStmt->execute() !== TRUE) {
        returnJsonError("Error updating order total: " . $stmt->error);
    }

    $clearCartSql = "DELETE FROM Carts WHERE UserID=?";
    $clearCartStmt = $conn->prepare($clearCartSql);
    $clearCartStmt->bind_param("i", $userID);
    if (!$clearCartStmt->execute()) {
        returnJsonError("Error clearing cart: " . $conn->error);
    }

    // Insert payment information into the payments table
    $insertPaymentSql = "INSERT INTO payments (user_id, order_id, stripe_payment_id, amount, currency, status) VALUES (?, ?, ?, ?, ?, ?)";
    $paymentStmt = $conn->prepare($insertPaymentSql);
    if (!$paymentStmt) {
        returnJsonError("Error preparing payment statement: " . $conn->error);
    }
    $status = 'succeeded'; // Assuming the payment was successful
    $paymentStmt->bind_param("iisdss", $userID, $orderID, $paymentIntentId, $grandTotal, $currency, $status);
    if ($paymentStmt->execute() !== TRUE) {
        returnJsonError("Error inserting payment: " . $stmt->error);
    }

    // Send email receipt
    $userEmail = $_SESSION['UserEmail'];
    $subject = "Your order is on the way!";
    $message = "<p>Thank you for your purchase! Here are your order details:</p>";
    $message .= $orderDetails;
    $message .= "<p><strong>Shipping Information:</strong></p>";
    $message .= "<p>Name: $firstName $lastName<br>Address: $addressLine1<br>City: $city<br>State: $state<br>ZIP Code: $zip<br>Country: $country</p>";
    $message .= "<p>Grand Total: $" . $grandTotal . "</p>";

    if (!sendEmail($userEmail, $subject, $message)) {
        returnJsonError("Error sending email");
    }

    // Store payment_intent in session
    $_SESSION['payment_intent'] = $paymentIntentId;

    // Send JSON response indicating success
    echo json_encode(['success' => true]);
    exit();
} catch (Exception $e) {
    logError($e->getMessage());
    returnJsonError("An unexpected error occurred");
}
?>


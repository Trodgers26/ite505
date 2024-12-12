<?php
include 'config.php';
include 'header.php';

date_default_timezone_set('America/New_York');

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['UserEmail'])) {
    echo "<div class='error-message'>User email is not set. Unable to proceed with checkout.</div>";
    exit();
}

$userID = $_SESSION['UserID'];

// Retrieve the user's name
$sql = "SELECT FirstName, LastName FROM userprofiles WHERE UserID='$userID'";
$result = $conn->query($sql);
$userProfile = $result->fetch_assoc();
$firstName = $userProfile['FirstName'] ?? '';
$lastName = $userProfile['LastName'] ?? '';

// Retrieve the latest shipping address
$sql = "SELECT * FROM addresses WHERE UserID='$userID' ORDER BY AddressID DESC LIMIT 1";
$result = $conn->query($sql);
$address = $result->fetch_assoc();

// Initialize address fields
$addressLine1 = $address['AddressLine1'] ?? '';
$addressLine2 = $address['AddressLine2'] ?? '';
$city = $address['City'] ?? '';
$state = $address['State'] ?? '';
$zip = $address['Zip'] ?? '';
$country = $address['Country'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    if (isset($_POST['update_quantities'])) {
        // Update quantities
        foreach ($_POST['quantities'] as $productID => $quantity) {
            if ($quantity == 0) {
                // Remove item from cart
                $removeSql = "DELETE FROM Carts WHERE UserID='$userID' AND ProductID='$productID'";
                $conn->query($removeSql);
            } else {
                // Check available stock
                $productSql = "SELECT Stock FROM Products WHERE ProductID='$productID'";
                $productResult = $conn->query($productSql);
                if (!$productResult) {
                    die("Product query failed: " . $conn->error);
                }
                $product = $productResult->fetch_assoc();
                if ($quantity > $product['Stock']) {
                    $errors[] = "The quantity for product ID $productID exceeds the available stock.";
                } else {
                    // Update the quantity in the cart
                    $updateSql = "UPDATE Carts SET Quantity='$quantity' WHERE UserID='$userID' AND ProductID='$productID'";
                    $conn->query($updateSql);
                }
            }
        }
    } elseif (isset($_POST['place_order'])) {
        // Collect shipping information
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $addressLine1 = $_POST['address_line1'];
        $addressLine2 = $_POST['address_line2'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $zip = $_POST['zip'];
        $country = $_POST['country'];

        // Handle the checkout process here
        $sql = "SELECT * FROM Carts WHERE UserID='$userID'";
        $result = $conn->query($sql);

        $orderDetails = "<table style='width: 100%; border-collapse: collapse;'>";
        $orderDetails .= "<tr style='background-color: #f2f2f2;'>
                            <th style='border: 1px solid #ddd; padding: 8px;'>Image</th>
                            <th style='border: 1px solid #ddd; padding: 8px;'>Product Name</th>
                            <th style='border: 1px solid #ddd; padding: 8px;'>Quantity</th>
                            <th style='border: 1px solid #ddd; padding: 8px;'>Price</th>
                            <th style='border: 1px solid #ddd; padding: 8px;'>Size</th>
                            <th style='border: 1px solid #ddd; padding: 8px;'>Total</th>
                          </tr>";
        $grandTotal = 0;

        // Insert order into the orders table
        $orderDate = date('Y-m-d');
        $insertOrderSql = "INSERT INTO orders (UserID, OrderDate, TotalAmount) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertOrderSql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error . " - SQL: " . $insertOrderSql);
        }
        $stmt->bind_param("isd", $userID, $orderDate, $grandTotal);
        if ($stmt->execute() !== TRUE) {
            die("Error inserting order: " . $stmt->error);
        }
        $orderID = $stmt->insert_id;

        while ($row = $result->fetch_assoc()) {
            $productID = $row['ProductID'];
            $quantity = $row['Quantity'];
            $size = $row['Size'];

            // Check available stock
            $productSql = "SELECT Stock, ProductName, Price, ImagePath FROM Products WHERE ProductID='$productID'";
            $productResult = $conn->query($productSql);
            if (!$productResult) {
                die("Product query failed: " . $conn->error);
            }
            $product = $productResult->fetch_assoc();
            if ($quantity > $product['Stock']) {
                $errors[] = "The quantity for product ID $productID exceeds the available stock.";
            } else {
                // Update the stock in the Products table
                $newStock = $product['Stock'] - $quantity;
                $updateStockSql = "UPDATE Products SET Stock='$newStock' WHERE ProductID='$productID'";
                $conn->query($updateStockSql);

                // Add to order details
                $totalPrice = $quantity * $product['Price'];
                $grandTotal += $totalPrice;
                $imageUrl = 'https://yourdomain.com/' . $product['ImagePath']; // Use absolute URL
                $orderDetails .= "<tr>";
                $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px; text-align: center;'><img src='" . $imageUrl . "' alt='" . $product['ProductName'] . "' width='50'></td>";
                $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px;'>" . $product['ProductName'] . "</td>";
                $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>" . $quantity . "</td>";
                $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>$" . $product['Price'] . "</td>";
                $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>" . $size . "</td>";
                $orderDetails .= "<td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>$" . $totalPrice . "</td>";
                $orderDetails .= "</tr>";

                // Insert order items into the orderitems table
                $insertOrderItemSql = "INSERT INTO orderitems (OrderID, ProductID, Quantity, Price, Sizes) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertOrderItemSql);
                if (!$stmt) {
                    die("Error preparing statement: " . $conn->error . " - SQL: " . $insertOrderItemSql);
                }
                $stmt->bind_param("iiids", $orderID, $productID, $quantity, $product['Price'], $sizes);
                if ($stmt->execute() !== TRUE) {
                    die("Error inserting order item: " . $stmt->error);
                }
            }
        }

        $orderDetails .= "<tr>
                            <td colspan='5' style='border: 1px solid #ddd; padding: 8px; text-align: right;'><strong>Grand Total:</strong></td>
                            <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'><strong>$" . $grandTotal . "</strong></td>
                          </tr>";
        $orderDetails .= "</table>";

        if (empty($errors)) {
            // Update the total amount in the orders table
            $updateOrderTotalSql = "UPDATE orders SET TotalAmount=? WHERE OrderID=?";
            $stmt = $conn->prepare($updateOrderTotalSql);
            if (!$stmt) {
                die("Error preparing statement: " . $conn->error . " - SQL: " . $updateOrderTotalSql);
            }
            $stmt->bind_param("di", $grandTotal, $orderID);
            if ($stmt->execute() !== TRUE) {
                die("Error updating order total: " . $stmt->error);
            }

            // Clear the cart after successful checkout
            $clearCartSql = "DELETE FROM Carts WHERE UserID='$userID'";
            $conn->query($clearCartSql);

            //echo "<div class='success-message'>Thank you for your purchase! Your order has been placed successfully.</div>";
        } else {
            foreach ($errors as $error) {
                echo "<div class='error-message'>$error</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <link rel="stylesheet" type="text/css" href="css/product-styles.css">
    <script src="https://js.stripe.com/v3/"></script> <!-- Include Stripe.js -->
</head>
<body>
    <div class="container">
        <?php
        $sql = "SELECT * FROM Carts WHERE UserID='" . $_SESSION['UserID'] . "'";
        $result = $conn->query($sql);



if ($result->num_rows > 0) {
    echo "<h2>Checkout</h2>";
    echo "<div class='checkout-container'>";
    echo "<div class='checkout-summary'>";
    echo "<form method='post' action='checkout.php'>";
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
    $grandTotal = 0;
    while ($row = $result->fetch_assoc()) {
        $productID = $row['ProductID'];
        $productSql = "SELECT * FROM Products WHERE ProductID='$productID'";
        $productResult = $conn->query($productSql);
        if (!$productResult) {
            die("Product query failed: " . $conn->error);
        }
        $product = $productResult->fetch_assoc();
        $totalPrice = $row['Quantity'] * $product['Price'];
        $grandTotal += $totalPrice;
        echo "<tr>
                <td><img src='" . $product['ImagePath'] . "' alt='" . $product['ProductName'] . "' class='product-image'></td>
                <td>" . $product['ProductName'] . "</td>
                <td><input type='number' name='quantities[" . $productID . "]' value='" . $row['Quantity'] . "' min='0' class='input-field'></td>
                <td>$" . $product['Price'] . "</td>
                <td>" . $row['Size'] . "</td>
                <td>$" . $totalPrice . "</td>
              </tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "<div style='text-align: right; margin-top: 10px;'><strong>Grand Total: $" . $grandTotal . "</strong></div>";
    echo "<input type='submit' name='update_quantities' value='Update Quantities' class='button'>";
    echo "</form>";
    echo "</div>"; // Close checkout-summary
    echo "<div class='checkout-form'>";
    echo "<form id='payment-form'>";
    echo "<h3>Enter Shipping Information</h3>";
    echo "<label for='first_name'>First Name:</label>";
    echo "<input type='text' id='first_name' name='first_name' class='input-field' value='" . htmlspecialchars($firstName) . "' required><br>";
    echo "<label for='last_name'>Last Name:</label>";
    echo "<input type='text' id='last_name' name='last_name' class='input-field' value='" . htmlspecialchars($lastName) . "' required><br>";
    echo "<label for='address_line1'>Address Line 1:</label>";
    echo "<input type='text' id='address_line1' name='address_line1' class='input-field' value='" . htmlspecialchars($addressLine1) . "' required><br>";
    echo "<label for='address_line2'>Address Line 2:</label>";
    echo "<input type='text' id='address_line2' name='address_line2' class='input-field' value='" . htmlspecialchars($addressLine2) . "'><br>";
    echo "<label for='city'>City:</label>";
    echo "<input type='text' id='city' name='city' class='input-field' value='" . htmlspecialchars($city) . "' required><br>";
    echo "<label for='state'>State:</label>";
    echo "<input type='text' id='state' name='state' class='input-field' value='" . htmlspecialchars($state) . "' required><br>";
    echo "<label for='zip'>ZIP Code:</label>";
    echo "<input type='text' id='zip' name='zip' class='input-field' value='" . htmlspecialchars($zip) . "' required><br>";
    echo "<label for='country'>Country:</label>";
    echo "<input type='text' id='country' name='country' class='input-field' value='" . htmlspecialchars($country) . "' required><br>";
    echo "<input type='hidden' id='grandTotal' value='" . $grandTotal . "'>"; // Hidden input for grandTotal
    echo "<h3>Enter Payment Information</h3>";
    echo "<div id='card-element'></div>"; // Stripe card element
    echo "<div id='card-errors' role='alert'></div>"; // Stripe card errors
    echo "<button type='submit' class='button'>Place Order</button>";
    echo "</form>";
    echo "</div>"; // Close checkout-form
    echo "</div>"; // Close checkout-container
} else {
    echo "<p>Your cart is empty.</p>";
}
?>

    </div>

<script>

var stripe = Stripe('pk_test_51QT2hl05mAP4XTAlBAgmESJZ8iSgRAFUh7A4LrKjfuCc1WU1PNnKCTLD2DrDqT5F6xCgIRQ7QE1JSba1roz0nWbF00zF9NWUwz');
var elements = stripe.elements();
var card = elements.create('card');
card.mount('#card-element');

card.on('change', function(event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
    event.preventDefault();

    stripe.createPaymentMethod({
        type: 'card',
        card: card,
        billing_details: {
            name: form.querySelector('input[name=first_name]').value + ' ' + form.querySelector('input[name=last_name]').value,
            address: {
                line1: form.querySelector('input[name=address_line1]').value,
                line2: form.querySelector('input[name=address_line2]').value,
                city: form.querySelector('input[name=city]').value,
                state: form.querySelector('input[name=state]').value,
                postal_code: form.querySelector('input[name=zip]').value,
                country: form.querySelector('input[name=country]').value,
            },
        },
    }).then(function(result) {
        if (result.error) {
            var errorElement = document.getElementById('card-errors');
            errorElement.textContent = result.error.message;
        } else {
            // Ensure that the grandTotal is properly echoed from PHP as an integer value
            var amount = parseInt(document.getElementById('grandTotal').value) * 100;

            fetch('create_payment_intent.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    payment_method_id: result.paymentMethod.id,
                    amount: amount,
                }),
            }).then(function(response) {
                return response.json();
            }).then(function(paymentIntent) {
                if (paymentIntent.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = paymentIntent.error.message;
                } else {
                    // Check the status of the PaymentIntent before confirming
                    if (paymentIntent.status === 'requires_confirmation') {
                        stripe.confirmCardPayment(paymentIntent.client_secret, {
                            payment_method: result.paymentMethod.id,
                        }).then(function (result) {
                            if (result.error) {
                                var errorElement = document.getElementById('card-errors');
                                errorElement.textContent = result.error.message;
                            } else {
                                // After successful payment, proceed to place the order
                                var paymentIntentId = paymentIntent.id;
                                console.log('Payment Intent ID:', paymentIntentId); // Debugging statement

                                fetch('place_order.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        first_name: form.querySelector('input[name=first_name]').value,
                                        last_name: form.querySelector('input[name=last_name]').value,
                                        address_line1: form.querySelector('input[name=address_line1]').value,
                                        address_line2: form.querySelector('input[name=address_line2]').value,
                                        city: form.querySelector('input[name=city]').value,
                                        state: form.querySelector('input[name=state]').value,
                                        zip: form.querySelector('input[name=zip]').value,
                                        country: form.querySelector('input[name=country]').value,
                                        payment_intent_id: paymentIntentId,
                                    }),
                                }).then(function (response) {
                                    return response.json();
                                }).then(function (data) {
                                    if (data.success) {
                                        // Redirect to success page after placing the order
                                        window.location.href = 'payment_success.php';
                                    } else {
                                        var errorElement = document.getElementById('card-errors');
                                        errorElement.textContent = data.error;
                                    }
                                }).catch(function (error) {
                                    console.error('Order placement error:', error);
                                });
                            }
                        });
                    } else {
                        // PaymentIntent is already confirmed, proceed to place the order
                        var paymentIntentId = paymentIntent.id;
                        console.log('Payment Intent ID:', paymentIntentId); // Debugging statement

                        fetch('place_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                first_name: form.querySelector('input[name=first_name]').value,
                                last_name: form.querySelector('input[name=last_name]').value,
                                address_line1: form.querySelector('input[name=address_line1]').value,
                                address_line2: form.querySelector('input[name=address_line2]').value,
                                city: form.querySelector('input[name=city]').value,
                                state: form.querySelector('input[name=state]').value,
                                zip: form.querySelector('input[name=zip]').value,
                                country: form.querySelector('input[name=country]').value,
                                payment_intent_id: paymentIntentId,
                            }),
                        }).then(function(response) {
                            return response.json();
                        }).then(function(data) {
                            if (data.success) {
                                // Redirect to success page after placing the order
                                window.location.href = 'payment_success.php';
                            } else {
                                var errorElement = document.getElementById('card-errors');
                                errorElement.textContent = data.error;
                            }
                        }).catch(function(error) {
                            console.error('Order placement error:', error);
                        });
                    }
                }
            });
        }
    });
});


</script>
    
</body>
</html>





<?php
include 'config.php';
include 'header.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM Carts WHERE UserID='" . $_SESSION['UserID'] . "'";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cart</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/product-styles.css">
</head>
<body>
    <div class="container">
        <center><h1>Cart</h1></center>
        <form method="post" action="update_cart.php" id="cart-form">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Size</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>    </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $productID = $row['ProductID'];
                            $productSql = "SELECT * FROM Products WHERE ProductID='$productID'";
                            $productResult = $conn->query($productSql);
                            if (!$productResult) {
                                die("Product query failed: " . $conn->error);
                            }
                            $product = $productResult->fetch_assoc();
                            $totalPrice = $row['Quantity'] * $product['Price'];
                            $sizes = explode(',', $product['Sizes']);
                            echo "<tr id='cart-item-" . $row['CartID'] . "'>
                                <td><img src='" . $product['ImagePath'] . "' alt='" . $product['ProductName'] . "' class='product-image'></td>
                                <td>" . $product['ProductName'] . "</td>
                                <td><input type='number' name='quantity[" . $row['CartID'] . "]' value='" . $row['Quantity'] . "' min='1' class='input-field'></td>
                                <td>
                                    <select name='size[" . $row['CartID'] . "]' class='input-field'>";
                                    foreach ($sizes as $size) {
                                        $selected = ($size == $row['Size']) ? "selected" : "";
                                        echo "<option value='$size' $selected>$size</option>";
                                    }
                                    echo "</select>
                                </td>
                                <td>$" . $product['Price'] . "</td>
                                <td>$" . $totalPrice . "</td>
                                <td>
                                    <button type='button' class='button remove-button' data-cart-id='" . $row['CartID'] . "'>Remove</button>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>Your cart is empty.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <center>
                <input type="submit" value="Update Cart" class="button">
                <a href="checkout.php" class="button">Proceed to Checkout</a>
                <a href="clear_cart.php" class="button">Clear Cart</a>
            </center>
        </form>
    </div>

    <script>
        document.querySelectorAll('.remove-button').forEach(button => {
            button.addEventListener('click', function() {
                const cartID = this.getAttribute('data-cart-id');
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'cartID=' + cartID
                })
                .then(response => response.text())
                .then(data => {
                    if (data === 'success') {
                        document.getElementById('cart-item-' + cartID).remove();
                    } else {
                        alert('Failed to remove item from cart.');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>
</html>

<?php include 'footer.php'; ?>



<?php
include 'config.php';
include 'header.php';

$sql = "SELECT * FROM Products";
$result = $conn->query($sql);
?>

<h1>Shop</h1>
<ul>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<li>
                <img src='" . $row['ImagePath'] . "' alt='" . $row['ProductName'] . "' style='width:100px;height:100px;'>
                " . $row['ProductName'] . " - $" . $row['Price'] . "
                <form method='post' action='add_to_cart.php'>
                    <input type='hidden' name='productID' value='" . $row['ProductID'] . "'>
                    Quantity: <input type='number' name='quantity' value='1' min='1'>
                    <input type='submit' value='Add to Cart'>
                </form>
            </li>";
        }
    } else {
        echo "No products available.";
    }
    ?>
</ul>
<a href="cart.php">View Cart</a>

<?php include 'footer.php'; ?>



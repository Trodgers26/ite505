<?php
include 'config.php';
include 'header.php';

$productID = $_GET['productID'] ?? null;
$category = $_GET['category'] ?? 'Men'; // Default to 'Men' if category is not provided

if ($productID) {
    $sql = "SELECT * FROM Products WHERE ProductID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
} else {
    echo "<p>Product not found.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $product['ProductName']; ?></title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/product-styles.css">
    <style>
        .product-detail {
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }
        .product-detail img {
            width: 400px;
            height: auto;
        }
        .product-info {
            max-width: 600px;
        }
        .product-info h1 {
            font-size: 2em;
            margin-bottom: 20px;
        }
        .product-info p {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .product-info form {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .product-info form input,
        .product-info form select {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 1em;
        }
        .product-info form .button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .product-info form .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="product-detail">
            <img src="<?php echo $product['ImagePath']; ?>" alt="<?php echo $product['ProductName']; ?>">
            <div class="product-info">
                <h1><?php echo $product['ProductName']; ?></h1>
                <p><?php echo $product['Description']; ?></p>
                <p>Price: $<?php echo $product['Price']; ?></p>
                <form method="post" action="add_to_cart.php">
                    <input type="hidden" name="productID" value="<?php echo $product['ProductID']; ?>">
                    Quantity: <input type="number" name="quantity" value="1" min="1">
                    Size: 
                    <select name="size">
                        <?php
                        $sizes = explode(',', $product['Sizes']);
                        foreach ($sizes as $size) {
                            echo "<option value='$size'>$size</option>";
                        }
                        ?>
                    </select>
                    <input type="submit" value="Add to Cart" class="button">
                </form>
            </div>
        </div>
        <center><a href="products_<?php echo strtolower($category); ?>.php" class="button">Back to <?php echo $category; ?>'s Products</a></center>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>



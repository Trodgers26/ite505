<?php
include 'config.php';
include 'header.php';

$sql = "SELECT * FROM Products WHERE Category='Women'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Women's Products</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/product-styles.css">
    <style>
        .product-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .product-item {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            width: 200px;
        }
        .product-item img {
            width: 100%;
            height: auto;
            border: none;
            outline: none;
        }
        .product-item h3 {
            font-size: 1.2em;
            margin: 10px 0;
        }
        .product-item p {
            margin: 5px 0;
        }
        .product-item a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background-color: inherit;
            color: inherit;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            outline: none;
        }
        .product-item a:hover {
            background-color: inherit;
        }
        .product-item a img {
            border: none;
            outline: none;
        }
        .product-item a:focus {
            outline: none;
        }
        .product-item a:focus img {
            outline: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><center>Women's Track / Jackets</center></h1>
        <div class="product-gallery">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $imageUrl = $row['ImagePath'];
                    echo "<div class='product-item'>
                        <a href='product_detail.php?productID=" . $row['ProductID'] . "&category=Women'>
                            <img src='" . $imageUrl . "' alt='" . $row['ProductName'] . "'>
                        </a>
                        <h3>" . $row['ProductName'] . "</h3>
                        <p>$" . $row['Price'] . "</p>
                        
                    </div>";
                    // Debugging output
                    echo "<!-- Image URL: " . $imageUrl . " -->";
                }
            } else {
                echo "<p>No products available.</p>";
            }
            ?>
        </div>
        <center><a href="cart.php" class="button">View Cart</a></center>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>


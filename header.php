<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soup Boi Athletics</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
    <header>
        <h1>SOUP BOI ATHLETICS || fitness with attitude</h1>
    </header>
    <nav>
        <ul class="nav-links left-nav">
            <li><a href="index.php">Home</a></li>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Shop</a>
                <div class="dropdown-content">
                    <a href="products_men.php">Men's</a>
                    <a href="products_women.php">Women's</a>
                    <a href="products_accessories.php">Accessories</a>
                </div>
            </li>
            <li><a href="about.php">About</a></li>
            <li><a href="blog.php">Blog</a></li>
        </ul>
        <ul class="nav-links right-nav">
            <?php if (isset($_SESSION['UserID'])): ?>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Sign Up</a></li>
                <li><a href="cart.php">Cart</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</body>
</html>

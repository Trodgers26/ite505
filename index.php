<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Soup Boi Athletics</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>

<?php
    // Check if the user is logged in
    if (isset($_SESSION["Username"])) {  // Use "Username" instead of "users2Uid"
        echo "<center><h2>Welcome, " . htmlspecialchars($_SESSION["Username"]) . "!</h2></center>"; 
    } else {
      //  echo "<h2><center>Welcome to Soup Boi Athletics!</center></h2>"; 
    } 
?>

<main>

    <div class="box-container">
        <a href="products_men.php" class="box">
            <img src="img/soupboimens.jpg" alt="Page 1">
        </a>
        <a href="products_accessories.php" class="box">
            <img src="img/soupmain.png" alt="Page 2">
        </a>
        <a href="products_women.php" class="box">
            <img src="img/soupboiwomens.png" alt="Page 3">
        </a>
    </div>
   
</main>
 <br>
 <br>
 <br>
 <br>
 <br>
 <br>
 <br>
 <br>
 <br>
 <br>
 
<?php include 'footer.php'; ?>
</body>
</html>


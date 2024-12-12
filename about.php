<?php
include 'config.php';
include 'header.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>About Us</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/product-styles.css">
    <style>
        .about-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .about-image {
            max-width: 300px;
            margin-right: 20px;
        }
        .about-text {
            max-width: 600px;
        }
        .about-text textarea {
            width: 100%;
            height: 300px;
            padding: 10px;
            font-size: 16px;
            resize: none; /* Make the text area non-resizable */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><center>About Us</center></h1>
        <div class="about-container">
            <img src="img/soupboiabout.jpg" alt="About Soup Boi Athletics" class="about-image">
            <div class="about-text">
                <textarea placeholder="Soup Boi Athletics was born from the thrist for something new.
                                        Something bold. Something authentic.
                                        Join us as we revolutionize altletic experiences through seriously drippy threads.
                                        You'll be glad you did."></textarea>
            </div>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>




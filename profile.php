<?php
include 'config.php';
include 'header.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['UserID'];

// Handle profile and address update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phoneNumber = $_POST['phone_number'];
    $dateOfBirth = $_POST['date_of_birth'];
    $addressLine1 = $_POST['address_line1'];
    $addressLine2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $country = $_POST['country'];

    // Debugging output
    echo "<pre>";
    echo "First Name: $firstName\n";
    echo "Last Name: $lastName\n";
    echo "Phone Number: $phoneNumber\n";
    echo "Date of Birth: $dateOfBirth\n";
    echo "Address Line 1: $addressLine1\n";
    echo "Address Line 2: $addressLine2\n";
    echo "City: $city\n";
    echo "State: $state\n";
    echo "ZIP: $zip\n";
    echo "Country: $country\n";
    echo "</pre>";

    // Update profile
    $sql = "UPDATE userprofiles 
            SET FirstName=?, LastName=?, PhoneNumber=?, DateOfBirth=? 
            WHERE UserID=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ssssi", $firstName, $lastName, $phoneNumber, $dateOfBirth, $userID);
    if ($stmt->execute() !== TRUE) {
        echo "Error updating profile: " . $stmt->error;
    } else {
        echo "Profile updated successfully!";
    }

    // Insert or update address
    $sql = "INSERT INTO addresses (UserID, AddressLine1, AddressLine2, City, State, Zip, Country) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE AddressLine1=VALUES(AddressLine1), AddressLine2=VALUES(AddressLine2), City=VALUES(City), State=VALUES(State), Zip=VALUES(Zip), Country=VALUES(Country)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("issssss", $userID, $addressLine1, $addressLine2, $city, $state, $zip, $country);
    if ($stmt->execute() !== TRUE) {
        echo "Error updating/inserting address: " . $stmt->error;
    } else {
        echo "Address updated successfully!";
    }

    // Redirect to index page after update
    header("Location: index.php");
    exit();
}

// Retrieve profile information
$sql = "SELECT * FROM userprofiles WHERE UserID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

$firstName = $profile['FirstName'] ?? '';
$lastName = $profile['LastName'] ?? '';
$phoneNumber = $profile['PhoneNumber'] ?? '';
$dateOfBirth = $profile['DateOfBirth'] ?? '';

// Retrieve address
$sql = "SELECT * FROM addresses WHERE UserID=? ORDER BY AddressID DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$address = ($result->num_rows > 0) ? $result->fetch_assoc() : [];

$addressLine1 = $address['AddressLine1'] ?? '';
$addressLine2 = $address['AddressLine2'] ?? '';
$city = $address['City'] ?? '';
$state = $address['State'] ?? '';
$zip = $address['Zip'] ?? '';
$country = $address['Country'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 10;
            padding: 0;
        }
        .container {
            max-width: fit-content;
            margin: 20px auto;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .form-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .form-column {
            flex: 1;
            min-width: 200px;
        }
        .form-group {
            margin-bottom: 8px;
        }
        label {
            display: block;
            margin-bottom: 3px;
            font-weight: bold;
            font-size: 12px;
        }
        input, select {
            width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 12px;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .button {
            display: block;
            width: 100%;
            padding: 8px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-align: center;
            margin-top: 10px;
            text-decoration: none;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Profile</h2>
        <form method="post" action="profile.php">
            <input type="hidden" name="update_profile" value="1">
            <div class="form-grid">
                <div class="form-column">
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phoneNumber); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth:</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($dateOfBirth); ?>">
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="address_line1">Address Line 1:</label>
                        <input type="text" id="address_line1" name="address_line1" value="<?php echo htmlspecialchars($addressLine1); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address_line2">Address Line 2:</label>
                        <input type="text" id="address_line2" name="address_line2" value="<?php echo htmlspecialchars($addressLine2); ?>">
                    </div>
                    <div class="form-group">
                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State:</label>
                        <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($state); ?>" required>
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="zip">ZIP Code:</label>
                        <input type="text" id="zip" name="zip" value="<?php echo htmlspecialchars($zip); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="country">Country:</label>
                        <select id="country" name="country" required>
                            <option value="US" <?php if ($country == 'US') echo 'selected'; ?>>United States</option>
                            <option value="VN" <?php if ($country == 'VN') echo 'selected'; ?>>Vietnam</option>
                            <option value="IN" <?php if ($country == 'IN') echo 'selected'; ?>>India</option>
                            <option value="LB" <?php if ($country == 'LB') echo 'selected'; ?>>Lebanon</option>
                            <option value="IE" <?php if ($country == 'IE') echo 'selected'; ?>>Ireland</option>
                            <option value="CA" <?php if ($country == 'CA') echo 'selected'; ?>>Canada</option>
                            <option value="MX" <?php if ($country == 'MX') echo 'selected'; ?>>Mexico</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="button-container">
                <input type="submit" value="Update Profile" class="button">
                <button type="button" class="button" onclick="window.location.href='past_orders.php'">View Past Orders</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>



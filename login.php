<?php
// Start the session to store session variables
session_start();
require 'C:\xampp\htdocs\database.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = ""; // Variable to store error message

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic input validation (You can enhance this later with more checks)
    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        // Prepare and bind query to fetch user details
        $stmt = $conn->prepare("SELECT id, fname, lname, email, pwd_hash, pwd_salt, admin FROM iss_persons WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        // Check if the user exists
        if ($stmt->num_rows > 0) {
            // Bind result variables
            $stmt->bind_result($id, $fname, $lname, $email_db, $pwd_hash, $pwd_salt, $admin);
            $stmt->fetch();
            
            // Hash the entered password with the stored salt
            $entered_pwd_hash = md5($password . $pwd_salt);
            
            // Verify if the password hash matches the stored hash
            if ($entered_pwd_hash === $pwd_hash) {
                // Password is correct, login successful
                $_SESSION['user_id'] = $id;
                $_SESSION['user_email'] = $email_db;
                $_SESSION['user_name'] = $fname . ' ' . $lname;
                $_SESSION['is_admin'] = ($admin === '1'); // Assuming '1' indicates an admin
                
                // Redirect to the issues list page
                header("Location: issues_list.php");
                exit;
            } else {
                // Incorrect password
                $error_message = "Invalid email or password.";
            }
        } else {
            // No user found with the provided email
            $error_message = "Invalid email or password.";
        }
        
        // Close the statement
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Status Report - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 300px;
            margin: 100px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Login</h2>

    <?php if ($error_message): ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
</div>

</body>
</html>
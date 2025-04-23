<?php
    session_start();
    require 'C:\xampp\htdocs\database.php';
    try {
        $connString = "mysql:host=localhost;dbname=cis355";
        $pdo = new PDO($connString, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e) {
        die ($e->getMessage());
    }
    $error = '';
    $success = '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $fName = trim($_POST['fname']);
        $lName = trim($_POST['lname']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if(!empty($fName) || !empty($lName) || !empty($email) || !empty($password) || !empty($confirm_password)){
            if(!preg_match('/@/', $email)) {
                $error = "Must use @ in your email";
            }
            else if ($password !== $confirm_password) {
                $error = "Password and confirm must match";
            }
            else {
                $stmt = $pdo->prepare("SELECT id FROM iss_persons WHERE email = :email");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
    
                if ($stmt->rowCount() > 0) {
                    $error = "An account with that email already exists.";
                } else {
                    $salt = bin2hex(random_bytes(8));
                    $pwd_hash = md5($password . $salt);

                    $stmt = $pdo->prepare("INSERT INTO iss_persons (fname, lname, email, pwd_hash, pwd_salt, admin) 
                                       VALUES (:fname, :lname, :email, :pwd_hash, :pwd_salt, 0)");
                    $stmt->bindParam(':fname', $fName);
                    $stmt->bindParam(':lname', $lName);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':pwd_hash', $pwd_hash);
                    $stmt->bindParam(':pwd_salt', $salt);
                    if ($stmt->execute()) {
                        $success = "Success. Please go back to login";
                    }
                    else {
                        $error = "Failure. Please try again";
                    }
                }       
            }
        }
        else {
            $error = "Please fill in everything";
        }
    }
    $pdo = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div>
        <h1>Register</h1>
        <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div>
            <label for="fname" class="form-label">First Name:</label>
            <input type="text" class="form-control" id="fname" name="fname" required>
        </div>
        <div class="">
        <label for="lname" class="form-label">Last Name:</label>
        <input type="text" class="form-control" id="lname" name="lname" required>
    </div>

    <div class="">
        <label for="email" class="form-label">Email:</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>

    <div class="">
        <label for="password" class="form-label">Password:</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>

    <div class="">
        <label for="confirm_password" class="form-label">Verify Password:</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
    </div>

    <button type="submit" class="btn btn-success">Register</button>
    <button> <a href="issues_list.php">Login</a></button>
</form>

    

    </div>
</body>
</html>

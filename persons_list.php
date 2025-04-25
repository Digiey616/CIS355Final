<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    session_destroy();
    header("Location: login.php");
    exit(); 
}
if ($_SESSION['is_admin'] != 'Y') {
    header("Location: issues_list.php");
    exit();
}
// Database connection (update with your credentials)
require 'C:\xampp/htdocs/database/database.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

try {
    $connString = "mysql:host=localhost;dbname=cis355";
    $pdo = new PDO($connString, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    die ($e->getMessage());
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch issues from the database

$persons_sql = "SELECT * FROM iss_persons ORDER BY fname DESC";
$persons_result = $conn->query($persons_sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['create_person'])) {
        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $mobile = trim($_POST['mobile']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $admin = $_POST['admin'];

        $pwd_salt = bin2hex(random_bytes(8));
        $pwd_hash = md5($password . $pwd_salt);

        $create_sql = "INSERT INTO iss_persons (fname, lname, mobile, email, pwd_hash, pwd_salt, admin) VALUES(?, ?, ?, ?, ?, ?, ?)";
        $create_stmt = $pdo->prepare($create_sql);
        $create_stmt->execute([$fname, $lname, $mobile, $email, $pwd_hash, $pwd_salt, $admin]);
        header("Location: persons_list.php");
        exit();
    }
    if (isset($_POST['update_person'])) {
        $id = $_POST['id'];
        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $mobile = trim($_POST['mobile']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $pwd_salt = trim($_POST['pwd_salt']);
        $admin = ($_POST['admin']);
        $pwd_hash = md5($password . $pwd_salt);

        $update_sql = "UPDATE iss_persons SET fname=?, lname=?, mobile=?, email=?, pwd_hash=?, pwd_salt=?, admin=? WHERE id=?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$fname, $lname, $mobile, $email, $pwd_hash, $pwd_salt, $admin, $id]);
        header("Location: persons_list.php");
        exit();
    }
    if (isset($_POST['delete_person'])) {
        $id = $_POST['id'];
        $delete_sql = "DELETE FROM iss_persons WHERE id=?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$id]);

        header("Location: persons_list.php");
        exit();
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        .button-container {
            margin: 20px 0;
        }

        .add-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .add-button:hover {
            background-color: #45a049;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 5px 10px;
            cursor: pointer;
        }

        .read-only {
            background-color: #f0f0f0;
        }

        .confirmation {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container mt-3">
    <h1>Persons List</h1>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="button-container">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPersonModal">+</button>
        </div>
        <a href="issues_list.php" class="btn btn-warning">I</a>
        <a href="logout.php" class="btn btn-warning">Logout</a>
    </div>


    <table class="table table-striped table-sm mt-2">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Admin?</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // If there are issues, display them
            if ($persons_result->num_rows > 0) {
                // Loop through and display each issue
                while ($row = $persons_result->fetch_assoc()) {

                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['fname']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['lname']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['admin']) . "</td>";

                    // Action buttons (R=Read, U=Update, D=Delete)
                    echo "<td>";
                    echo "<div class='action-buttons'>";
                    ?>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#readPerson<?= $row['id']; ?>">R</button>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updatePerson<?= $row['id']; ?>">U</button>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deletePerson<?= $row['id']; ?>">D</button>
                    <?php     
                    echo "</div>";
                    echo "</td>";
                    echo "</tr>";

                    ?>
                    <div class="modal fade" id="addPersonModal" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header bg-success text-white">
                                           <h5 class="modal-title">Add New Person</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <form method="POST" enctype="multipart/form-data">
                                               <label for="fname">First name</label>
                                               <input type="text" name="fname" class="form-control mb-2" required>
       
                                               <label for="lname">Last name</label>
                                               <input type="text" name="lname" class="form-control mb-2" required>
       
                                               <label for="mobile">Mobile</label>
                                               <input type="text" name="mobile" class="form-control mb-2">
       
                                               <label for="email">Email</label>
                                               <input type="text" name="email" class="form-control mb-2" required>
       
                                               <label for="password">Password</label>
                                               <input type="text" name="password" class="form-control mb-2" required>

       
                                               <label for="admin">Admin?</label>
                                               <input type="text" name="admin" class="form-control mb-2" required>
                                               <button type="submit" name="create_person" class="btn btn-success">Add Person</button>
                                           </form>
                                       </div>
                                   </div>
                               </div>
                           </div>
       
       
                           <!-- Read Modal -->
                           <div class="modal fade" id="readPerson<?= $row['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header">
                                           <h5 class="modal-title">Person Details</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <p><strong>ID:</strong> <?= htmlspecialchars($row['id']); ?></p>
                                           <p><strong>First name:</strong> <?= htmlspecialchars($row['fname']); ?></p>
                                           <p><strong>Last name:</strong> <?= htmlspecialchars($row['lname']); ?></p>
                                           <p><strong>Mobile:</strong> <?= htmlspecialchars($row['mobile']); ?></p>
                                           <p><strong>Email:</strong> <?= htmlspecialchars($row['email']); ?></p>
                                           <p><strong>pwd_hash:</strong> <?= htmlspecialchars($row['pwd_hash']); ?></p>
                                           <p><strong>pwd_salt:</strong> <?= htmlspecialchars($row['pwd_salt']); ?></p>
                                           <p><strong>Admin:</strong> <?= htmlspecialchars($row['admin']); ?></p>     
                                       </div>
                                   </div>
                               </div>
                           </div>
       
                           <!-- Update Modal -->
                           <div class="modal fade" id="updatePerson<?= $row['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header">
                                           <h5 class="modal-title">Update Person</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <form method="POST">
                                               <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                               <label for="fname">First Name</label>
                                               <input type="text" name="fname" class="form-control mb-2" value="<?= htmlspecialchars($row['fname']); ?>" required>
                                               <label for="lname">Last Name</label>
                                               <input type="text" name="lname" class="form-control mb-2" value="<?= htmlspecialchars($row['lname']); ?>" required>
                                               <label for="mobile">Mobile</label>
                                               <input type="text" name="mobile" class="form-control mb-2" value="<?= $row['mobile']; ?>">
                                               <label for="email">Email</label>
                                               <input type="text" name="email" class="form-control mb-2" value="<?= $row['email']; ?>" required>
                                               <label for="password">Password</label>
                                               <input type="text" name="password" class="form-control mb-2" required>
                                               <label for="pwd_salt">pwd_salt</label>
                                               <input type="text" name="pwd_salt" class="form-control mb-2" value="<?= $row['pwd_salt']; ?>" required>
                                               <label for="admin">Admin</label>
                                               <input type="text" name="admin" class="form-control mb-2" value="<?= $row['admin']; ?>">
                                               <button type="submit" name="update_person" class="btn btn-primary">Save Changes</button>
                                           </form>
                                       </div>
                                   </div>
                               </div>
                           </div>
       
                           <!-- Delete Modal -->
                           <div class="modal fade" id="deletePerson<?= $row['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header bg-danger text-white">
                                           <h5 class="modal-title">Confirm Deletion</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <p>Are you sure you want to delete this person?</p>
                                           <p><strong>ID:</strong> <?= htmlspecialchars($row['id']); ?></p>
                                           <p><strong>First Name:</strong> <?= htmlspecialchars($row['fname']); ?></p>
                                           <p><strong>Last Name:</strong> <?= htmlspecialchars($row['lname']); ?></p>
                                           <p><strong>Mobile:</strong> <?= htmlspecialchars($row['mobile']); ?></p>
                                           <p><strong>Email:</strong> <?= htmlspecialchars($row['email']); ?></p>
                                           <p><strong>pwd_hash:</strong> <?= htmlspecialchars($row['pwd_hash']); ?></p>
                                           <p><strong>pwd_salt:</strong> <?= htmlspecialchars($row['pwd_salt']); ?></p>
                                           <p><strong>Admin:</strong> <?= htmlspecialchars($row['admin']); ?></p>
       
                                           <form method="POST">
                                               <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                               <button type="submit" name="delete_person" class="btn btn-danger">Delete</button>
                                               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                           </form>
                                       </div>
                                   </div>
                               </div>
                           </div>
           <?php     }
            }
             else {
                echo "<tr><td colspan='10'>No Persons found</td></tr>";
             }
             ?>
              
        </tbody>
    </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
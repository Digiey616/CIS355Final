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
require 'C:\xampp\htdocs\database\database.php';

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

$issues_sql = "SELECT id, short_description FROM iss_issues ORDER BY open_date DESC";
$issues_result = $conn->query($issues_sql);

$persons_sql = "SELECT id, fname, lname FROM iss_persons ORDER BY lname ASC";
$persons_result = $conn->query($persons_sql);

$comments_sql = "SELECT * FROM iss_comments";
$comments_result = $conn->query($comments_sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['create_comment'])) {
        $per_id = $_POST['per_id'];
        $iss_id = $_POST['iss_id'];
        $short_comment = trim($_POST['short_comment']);
        $long_comment = trim($_POST['long_comment']);
        $posted_date = trim($_POST['posted_date']);

        $create_sql = "INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date) VALUES(?, ?, ?, ?, ?)";
        $create_stmt = $pdo->prepare($create_sql);
        $create_stmt->execute([$per_id, $iss_id, $short_comment, $long_comment, $posted_date]);
        header("Location: comments_list.php");
        exit();
    }
    if (isset($_POST['update_comment'])) {
        $id = $_POST['id'];
        $per_id = trim($_POST['per_id']);
        $iss_id = trim($_POST['iss_id']);
        $short_comment = trim($_POST['short_comment']);
        $long_comment = trim($_POST['long_comment']);
        $posted_date = trim($_POST['posted_date']);

        $update_sql = "UPDATE iss_comments SET per_id=?, iss_id=?, short_comment=?, long_comment=?, posted_date=? WHERE id=?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$per_id, $iss_id, $short_comment, $long_comment, $posted_date, $id]);
        header("Location: comments_list.php");
        exit();
    }
    if (isset($_POST['delete_comment'])) {
        $id = $_POST['id'];
        $delete_sql = "DELETE FROM iss_comments WHERE id=?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$id]);

        header("Location: comments_list.php");
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
    <h1>Comments List</h1>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="button-container">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCommentModal">+</button>
        </div>
        <a href="issues_list.php" class="btn btn-warning">I</a>
        <a href="logout.php" class="btn btn-warning">Logout</a>
    </div>


    <table class="table table-striped table-sm mt-2">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Person ID</th>
                <th>Issue ID</th>
                <th>Short Comment</th>
                <th>Long Comment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // If there are issues, display them
            if ($comments_result->num_rows > 0) {
                // Loop through and display each issue
                while ($row = $comments_result->fetch_assoc()) {

                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['per_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['iss_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['short_comment']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['long_comment']) . "</td>";

                    // Action buttons (R=Read, U=Update, D=Delete)
                    echo "<td>";
                    echo "<div class='action-buttons'>";
                    ?>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#readComment<?= $row['id']; ?>">R</button>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateComment<?= $row['id']; ?>">U</button>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteComment<?= $row['id']; ?>">D</button>
                    <?php     
                    echo "</div>";
                    echo "</td>";
                    echo "</tr>";

                    ?>
                    <div class="modal fade" id="addCommentModal" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header bg-success text-white">
                                           <h5 class="modal-title">Add New Comment</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <form method="POST" enctype="multipart/form-data">
                                               <label for="per_id">Person ID</label>
                                               <select name="per_id" class="form-control mb-3">
                                                   <option value="">-- Select Person --</option>
                                                   <?php while ($person = $persons_result->fetch_assoc()) { ?>
                                                       <option value="<?= $person['id']; ?>">
                                                           <?= htmlspecialchars($person['lname'] . ', ' . $person['fname']) . ' (' . $person['id'] .  ') '; ?>
                                                       </option>
                                                       <?php } ?>
                                                </select>
       
                                               <label for="iss_id">Issue ID</label>
                                               <select name="iss_id" class="form-control mb-3">
                                                   <option value="">-- Select Issue --</option>
                                                   <?php while ($issue = $issues_result->fetch_assoc()) { ?>
                                                       <option value="<?= $issue['id']; ?>">
                                                           <?= htmlspecialchars($issue['short_description']) . ' (' . $issue['id'] .  ') '; ?>
                                                       </option>
                                                       <?php } ?>
                                                </select>
       
                                               <label for="short_comment">Short Comment</label>
                                               <input type="text" name="short_comment" class="form-control mb-2" required>
       
                                               <label for="long_comment">Long Comment</label>
                                               <input type="text" name="long_comment" class="form-control mb-2" required>
       
                                               <label for="posted_date">Posted Date</label>
                                               <input type="date" name="posted_date" class="form-control mb-2">
                                               <button type="submit" name="create_comment" class="btn btn-success">Add Comment</button>
                                           </form>
                                       </div>
                                   </div>
                               </div>
                           </div>
       
       
                           <!-- Read Modal -->
                           <div class="modal fade" id="readComment<?= $row['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header">
                                           <h5 class="modal-title">Comment Details</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <p><strong>ID:</strong> <?= htmlspecialchars($row['id']); ?></p>
                                           <p><strong>Person ID:</strong> <?= htmlspecialchars($row['per_id']); ?></p>
                                           <p><strong>Issue ID:</strong> <?= htmlspecialchars($row['iss_id']); ?></p>
                                           <p><strong>Short Comment:</strong> <?= htmlspecialchars($row['short_comment']); ?></p>
                                           <p><strong>Long Comment:</strong> <?= htmlspecialchars($row['long_comment']); ?></p>
                                           <p><strong>Posted Date:</strong> <?= htmlspecialchars($row['posted_date']); ?></p> 
                                       </div>
                                   </div>
                               </div>
                           </div>
       
                           <!-- Update Modal -->
                           <div class="modal fade" id="updateComment<?= $row['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header">
                                           <h5 class="modal-title">Update Comment</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <form method="POST">
                                                <label for="per_id">Person ID</label>
                                                <input type="number" name="per_id"  class="form-control mb-2" value="<?= $row['per_id']; ?>">
                                                <label for="iss_id">Issue ID</label>
                                                <input type="number" name="iss_id"  class="form-control mb-2" value="<?= $row['iss_id']; ?>">
                                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                                <label for="short_comment">Short Comment</label required>
                                                <input type="text" name="short_comment" class="form-control mb-2" value="<?= $row['short_comment']; ?>" required>
                                                <label for="long_comment">Long Comment</label>
                                                <input type="text" name="long_comment" class="form-control mb-2" value="<?= $row['long_comment']; ?>" required>
                                                <label for="posted_date">Posted Date</label>
                                                <input type="date" name="posted_date" class="form-control mb-2">
                                                <button type="submit" name="update_comment" class="btn btn-primary">Save Comment</button>
                                           </form>
                                       </div>
                                   </div>
                               </div>
                           </div>
       
                           <!-- Delete Modal -->
                           <div class="modal fade" id="deleteComment<?= $row['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header bg-danger text-white">
                                           <h5 class="modal-title">Confirm Deletion</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <p>Are you sure you want to delete this comment?</p>
                                           <p><strong>ID:</strong> <?= htmlspecialchars($row['id']); ?></p>
                                           <p><strong>Person ID:</strong> <?= htmlspecialchars($row['per_id']); ?></p>
                                           <p><strong>Issue ID:</strong> <?= htmlspecialchars($row['iss_id']); ?></p>
                                           <p><strong>Short Comment:</strong> <?= htmlspecialchars($row['short_comment']); ?></p>
                                           <p><strong>Long Comment:</strong> <?= htmlspecialchars($row['long_comment']); ?></p>
                                           <p><strong>Posted Date:</strong> <?= htmlspecialchars($row['posted_date']); ?></p>
       
                                           <form method="POST">
                                               <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                               <button type="submit" name="delete_comment" class="btn btn-danger">Delete</button>
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
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    session_destroy();
    header("Location: login.php");
    exit(); 
}


// Database connection (update with your credentials)
require 'C:\xampp\htdocs\database.php';

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
$persons_sql = "SELECT id, fname, lname FROM iss_persons ORDER BY lname ASC";
$persons_result = $conn->query($persons_sql);
$persons_stmt = $pdo->query($persons_sql);
$persons = $persons_stmt->fetchAll(PDO::FETCH_ASSOC);

$issues_sql = "SELECT * FROM iss_issues ORDER BY open_date DESC";
$issues_result = $conn->query($issues_sql);
$issues_stmt = $pdo->query($issues_sql);
$issues = $issues_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    if($_FILES['pdf_attachment']['size'] > 0) {
        $fileTmpPath = $_FILES['pdf_attachment']['tmp_name'];
        $fileName = $_FILES ['pdf_attachment']['name'];
        $fileSize = $_FILES ['pdf_attachment']['size'];
        $fileType = $_FILES ['pdf_attachment']['type'];
        $fileNameCmps = explode(".", $fileName );
        $fileExtension = strtolower(end( $fileNameCmps));

        if($fileExtension !== 'pdf') {
            die("Only PDF files allowed");
        }
        if($fileSize > 2 * 1024 * 1024) {
            die("File size exceeds 2MB limit");
        }
        $newFileName = MD5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        if(!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            $attchmentPath = $dest_path;
        }
        else {
            die("error moving file");
        }
    }

    if (isset($_POST['create_issue'])) {
        $short_description = trim($_POST['short_description']);
        $long_description = trim($_POST['long_description']);
        $open_date = $_POST['open_date'];
        $close_date = $_POST['close_date'];
        $priority = $_POST['priority'];
        $org = trim($_POST['org']);
        $project = trim($_POST['project']);
        $per_id = $_POST['per_id'];

        $create_sql = "INSERT INTO iss_issues (short_description, long_description, open_date, close_date, priority, org, project, per_id, pdf_attachment) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $create_stmt = $pdo->prepare($create_sql);
        $create_stmt->execute([$short_description, $long_description, 
            $open_date, $close_date, $priority, $org, $project, $per_id,
            $newFileName]);
        header("Location: issues_list.php");
        exit();
    }
    if (isset($_POST['update_issue'])) {
        if( !( $_SESSION['is_admin'] == "Y" || $_SESSION['user_id'] == $_POST['per_id'] ) ) {
            header("Location: issues_list.php"); 
            exit();
        }
        $id = $_POST['id'];
        $short_description = trim($_POST['short_description']);
        $long_description = trim($_POST['long_description']);
        $open_date = $_POST['open_date'];
        $close_date = $_POST['close_date'];
        $priority = $_POST['priority'];
        $org = trim($_POST['org']);
        $project = trim($_POST['project']);
        $per_id = $_POST['per_id'];

        $update_sql = "UPDATE iss_issues SET short_description=?, long_description=?, open_date=?, close_date=?, priority=?, org=?, project=?, per_id=? WHERE id=?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$short_description, $long_description, $open_date, $close_date, $priority, $org, $project, $per_id, $id]);
        header("Location: issues_list.php");
        exit();
    }
    if (isset($_POST['delete_issue'])) {
        if( !( $_SESSION['is_admin'] == "Y" || $_SESSION['user_id'] == $_POST['per_id'] ) ) {
            header("Location: issues_list.php"); 
            exit();
        }
        $id = $_POST['id'];
        $delete_sql = "DELETE FROM iss_issues WHERE id=?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$id]);

        header("Location: issues_list.php");
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
        header("Location: issues_list.php");
        exit();
    }
    if (isset($_POST['delete_comment'])) {
        $id = $_POST['id'];
        $delete_sql = "DELETE FROM iss_comments WHERE id=?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$id]);

        header("Location: issues_list.php");
        exit();
    }
    if (isset($_POST['create_comment'])) {
        $per_id = $_POST['per_id'];
        $iss_id = $_POST['iss_id'];
        $short_comment = trim($_POST['short_comment']);
        $long_comment = trim($_POST['long_comment']);
        $posted_date = trim($_POST['posted_date']);

        $create_sql = "INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date) VALUES(?, ?, ?, ?, ?)";
        $create_stmt = $pdo->prepare($create_sql);
        $create_stmt->execute([$per_id, $iss_id, $short_comment, $long_comment, $posted_date]);
        header("Location: issues_list.php");
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
    <h1>Issues List</h1>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="button-container">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addIssueModal">+</button>
        </div>
        <?php 
        if (( $_SESSION['is_admin'] == "Y" ) ) {
            echo '<div class="button-container"><a href="persons_list.php" class="add-button">P</a> <!-- Link to the form to add a new issue --></div>';
            echo  '<div class="button-container">
            <a href="comments_list.php" class="add-button">C</a> <!-- Link to the form to add a new issue -->
        </div>';
        }
        ?>
        <a href="logout.php" class="btn btn-warning">Logout</a>
    </div>


    <table class="table table-striped table-sm mt-2">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Short Description</th>
                <th>Long Description</th>
                <th>Open Date</th>
                <th>Close Date</th>
                <th>Priority</th>
                <th>Organization</th>
                <th>Project</th>
                <th>Per ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // If there are issues, display them
            if ($issues_result->num_rows > 0) {
                // Loop through and display each issue
                while ($row = $issues_result->fetch_assoc()) {

                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['short_description']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['long_description']) . "</td>";
                    echo "<td>" . $row['open_date'] . "</td>";
                    echo "<td>" . $row['close_date'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['priority']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['org']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['project']) . "</td>";
                    echo "<td>" . $row['per_id'] . "</td>";

                    // Action buttons (R=Read, U=Update, D=Delete)
                    echo "<td>";
                    echo "<div class='action-buttons'>";
                    ?>
                     <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#readIssue<?= $row['id']; ?>">R</button>
                    <?php 
                    if ($row['per_id'] == $_SESSION['user_id'] || $_SESSION['is_admin'] == "Y") {
                    ?>
                         <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateIssue<?= $row['id']; ?>">U</button>
                         <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteIssue<?= $row['id']; ?>">D</button>
                    <?php     
                    }
                    echo "</div>";
                    echo "</td>";
                    echo "</tr>";

                    ?>
                    <div class="modal fade" id="addIssueModal" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header bg-success text-white">
                                           <h5 class="modal-title">Add New Issue</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <form method="POST" enctype="multipart/form-data">
                                               <label for="short_description">Short Description</label>
                                               <input type="text" name="short_description" class="form-control mb-2" required>
       
                                               <label for="long_description">Long Description</label>
                                               <textarea name="long_description" class="form-control mb-2"></textarea>
       
                                               <label for="open_date">Open Date</label>
                                               <input type="date" name="open_date" class="form-control mb-2" value="<?= date('Y-m-d'); ?>" required>
       
                                               <label for="close_date">Close Date</label>
                                               <input type="date" name="close_date" class="form-control mb-2">
       
                                               <label for="priority">Priority</label>
                                               <input type="text" name="priority" class="form-control mb-2">
       
                                               <label for="org">Org</label>
                                               <input type="text" name="org" class="form-control mb-2">
       
                                               <label for="project">Project</label>
                                               <input type="text" name="project" class="form-control mb-2">
       
                                               <label for="per_id">Person Responsible</label>
                                               <?php
                                               if(( $_SESSION['is_admin'] == "Y" )) { ?>
                                               <select name="per_id" class="form-control mb-3">
                                                   <option value="">-- Select Person --</option>
                                                   <?php while ($person = $persons_result->fetch_assoc()) { ?>
                                                       <option value="<?= $person['id']; ?>">
                                                           <?= htmlspecialchars($person['lname'] . ', ' . $person['fname']) . ' (' . $person['id'] .  ') '; ?>
                                                       </option>
                                                   <?php } ?>
                                               </select>
                                               <?php }
                                               else { ?>
                                                    <select name="per_id" class="fprm-control mb-3">
                                                        <option value="<?= $_SESSION['user_id']; ?>">
                                                            <?= htmlspecialchars($_SESSION['user_name']) . ' (' . $_SESSION['user_id'] . ') '; ?>
                                                        </option>
                                               <?php } ?>
       
                                               <label for="pdf_attachment">PDF</label>
                                               <input type="file" name="pdf_attachment" class="form-control mb-2"
                                                   accept="application/pdf" />
       
                                               <button type="submit" name="create_issue" class="btn btn-success">Add Issue</button>
                                           </form>
                                       </div>
                                   </div>
                               </div>
                           </div>
       
       
                           <!-- Read Modal -->
                           <div class="modal fade" id="readIssue<?= $row['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header">
                                           <h5 class="modal-title">Issue Details</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <p><strong>ID:</strong> <?= htmlspecialchars($row['id']); ?></p>
                                           <p><strong>Short Description:</strong> <?= htmlspecialchars($row['short_description']); ?></p>
                                           <p><strong>Long Description:</strong> <?= htmlspecialchars($row['long_description']); ?></p>
                                           <p><strong>Open Date:</strong> <?= htmlspecialchars($row['open_date']); ?></p>
                                           <p><strong>Close Date:</strong> <?= htmlspecialchars($row['close_date']); ?></p>
                                           <p><strong>Priority:</strong> <?= htmlspecialchars($row['priority']); ?></p>
                                           <p><strong>Organization:</strong> <?= htmlspecialchars($row['org']); ?></p>
                                           <p><strong>Project:</strong> <?= htmlspecialchars($row['project']); ?></p>
                                           <p><strong>Person:</strong> <?= htmlspecialchars($row['per_id']); ?></p>
                                           
                                           
                                           <?php
                                               $com_iss_id = $row['id'];
                                               // Fetch comments this particular issue: gpcorser
                                               $comments_sql = "SELECT * FROM iss_persons, iss_comments 
                                                   WHERE iss_id = $com_iss_id
                                                   AND `iss_persons`.id = per_id
                                                   ORDER BY posted_date DESC";
                                               $comments_result = $conn->query($comments_sql);
                                               $comments_stmt = $pdo->query($comments_sql);
                                               $comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
                                               
                                           ?>
                                           <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCommentModal">+</button>
                <?php  while ($comment = $comments_result->fetch_assoc()) { ?>
                    <div style="font-family: monospace;">
                        <span style="display:inline-block; width: 180px;">
                            <?= htmlspecialchars($comment['lname'] . ", " . $comment['fname']) ?>
                        </span>
                        <span style="display:inline-block; width: 300px;">
                            <?= htmlspecialchars($comment['short_comment']) ?>
                        </span>
                        <span style="display:inline-block; width: 140px;">
                            <?= htmlspecialchars($comment['posted_date']) ?>
                        </span>
                        <span style="display:inline-block; width: 150px;">
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#readComment<?= $comment['id']; ?>">R</button>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateComment<?= $comment['id']; ?>">U</button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteComment<?= $comment['id']; ?>">D</button>
                        </span>
                    </div>
                    <?php } ?>           
                                       </div>
                                   </div>
                               </div>
                           </div>
       
                           <!-- Update Modal -->
                           <div class="modal fade" id="updateIssue<?= $row['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header">
                                           <h5 class="modal-title">Update Issue</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <form method="POST">
                                               <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                               <label for="short_description">Short Description</label>
                                               <input type="text" name="short_description" class="form-control mb-2" value="<?= htmlspecialchars($row['short_description']); ?>" required>
                                               <label for="long_description">Long Description</label>
                                               <textarea name="long_description" class="form-control mb-2"><?= htmlspecialchars($row['long_description']); ?></textarea>
                                               <label for="open_date">Open Date</label>
                                               <input type="date" name="open_date" class="form-control mb-2" value="<?= $row['open_date']; ?>" readonly>
                                               <label for="close_date">Close Date</label>
                                               <input type="date" name="close_date" class="form-control mb-2" value="<?= $row['close_date']; ?>">
                                               <label for="priority">Priority</label>
                                               <input type="text" name="priority" class="form-control mb-2" value="<?= $row['priority']; ?>">
                                               <label for="org">Org</label>
                                               <input type="text" name="org" class="form-control mb-2" value="<?= $row['org']; ?>">
                                               <label for="project">Project</label>
                                               <input type="text" name="project" class="form-control mb-2" value="<?= $row['project']; ?>">
                                               <?php if (( $_SESSION['is_admin'] == "Y" )) { ?>
                                                <label for="per_id">Person Responsible</label>
                                               <input type="number" name="per_id" class="form-control mb-2" value="<?= $row['per_id']; ?>">
                                               <?php } 
                                               else { ?>
                                                <input type="hidden" name="per_id" value="<?= $_SESSION['user_id']; ?>">
                                             <?php  }?>
                                               <button type="submit" name="update_issue" class="btn btn-primary">Save Changes</button>
                                           </form>
                                       </div>
                                   </div>
                               </div>
                           </div>
       
                           <!-- Delete Modal -->
                           <div class="modal fade" id="deleteIssue<?= $row['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header bg-danger text-white">
                                           <h5 class="modal-title">Confirm Deletion</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <p>Are you sure you want to delete this issue?</p>
                                           <p><strong>ID:</strong> <?= htmlspecialchars($row['id']); ?></p>
                                           <p><strong>Short Description:</strong> <?= htmlspecialchars($row['short_description']); ?></p>
                                           <p><strong>Long Description:</strong> <?= htmlspecialchars($row['long_description']); ?></p>
                                           <p><strong>Open Date:</strong> <?= htmlspecialchars($row['open_date']); ?></p>
                                           <p><strong>Close Date:</strong> <?= htmlspecialchars($row['close_date']); ?></p>
                                           <p><strong>Priority:</strong> <?= htmlspecialchars($row['priority']); ?></p>
                                           <p><strong>Organization:</strong> <?= htmlspecialchars($row['org']); ?></p>
                                           <p><strong>Project:</strong> <?= htmlspecialchars($row['project']); ?></p>
                                           <p><strong>Person:</strong> <?= htmlspecialchars($row['per_id']); ?></p>
       
                                           <form method="POST">
                                               <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                               <input type="hidden" name="per_id" class="form-control mb-2" value="<?= $row['per_id']; ?>">
                                               <button type="submit" name="delete_issue" class="btn btn-danger">Delete</button>
                                               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                           </form>
                                       </div>
                                   </div>
                               </div>
                            </div>
                            <?php foreach ($issues as $issue) : ?>
                            <div class="modal fade" id="addCommentModal<?= $issue['id']; ?>" tabindex="-1">
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
                                                   <?php foreach ($persons as $person) : ?>
                                                       <option value="<?= $issue['id']; ?>">
                                                           <?= htmlspecialchars($person['lname'] . ', ' . $person['fname']); ?>
                                                       </option>
                                                       <?php endforeach ?>
                                                </select>

                                               <input type=text name=iss_id value="<?= $issue['id']; ?>">>
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
                           <?php endforeach ?>
                            <?php foreach ($comments as $comment) : ?>
                                <div class="modal fade" id="readComment<?= $comment['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Comment Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>ID:</strong> <?= htmlspecialchars($comment['id']); ?></p>
                                                <p><strong>Person ID:</strong> <?= htmlspecialchars($comment['per_id']); ?></p>
                                                <p><strong>Issue ID:</strong> <?= htmlspecialchars($comment['iss_id']); ?></p>
                                                <p><strong>Short Comment:</strong> <?= htmlspecialchars($comment['short_comment']); ?></p>
                                                <p><strong>Long Comment:</strong> <?= htmlspecialchars($comment['long_comment']); ?></p>
                                                <p><strong>Posted Date:</strong> <?= htmlspecialchars($comment['posted_date']); ?></p> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
       
                           <!-- Update Modal -->
                           <div class="modal fade" id="updateComment<?= $comment['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header">
                                           <h5 class="modal-title">Update Comment</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <form method="POST">
                                                <label for="per_id">Person ID</label>
                                                <input type="number" name="per_id"  class="form-control mb-2" value="<?= $comment['per_id']; ?>">
                                                <label for="iss_id">Issue ID</label>
                                                <input type="number" name="iss_id"  class="form-control mb-2" value="<?= $comment['iss_id']; ?>">
                                                <input type="hidden" name="id" value="<?= $comment["id"]; ?>">
                                                <label for="short_comment">Short Comment</label required>
                                                <input type="text" name="short_comment" class="form-control mb-2" value="<?= $comment['short_comment']; ?>" required>
                                                <label for="long_comment">Long Comment</label>
                                                <input type="text" name="long_comment" class="form-control mb-2" value="<?= $comment['long_comment']; ?>" required>
                                                <label for="posted_date">Posted Date</label>
                                                <input type="date" name="posted_date" class="form-control mb-2" value="<?= $comment['posted_date']; ?>">
                                                <button type="submit" name="update_comment" class="btn btn-primary">Save Comment</button>
                                           </form>
                                       </div>
                                   </div>
                               </div>
                           </div>
       
                           <!-- Delete Modal -->
                           <div class="modal fade" id="deleteComment<?= $comment['id']; ?>" tabindex="-1">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header bg-danger text-white">
                                           <h5 class="modal-title">Confirm Deletion</h5>
                                           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                       </div>
                                       <div class="modal-body">
                                           <p>Are you sure you want to delete this comment?</p>
                                           <p><strong>ID:</strong> <?= htmlspecialchars($comment['id']); ?></p>
                                           <p><strong>Person ID:</strong> <?= htmlspecialchars($comment['per_id']); ?></p>
                                           <p><strong>Issue ID:</strong> <?= htmlspecialchars($comment['iss_id']); ?></p>
                                           <p><strong>Short Comment:</strong> <?= htmlspecialchars($comment['short_comment']); ?></p>
                                           <p><strong>Long Comment:</strong> <?= htmlspecialchars($comment['long_comment']); ?></p>
                                           <p><strong>Posted Date:</strong> <?= htmlspecialchars($comment['posted_date']); ?></p>
       
                                           <form method="POST">
                                               <input type="hidden" name="id" value="<?= $comment['id']; ?>">
                                               <button type="submit" name="delete_comment" class="btn btn-danger">Delete</button>
                                               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                           </form>
                                       </div>
                                   </div>
                               </div>
                           </div>
                           <?php endforeach; ?>
           <?php     }
            }
             else {
                echo "<tr><td colspan='10'>No issues found</td></tr>";
             }
             ?>
              
        </tbody>
    </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close();
$pdo = null;
 ?>
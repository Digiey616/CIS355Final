<?php
// Database connection (update with your credentials)
require 'C:\xampp\htdocs\database.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch issues from the database
$sql = "SELECT * FROM iss_issues";
$result = $conn->query($sql);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file here -->
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

    <div class="button-container">
        <a href="add_issue.php" class="add-button">+</a> <!-- Link to the form to add a new issue -->
    </div>

    <h1>Issues List</h1>

    <table>
        <thead>
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
            if ($result->num_rows > 0) {
                // Loop through and display each issue
                while ($row = $result->fetch_assoc()) {
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
                    echo "<a href='?action=read&id=" . $row['id'] . "'><button>R</button></a>";
                    echo "<a href='?action=update&id=" . $row['id'] . "'><button>U</button></a>";
                    echo "<a href='?action=delete&id=" . $row['id'] . "'><button>D</button></a>";
                    echo "</div>";
                    echo "</td>";

                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No issues found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php
    // Handle the actions (Read, Update, Delete) via GET parameters
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        $id = $_GET['id'];

        // Database connection for action handling
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($action == 'read') {
            // Read action: Show data in read-only mode
            $sql = "SELECT * FROM iss_issues WHERE id = $id";
            $result = $conn->query($sql);
            $issue = $result->fetch_assoc();
            echo "<h2>Issue Details (Read-Only)</h2>";
            echo "<form>";
            echo "<label>id:</label><input type='text' value='" . htmlspecialchars($issue['id']) . "' class='read-only' readonly><br>";
            echo "<label>Short Description:</label><input type='text' value='" . htmlspecialchars($issue['short_description']) . "' class='read-only' readonly><br>";
            echo "<label>Long Description:</label><textarea class='read-only' readonly>" . htmlspecialchars($issue['long_description']) . "</textarea><br>";
            echo "<label>Open Date:</label><input type='date' value='" . $issue['open_date'] . "' class='read-only' readonly><br>";
            echo "<label>Close Date:</label><input type='date' value='" . $issue['close_date'] . "' class='read-only' readonly><br>";
            echo "<label>Priority:</label><input type='text' value='" . htmlspecialchars($issue['priority']) . "' class='read-only' readonly><br>";
            echo "<label>Organization:</label><input type='text' value='" . htmlspecialchars($issue['org']) . "' class='read-only' readonly><br>";
            echo "<label>Project:</label><input type='text' value='" . htmlspecialchars($issue['project']) . "' class='read-only' readonly><br>";
            echo "<label>Per ID:</label><input type='text' value='" . $issue['per_id'] . "' class='read-only' readonly><br>";
            echo "</form>";
        } elseif ($action == 'update') {
            // Update action: Show data in editable form
            $sql = "SELECT * FROM iss_issues WHERE id = $id";
            $result = $conn->query($sql);
            $issue = $result->fetch_assoc();
            echo "<h2>Update Issue</h2>";
            echo "<form action='update_issue.php' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $issue['id'] . "'>";
            echo "<label>Short Description:</label><input type='text' name='short_description' value='" . htmlspecialchars($issue['short_description']) . "'><br>";
            echo "<label>Long Description:</label><textarea name='long_description'>" . htmlspecialchars($issue['long_description']) . "</textarea><br>";
            echo "<label>Open Date:</label><input type='date' name='open_date' value='" . $issue['open_date'] . "'><br>";
            echo "<label>Close Date:</label><input type='date' name='close_date' value='" . $issue['close_date'] . "'><br>";
            echo "<label>Priority:</label><input type='text' name='priority' value='" . htmlspecialchars($issue['priority']) . "'><br>";
            echo "<label>Organization:</label><input type='text' name='org' value='" . htmlspecialchars($issue['org']) . "'><br>";
            echo "<label>Project:</label><input type='text' name='project' value='" . htmlspecialchars($issue['project']) . "'><br>";
            echo "<label>Per ID:</label><input type='text' name='per_id' value='" . $issue['per_id'] . "'><br>";
            echo "<button type='submit'>Update</button>";
            echo "</form>";
        } elseif ($action == 'delete') {
            // Delete action: Show confirmation message
            $sql = "SELECT * FROM iss_issues WHERE id = $id";
            $result = $conn->query($sql);
            $issue = $result->fetch_assoc();
            echo "<h2>Delete Issue</h2>";
            echo "<p>Are you sure you want to delete this issue?</p>";
            echo "<form action='delete_issue.php' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $issue['id'] . "'>";
            echo "<p><strong>Short Description:</strong> " . htmlspecialchars($issue['short_description']) . "</p>";
            echo "<button type='submit' class='confirmation'>Yes, Delete</button>";
            echo "</form>";
            echo "<a href='issues_list.php'>Cancel</a>";
        }

        // Close the connection
        $conn->close();
    }
    ?>

</body>
</html>
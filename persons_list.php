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
$sql = "SELECT * FROM iss_persons";
$result = $conn->query($sql);

// Close the connection
$conn->close();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persons List</title>
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
        <a href="issues_list.php" class="add-button">Issues</a> <!-- Link to the form to add a new issue -->
    </div>

    <h1>Persons List</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>First name</th>
                <th>Last name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Password Hash</th>
                <th>Password Salt</th>
                <th>Admin</th>
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
                    echo "<td>" . htmlspecialchars($row['fname']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['lname']) . "</td>";
                    echo "<td>" . $row['mobile'] . "</td>";
                    echo "<td>" . $row['email'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['pwd_hash']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['pwd_salt']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['admin']) . "</td>";

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
            $sql = "SELECT * FROM iss_persons WHERE id = $id";
            $result = $conn->query($sql);
            $person = $result->fetch_assoc();
            echo "<h2>Person Details (Read-Only)</h2>";
            echo "<form>";
            echo "<label>id:</label><input type='text' value='" . htmlspecialchars($person['id']) . "' class='read-only' readonly><br>";
            echo "<label>First Name:</label><input type='text' value='" . htmlspecialchars($person['fname']) . "' class='read-only' readonly><br>";
            echo "<label>Last Name:</label><input type='text' value='" . htmlspecialchars($person['lname']) . "' class='read-only' readonly><br>";
            echo "<label>Phone:</label><input type='text' value='" . $person['mobile'] . "' class='read-only' readonly><br>";
            echo "<label>Email:</label><input type='text' value='" . $person['email'] . "' class='read-only' readonly><br>";
            echo "<label>Password Hash:</label><input type='text' value='" . htmlspecialchars($person['pwd_hash']) . "' class='read-only' readonly><br>";
            echo "<label>Password Salt:</label><input type='text' value='" . htmlspecialchars($person['pwd_salt']) . "' class='read-only' readonly><br>";
            echo "<label>Admin:</label><input type='text' value='" . htmlspecialchars($person['admin']) . "' class='read-only' readonly><br>";
            echo "</form>";
        } elseif ($action == 'update') {
            // Update action: Show data in editable form
            $sql = "SELECT * FROM iss_persons WHERE id = $id";
            $result = $conn->query($sql);
            $person = $result->fetch_assoc();
            echo "<h2>Update Person</h2>";
            echo "<form action='update_persons.php' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $person['id'] . "'>";
            echo "<label>First Name:</label><input type='text' name='first_name' value='" . htmlspecialchars($person['fname']) . "'><br>";
            echo "<label>Last Name:</label><input type='text' name='last_name' value='" . htmlspecialchars($person['lname']) . "'><br>";
            echo "<label>Phone:</label><input type='text' name='phone' value='" . $person['mobile'] . "'><br>";
            echo "<label>Email:</label><input type='text' name='email' value='" . $person['email'] . "'><br>";
            echo "<label>Password Hash:</label><input type='text' name='pwd_hash' value='" . htmlspecialchars($person['pwd_hash']) . "'><br>";
            echo "<label>Password Salt:</label><input type='text' name='pwd_salt' value='" . htmlspecialchars($person['pwd_salt']) . "'><br>";
            echo "<label>Admin:</label><input type='text' name='admin' value='" . htmlspecialchars($person['admin']) . "'><br>";
            echo "<button type='submit'>Update</button>";
            echo "</form>";
        } elseif ($action == 'delete') {
            // Delete action: Show confirmation message
            $sql = "SELECT * FROM iss_persons WHERE id = $id";
            $result = $conn->query($sql);
            $person = $result->fetch_assoc();
            echo "<h2>Delete Person</h2>";
            echo "<p>Are you sure you want to delete this Person?</p>";
            echo "<form action='delete_person.php' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $person['id'] . "'>";
            echo "<p><strong>First Name:</strong> " . htmlspecialchars($person['fname']) . "</p>";
            echo "<button type='submit' class='confirmation'>Yes, Delete</button>";
            echo "</form>";
            echo "<a href='persons_list.php'>Cancel</a>";
        }

        // Close the connection
        $conn->close();
    }
    ?>

</body>
</html>
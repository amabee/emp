<?php
// Check if emp_app user exists
$conn = new mysqli('localhost', 'root', 'root', 'emp');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Checking for employee users...\n";
$result = $conn->query("SELECT User, Host FROM mysql.user WHERE User LIKE '%emp%'");
if ($result->num_rows > 0) {
    echo "Employee users found:\n";
    while($row = $result->fetch_assoc()) {
        echo "User: " . $row['User'] . ", Host: " . $row['Host'] . "\n";
    }
} else {
    echo "No employee users found.\n";
}

echo "\nAll users in system:\n";
$result = $conn->query("SELECT User, Host FROM mysql.user ORDER BY User");
while($row = $result->fetch_assoc()) {
    echo "User: " . $row['User'] . ", Host: " . $row['Host'] . "\n";
}

$conn->close();
?>

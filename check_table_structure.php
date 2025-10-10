<?php
// Check actual table structure
$conn = new mysqli('localhost', 'root', 'root', 'emp');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "=== TABLE STRUCTURES ===\n\n";

echo "user_type table structure:\n";
$result = $conn->query("DESCRIBE user_type");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\nuser_type table data:\n";
$result = $conn->query("SELECT * FROM user_type");
while($row = $result->fetch_assoc()) {
    print_r($row);
}

$conn->close();
?>

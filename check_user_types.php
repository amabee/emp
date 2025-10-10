<?php
// Check user types in the system
$conn = new mysqli('localhost', 'root', 'root', 'emp');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Checking user types in the system...\n\n";

// Check if user_types table exists
$result = $conn->query("SHOW TABLES LIKE 'user_types'");
if ($result->num_rows > 0) {
    echo "User Types found:\n";
    $result = $conn->query("SELECT * FROM user_types ORDER BY id");
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Type: " . $row['type_name'] . "\n";
    }
} else {
    echo "No user_types table found. Checking users table...\n";
    
    // Check users table structure
    $result = $conn->query("DESCRIBE users");
    echo "\nUsers table structure:\n";
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    // Check distinct user types in users table
    $result = $conn->query("SELECT DISTINCT user_type_id FROM users ORDER BY user_type_id");
    if ($result->num_rows > 0) {
        echo "\nDistinct user type IDs in users table:\n";
        while($row = $result->fetch_assoc()) {
            echo "- " . $row['user_type_id'] . "\n";
        }
    }
    
    // Check if there's a user_type table
    $result = $conn->query("SHOW TABLES LIKE '%user%'");
    echo "\nTables with 'user' in name:\n";
    while($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
}

$conn->close();
?>

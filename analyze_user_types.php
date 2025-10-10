<?php
// Check user types and create role-based grants
$conn = new mysqli('localhost', 'root', 'root', 'emp');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "=== USER TYPES IN SYSTEM ===\n\n";

// Get user types
$result = $conn->query("SELECT * FROM user_type ORDER BY user_type_id");
if ($result->num_rows > 0) {
    echo "Available User Types:\n";
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['user_type_id'] . " - " . $row['user_type'] . " (" . $row['description'] . ")\n";
    }
} else {
    echo "No user types found.\n";
}

// Show current users and their types
echo "\n=== CURRENT USERS ===\n";
$result = $conn->query("
    SELECT u.username, ut.user_type, ut.description 
    FROM users u 
    JOIN user_type ut ON u.user_type_id = ut.user_type_id 
    ORDER BY ut.user_type_id
");

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "User: " . $row['username'] . " -> " . $row['user_type'] . " (" . $row['description'] . ")\n";
    }
}

$conn->close();

echo "\n=== PROPOSED ROLE-BASED PERMISSIONS ===\n";
echo "Based on user types, here's what permissions each should have:\n\n";

echo "📋 EMPLOYEE (user_type_id = 1):\n";
echo "  - employees: SELECT, INSERT (own record)\n";
echo "  - dtr: SELECT, INSERT (own records)\n";
echo "  - leaves: SELECT, INSERT (own records)\n";
echo "  - department, job_position: SELECT\n\n";

echo "👥 HR/MANAGER (user_type_id = 2):\n";
echo "  - employees: SELECT, INSERT, UPDATE\n"; 
echo "  - All HR tables: SELECT, INSERT, UPDATE, DELETE\n";
echo "  - Reports: SELECT\n\n";

echo "⚙️ ADMIN (user_type_id = 3):\n";
echo "  - ALL tables: SELECT, INSERT, UPDATE, DELETE\n";
echo "  - System management: Full access\n\n";

echo "📊 READONLY (user_type_id = 4):\n";
echo "  - All tables: SELECT only\n";
echo "  - Reports and analytics: SELECT\n\n";
?>

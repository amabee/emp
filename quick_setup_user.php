<?php
// Quick setup for emp_app user
echo "Creating emp_app user and basic grants...\n";

$conn = new mysqli('localhost', 'root', 'root', 'emp');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Create user
echo "1. Creating user emp_app@localhost...\n";
$result = $conn->query("CREATE USER IF NOT EXISTS 'emp_app'@'localhost' IDENTIFIED BY 'emp123'");
if ($result) {
    echo "   ✅ User created successfully\n";
} else {
    echo "   ❌ Error: " . $conn->error . "\n";
}

// Basic employee permissions
echo "2. Granting basic permissions...\n";
$grants = [
    "GRANT SELECT, INSERT, UPDATE ON emp.employees TO 'emp_app'@'localhost'",
    "GRANT SELECT, INSERT ON emp.system_logs TO 'emp_app'@'localhost'",
    "GRANT SELECT ON emp.users TO 'emp_app'@'localhost'",
    "GRANT SELECT ON emp.department TO 'emp_app'@'localhost'",
    "GRANT SELECT ON emp.job_position TO 'emp_app'@'localhost'",
    "GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_app'@'localhost'"
];

foreach ($grants as $grant) {
    $result = $conn->query($grant);
    if ($result) {
        echo "   ✅ " . substr($grant, 6, 30) . "...\n";
    } else {
        echo "   ❌ Error: " . $conn->error . "\n";
    }
}

// Flush privileges
echo "3. Applying privileges...\n";
$result = $conn->query("FLUSH PRIVILEGES");
if ($result) {
    echo "   ✅ Privileges flushed\n";
} else {
    echo "   ❌ Error: " . $conn->error . "\n";
}

// Verify user creation
echo "\n4. Verifying user creation...\n";
$result = $conn->query("SELECT User, Host FROM mysql.user WHERE User = 'emp_app'");
if ($result->num_rows > 0) {
    echo "   ✅ emp_app user exists\n";
    
    // Show grants
    echo "\n5. Current grants for emp_app:\n";
    $result = $conn->query("SHOW GRANTS FOR 'emp_app'@'localhost'");
    while ($row = $result->fetch_array()) {
        echo "   " . $row[0] . "\n";
    }
} else {
    echo "   ❌ emp_app user not found\n";
}

$conn->close();
echo "\nDone! You can now check grants with:\n";
echo "SHOW GRANTS FOR 'emp_app'@'localhost';\n";
?>

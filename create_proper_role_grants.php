<?php
// Check what tables exist and create appropriate role-based grants
echo "Checking existing tables...\n";

$conn = new mysqli('localhost', 'root', 'root', 'emp');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get all tables in emp database
$result = $conn->query("SHOW TABLES FROM emp");
$existing_tables = [];
echo "📋 Existing tables:\n";
while ($row = $result->fetch_array()) {
    $table = $row[0];
    $existing_tables[] = $table;
    echo "   - {$table}\n";
}

echo "\n🔧 Creating role-based users with existing tables only...\n\n";

// Drop existing users first
$users_to_drop = ['emp_admin', 'emp_supervisor', 'emp_hr', 'emp_employee'];
foreach ($users_to_drop as $user) {
    $conn->query("DROP USER IF EXISTS '{$user}'@'localhost'");
}

// Helper function to grant on table if it exists
function grantIfExists($conn, $grant, $table, $user, $existing_tables) {
    if (in_array($table, $existing_tables)) {
        $sql = str_replace('TABLE_NAME', $table, $grant);
        $conn->query($sql);
        return true;
    }
    return false;
}

// 1. ADMIN USER (Full access to all tables)
echo "👑 Creating ADMIN user...\n";
$conn->query("CREATE USER 'emp_admin'@'localhost' IDENTIFIED BY 'admin123'");
$conn->query("GRANT ALL PRIVILEGES ON emp.* TO 'emp_admin'@'localhost'");
$conn->query("GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_admin'@'localhost'");
echo "   ✅ emp_admin: Full access to all tables\n";

// 2. SUPERVISOR USER (Management access)
echo "👥 Creating SUPERVISOR user...\n";
$conn->query("CREATE USER 'emp_supervisor'@'localhost' IDENTIFIED BY 'super123'");
$supervisor_tables = [
    'employees' => 'SELECT, INSERT, UPDATE, DELETE',
    'employee_allowances' => 'SELECT, INSERT, UPDATE, DELETE',
    'employee_deductions' => 'SELECT, INSERT, UPDATE, DELETE', 
    'leaves' => 'SELECT, INSERT, UPDATE, DELETE',
    'leave_balances' => 'SELECT, INSERT, UPDATE, DELETE',
    'payroll' => 'SELECT',
    'department' => 'SELECT',
    'job_position' => 'SELECT',
    'users' => 'SELECT',
    'system_logs' => 'SELECT, INSERT'
];

foreach ($supervisor_tables as $table => $perms) {
    if (in_array($table, $existing_tables)) {
        $conn->query("GRANT {$perms} ON emp.{$table} TO 'emp_supervisor'@'localhost'");
        echo "   ✅ {$table}: {$perms}\n";
    }
}
$conn->query("GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_supervisor'@'localhost'");

// 3. HR USER (HR functions)
echo "🏢 Creating HR user...\n";
$conn->query("CREATE USER 'emp_hr'@'localhost' IDENTIFIED BY 'hr123'");
$hr_tables = [
    'employees' => 'SELECT, INSERT, UPDATE',
    'employee_allowances' => 'SELECT, INSERT, UPDATE, DELETE',
    'employee_deductions' => 'SELECT, INSERT, UPDATE, DELETE',
    'leaves' => 'SELECT, INSERT, UPDATE, DELETE',
    'leave_balances' => 'SELECT, INSERT, UPDATE, DELETE',
    'payroll' => 'SELECT, INSERT, UPDATE',
    'allowance_types' => 'SELECT',
    'deduction_types' => 'SELECT',
    'department' => 'SELECT',
    'job_position' => 'SELECT',
    'users' => 'SELECT',
    'system_logs' => 'SELECT, INSERT'
];

foreach ($hr_tables as $table => $perms) {
    if (in_array($table, $existing_tables)) {
        $conn->query("GRANT {$perms} ON emp.{$table} TO 'emp_hr'@'localhost'");
        echo "   ✅ {$table}: {$perms}\n";
    }
}
$conn->query("GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_hr'@'localhost'");

// 4. EMPLOYEE USER (Limited access - SELECT + INSERT only)
echo "👤 Creating EMPLOYEE user...\n";
$conn->query("CREATE USER 'emp_employee'@'localhost' IDENTIFIED BY 'emp123'");
$employee_tables = [
    'employees' => 'SELECT',
    'employee_allowances' => 'SELECT', 
    'employee_deductions' => 'SELECT',
    'leaves' => 'SELECT, INSERT',
    'leave_balances' => 'SELECT',
    'payroll' => 'SELECT',
    'allowance_types' => 'SELECT',
    'deduction_types' => 'SELECT',
    'department' => 'SELECT',
    'job_position' => 'SELECT',
    'system_logs' => 'SELECT, INSERT'
];

foreach ($employee_tables as $table => $perms) {
    if (in_array($table, $existing_tables)) {
        $conn->query("GRANT {$perms} ON emp.{$table} TO 'emp_employee'@'localhost'");
        echo "   ✅ {$table}: {$perms}\n";
    }
}
$conn->query("GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_employee'@'localhost'");

// Flush privileges
echo "\n🔄 Applying privileges...\n";
$conn->query("FLUSH PRIVILEGES");

echo "\n=== ROLE-BASED PERMISSIONS SUMMARY ===\n\n";
echo "👤 EMPLOYEE (emp_employee):\n";
echo "   - SELECT only on most tables\n";
echo "   - INSERT on leaves (can request leave)\n";
echo "   - Cannot modify employee records\n\n";

echo "🏢 HR (emp_hr):\n";
echo "   - Manage employee allowances/deductions\n";
echo "   - Process leaves and payroll\n";
echo "   - UPDATE employees (not DELETE)\n\n";

echo "👥 SUPERVISOR (emp_supervisor):\n";  
echo "   - Full CRUD on employees\n";
echo "   - Manage all HR operations\n";
echo "   - View reports and analytics\n\n";

echo "👑 ADMIN (emp_admin):\n";
echo "   - Full database access\n";
echo "   - All privileges on all tables\n\n";

echo "=== TEST GRANTS ===\n";
echo "To check grants for employee user:\n";
echo "SHOW GRANTS FOR 'emp_employee'@'localhost';\n\n";

echo "To check specific table permissions:\n";
echo "SELECT TABLE_NAME, PRIVILEGE_TYPE FROM information_schema.TABLE_PRIVILEGES\n";
echo "WHERE GRANTEE = \"'emp_employee'@'localhost'\" ORDER BY TABLE_NAME;\n";

$conn->close();
?>

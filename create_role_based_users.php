<?php
// Create role-based database users with proper permissions
echo "Creating role-based database users...\n\n";

$conn = new mysqli('localhost', 'root', 'root', 'emp');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Drop existing users first (clean slate)
echo "🧹 Cleaning up existing users...\n";
$users_to_drop = ['emp_admin', 'emp_supervisor', 'emp_hr', 'emp_employee'];
foreach ($users_to_drop as $user) {
    $conn->query("DROP USER IF EXISTS '{$user}'@'localhost'");
    echo "   Dropped {$user} (if existed)\n";
}

echo "\n";

// 1. ADMIN USER (type_name = Admin, user_type_id = 1)
echo "👑 Creating ADMIN user (Full Access)...\n";
$conn->query("CREATE USER 'emp_admin'@'localhost' IDENTIFIED BY 'admin123'");
$conn->query("GRANT ALL PRIVILEGES ON emp.* TO 'emp_admin'@'localhost'");
$conn->query("GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_admin'@'localhost'");
echo "   ✅ emp_admin: Full database access\n";

// 2. SUPERVISOR USER (type_name = Supervisor, user_type_id = 2) 
echo "👥 Creating SUPERVISOR user (Management Access)...\n";
$conn->query("CREATE USER 'emp_supervisor'@'localhost' IDENTIFIED BY 'super123'");
// Can manage employees, view reports, manage schedules
$conn->query("GRANT SELECT, INSERT, UPDATE, DELETE ON emp.employees TO 'emp_supervisor'@'localhost'");
$conn->query("GRANT SELECT, INSERT, UPDATE, DELETE ON emp.dtr TO 'emp_supervisor'@'localhost'");
$conn->query("GRANT SELECT, INSERT, UPDATE, DELETE ON emp.leaves TO 'emp_supervisor'@'localhost'");
$conn->query("GRANT SELECT, INSERT, UPDATE, DELETE ON emp.employee_allowances TO 'emp_supervisor'@'localhost'");
$conn->query("GRANT SELECT, INSERT, UPDATE, DELETE ON emp.employee_deductions TO 'emp_supervisor'@'localhost'");
$conn->query("GRANT SELECT ON emp.payroll TO 'emp_supervisor'@'localhost'");
$conn->query("GRANT SELECT ON emp.department TO 'emp_supervisor'@'localhost'");
$conn->query("GRANT SELECT ON emp.job_position TO 'emp_supervisor'@'localhost'");
$conn->query("GRANT SELECT ON emp.users TO 'emp_supervisor'@'localhost'");
$conn->query("GRANT SELECT, INSERT ON emp.system_logs TO 'emp_supervisor'@'localhost'");
$conn->query("GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_supervisor'@'localhost'");
echo "   ✅ emp_supervisor: Employee management + reports access\n";

// 3. HR USER (type_name = HR, user_type_id = 3)
echo "🏢 Creating HR user (HR Management Access)...\n"; 
$conn->query("CREATE USER 'emp_hr'@'localhost' IDENTIFIED BY 'hr123'");
// Can manage HR functions, payroll, benefits
$conn->query("GRANT SELECT, INSERT, UPDATE ON emp.employees TO 'emp_hr'@'localhost'");
$conn->query("GRANT SELECT, INSERT, UPDATE, DELETE ON emp.leaves TO 'emp_hr'@'localhost'");
$conn->query("GRANT SELECT, INSERT, UPDATE, DELETE ON emp.leave_balances TO 'emp_hr'@'localhost'");
$conn->query("GRANT SELECT, INSERT, UPDATE, DELETE ON emp.employee_allowances TO 'emp_hr'@'localhost'");
$conn->query("GRANT SELECT, INSERT, UPDATE, DELETE ON emp.employee_deductions TO 'emp_hr'@'localhost'");
$conn->query("GRANT SELECT, INSERT, UPDATE ON emp.payroll TO 'emp_hr'@'localhost'");
$conn->query("GRANT SELECT ON emp.dtr TO 'emp_hr'@'localhost'");
$conn->query("GRANT SELECT ON emp.department TO 'emp_hr'@'localhost'");
$conn->query("GRANT SELECT ON emp.job_position TO 'emp_hr'@'localhost'");
$conn->query("GRANT SELECT ON emp.users TO 'emp_hr'@'localhost'");
$conn->query("GRANT SELECT, INSERT ON emp.system_logs TO 'emp_hr'@'localhost'");
$conn->query("GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_hr'@'localhost'");
echo "   ✅ emp_hr: HR functions + payroll access\n";

// 4. EMPLOYEE USER (type_name = Employee, user_type_id = 4)
echo "👤 Creating EMPLOYEE user (Limited Access)...\n";
$conn->query("CREATE USER 'emp_employee'@'localhost' IDENTIFIED BY 'emp123'");
// Can only view own data and basic operations
$conn->query("GRANT SELECT ON emp.employees TO 'emp_employee'@'localhost'");
$conn->query("GRANT SELECT, INSERT ON emp.dtr TO 'emp_employee'@'localhost'");
$conn->query("GRANT SELECT, INSERT ON emp.leaves TO 'emp_employee'@'localhost'");
$conn->query("GRANT SELECT ON emp.leave_balances TO 'emp_employee'@'localhost'");
$conn->query("GRANT SELECT ON emp.employee_allowances TO 'emp_employee'@'localhost'");
$conn->query("GRANT SELECT ON emp.employee_deductions TO 'emp_employee'@'localhost'");
$conn->query("GRANT SELECT ON emp.payroll TO 'emp_employee'@'localhost'");
$conn->query("GRANT SELECT ON emp.department TO 'emp_employee'@'localhost'");
$conn->query("GRANT SELECT ON emp.job_position TO 'emp_employee'@'localhost'");
$conn->query("GRANT SELECT, INSERT ON emp.system_logs TO 'emp_employee'@'localhost'");
$conn->query("GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_employee'@'localhost'");
echo "   ✅ emp_employee: SELECT + INSERT only (own data)\n";

// Flush privileges
echo "\n🔄 Applying all privileges...\n";
$conn->query("FLUSH PRIVILEGES");
echo "   ✅ All privileges applied\n";

// Show summary
echo "\n=== ROLE-BASED DATABASE USERS CREATED ===\n\n";

$users = [
    'emp_admin' => 'Admin (Full Access)',
    'emp_supervisor' => 'Supervisor (Management)',  
    'emp_hr' => 'HR (HR Functions)',
    'emp_employee' => 'Employee (Limited)'
];

foreach ($users as $user => $description) {
    echo "🔐 {$user}@localhost - {$description}\n";
    $result = $conn->query("SHOW GRANTS FOR '{$user}'@'localhost'");
    $grants = [];
    while ($row = $result->fetch_array()) {
        $grants[] = $row[0];
    }
    echo "   Grants: " . count($grants) . " permissions\n";
}

echo "\n=== CONNECTION CREDENTIALS ===\n";
echo "Admin:      mysql -u emp_admin -p      (password: admin123)\n";
echo "Supervisor: mysql -u emp_supervisor -p (password: super123)\n";
echo "HR:         mysql -u emp_hr -p         (password: hr123)\n";
echo "Employee:   mysql -u emp_employee -p   (password: emp123)\n";

echo "\n=== PERMISSION SUMMARY ===\n";
echo "👤 EMPLOYEE: SELECT + INSERT only (own records)\n";
echo "🏢 HR: HR functions + payroll management\n";
echo "👥 SUPERVISOR: Employee management + reports\n";
echo "👑 ADMIN: Full database access\n";

$conn->close();
?>

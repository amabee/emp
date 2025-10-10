# Database Triggers Documentation
## Employee Management System

### 📋 Table of Contents
1. [Overview](#overview)
2. [Trigger Architecture](#trigger-architecture)
3. [Installation Guide](#installation-guide)
4. [Database Permissions & Grants](#database-permissions--grants)
5. [Integration Instructions](#integration-instructions)
6. [Trigger Specifications](#trigger-specifications)
7. [Usage Examples](#usage-examples)
8. [Testing & Validation](#testing--validation)
9. [Troubleshooting](#troubleshooting)
10. [Best Practices](#best-practices)

---

## Overview

The Employee Management System implements **4 database triggers** to provide automatic data validation and comprehensive audit logging for all employee operations. These triggers ensure data integrity, maintain audit trails, and enforce business rules at the database level.

### 🎯 **Key Benefits:**
- **Data Integrity**: Automatic validation prevents invalid data entry
- **Audit Trail**: Complete logging of all employee operations
- **Security**: Server-side validation that cannot be bypassed
- **Compliance**: Detailed logs for regulatory requirements
- **Automatic Defaults**: Sets default values when fields are missing

---

## Trigger Architecture

### **4 Core Triggers:**

| Trigger Name | Type | Purpose | When It Fires |
|-------------|------|---------|---------------|
| `before_employee_insert_validation` | BEFORE INSERT | Validates data before creating new employees | Before any INSERT into employees table |
| `before_employee_update_validation` | BEFORE UPDATE | Validates data before updating employees | Before any UPDATE on employees table |
| `after_employee_insert_logging` | AFTER INSERT | Logs new employee creation | After successful INSERT into employees table |
| `after_employee_update_logging` | AFTER UPDATE | Logs employee updates | After successful UPDATE on employees table |

### **Database Dependencies:**
- **Primary Table**: `employees`
- **Logging Table**: `system_logs`
- **Reference Tables**: `users`, `department`, `job_position`

---

## Database Permissions & Grants

### **Required Permissions for Triggers**

Database triggers require specific permissions to function properly. Here are the essential grants needed:

#### **For Development Environment (Local):**
```sql
-- Grant full privileges for development (use with caution)
GRANT ALL PRIVILEGES ON emp.* TO 'emp_user'@'localhost' IDENTIFIED BY 'secure_password';
FLUSH PRIVILEGES;
```

#### **For Production Environment (Recommended):**
```sql
-- Create dedicated database user for the application
CREATE USER 'emp_app'@'localhost' IDENTIFIED BY 'your_secure_password_here';
CREATE USER 'emp_app'@'%' IDENTIFIED BY 'your_secure_password_here'; -- For remote access if needed

-- Grant specific permissions for the application user
GRANT SELECT, INSERT, UPDATE ON emp.employees TO 'emp_app'@'localhost';
GRANT SELECT, INSERT ON emp.system_logs TO 'emp_app'@'localhost';
GRANT SELECT ON emp.users TO 'emp_app'@'localhost';
GRANT SELECT ON emp.department TO 'emp_app'@'localhost';
GRANT SELECT ON emp.job_position TO 'emp_app'@'localhost';

-- Additional permissions for other tables your app uses
GRANT SELECT, INSERT, UPDATE, DELETE ON emp.leave_balances TO 'emp_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON emp.dtr TO 'emp_app'@'localhost';
GRANT SELECT ON emp.allowance_types TO 'emp_app'@'localhost';
GRANT SELECT ON emp.deduction_types TO 'emp_app'@'localhost';

-- Grant ability to set session variables (needed for trigger logging)
GRANT SUPER ON *.* TO 'emp_app'@'localhost';
-- OR alternatively, grant specific session variable privileges (MySQL 8.0+)
GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_app'@'localhost';

FLUSH PRIVILEGES;
```

#### **For Trigger Installation (Admin User):**
```sql
-- The user installing triggers needs these privileges
GRANT CREATE ON emp.* TO 'admin_user'@'localhost';
GRANT ALTER ON emp.* TO 'admin_user'@'localhost';
GRANT TRIGGER ON emp.* TO 'admin_user'@'localhost';
GRANT DROP ON emp.* TO 'admin_user'@'localhost'; -- For dropping existing triggers
FLUSH PRIVILEGES;
```

### **Security Best Practices for Grants:**

#### **1. Principle of Least Privilege**
```sql
-- ✅ GOOD: Grant only what's needed
GRANT SELECT, INSERT, UPDATE ON emp.employees TO 'emp_app'@'localhost';

-- ❌ BAD: Don't grant unnecessary privileges
-- GRANT ALL PRIVILEGES ON *.* TO 'emp_app'@'localhost'; -- Too permissive!
```

#### **2. Separate Users for Different Purposes**
```sql
-- Application user (limited permissions)
CREATE USER 'emp_app'@'localhost' IDENTIFIED BY 'app_password';
GRANT SELECT, INSERT, UPDATE ON emp.employees TO 'emp_app'@'localhost';

-- Admin user (full permissions for maintenance)
CREATE USER 'emp_admin'@'localhost' IDENTIFIED BY 'admin_password';
GRANT ALL PRIVILEGES ON emp.* TO 'emp_admin'@'localhost';

-- Read-only user (for reports/analytics)
CREATE USER 'emp_readonly'@'localhost' IDENTIFIED BY 'readonly_password';
GRANT SELECT ON emp.* TO 'emp_readonly'@'localhost';
```

#### **3. Network Security**
```sql
-- ✅ GOOD: Restrict by host
CREATE USER 'emp_app'@'localhost';        -- Local only
CREATE USER 'emp_app'@'192.168.1.%';     -- Specific network
CREATE USER 'emp_app'@'app-server.com';  -- Specific host

-- ❌ BAD: Wildcard access (use only if absolutely necessary)
-- CREATE USER 'emp_app'@'%';  -- Any host - security risk!
```

### **Grant Verification Commands:**

```sql
-- Check current user privileges
SHOW GRANTS FOR 'emp_app'@'localhost';

-- Check all users and their hosts
SELECT User, Host FROM mysql.user WHERE User LIKE 'emp%';

-- Check table-level permissions
SELECT * FROM information_schema.TABLE_PRIVILEGES 
WHERE GRANTEE LIKE '%emp_app%';

-- Check if triggers can be created
SELECT TRIGGER_SCHEMA, TRIGGER_NAME, DEFINER 
FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'emp';
```

### **Common Permission Issues & Solutions:**

#### **Issue 1: "Access denied for user"**
```sql
-- Solution: Grant required permissions
GRANT SELECT, INSERT, UPDATE ON emp.employees TO 'emp_app'@'localhost';
FLUSH PRIVILEGES;
```

#### **Issue 2: "Trigger does not have privileges to access tables"**
```sql
-- Solution: Ensure trigger definer has access to all referenced tables
GRANT SELECT ON emp.users TO 'emp_app'@'localhost';
GRANT SELECT ON emp.department TO 'emp_app'@'localhost';
GRANT SELECT ON emp.job_position TO 'emp_app'@'localhost';
FLUSH PRIVILEGES;
```

#### **Issue 3: "Cannot set @current_user_id variable"**
```sql
-- Solution: Grant session variable privileges
GRANT SUPER ON *.* TO 'emp_app'@'localhost';
-- OR for MySQL 8.0+
GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_app'@'localhost';
FLUSH PRIVILEGES;
```

### **Grant Setup Script for Production:**

Create this as `setup_grants.sql`:
```sql
-- =============================================
-- PRODUCTION GRANTS SETUP SCRIPT
-- Run this as root/admin user
-- =============================================

-- Create application user
CREATE USER IF NOT EXISTS 'emp_app'@'localhost' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';

-- Core application permissions
GRANT SELECT, INSERT, UPDATE ON emp.employees TO 'emp_app'@'localhost';
GRANT SELECT, INSERT ON emp.system_logs TO 'emp_app'@'localhost';
GRANT SELECT ON emp.users TO 'emp_app'@'localhost';
GRANT SELECT ON emp.department TO 'emp_app'@'localhost';
GRANT SELECT ON emp.job_position TO 'emp_app'@'localhost';

-- Additional table permissions (adjust as needed)
GRANT SELECT, INSERT, UPDATE, DELETE ON emp.leave_balances TO 'emp_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON emp.dtr TO 'emp_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON emp.employee_allowances TO 'emp_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON emp.employee_deductions TO 'emp_app'@'localhost';
GRANT SELECT ON emp.allowance_types TO 'emp_app'@'localhost';
GRANT SELECT ON emp.deduction_types TO 'emp_app'@'localhost';

-- Session variable permissions (needed for trigger logging)
GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_app'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify grants
SHOW GRANTS FOR 'emp_app'@'localhost';
```

### **Update Database Configuration:**

Update your `shared/config.php` to use the new user:
```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'emp');
define('DB_USER', 'emp_app');      // Use application user
define('DB_PASS', 'your_password'); // Use secure password
```

---

## Installation Guide

### **Method 1: Using PHP Installation Script (Recommended)**

1. **Run the installation script:**
   ```bash
   php create_triggers.php
   ```

2. **Expected output:**
   ```
   🔧 Installing Employee Validation and Logging Triggers...
   ✅ Dropped trigger: DROP TRIGGER IF EXISTS before_employee_insert_validation
   ✅ Created: before_employee_insert_validation
   ✅ Created: before_employee_update_validation
   ✅ Created: after_employee_insert_logging
   ✅ Created: after_employee_update_logging
   🎉 Trigger installation complete!
   ```

### **Method 2: Manual SQL Installation**

1. **Open phpMyAdmin or MySQL client**
2. **Select your `emp` database**
3. **Copy and paste the trigger definitions from `database_triggers.sql`**
4. **Execute the SQL statements**

### **Verification:**

Run the test script to verify installation:
```bash
php final_trigger_test.php
```

---

## Integration Instructions

### **Step 1: Set User Context for Logging**

Before performing any employee operations, set the current user ID for audit logging:

```php
// In your PHP application (add this to employee operation functions)
$pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");
```

### **Step 2: Update Existing Employee Management Files**

#### **In `ajax/add_employee.php`:**
```php
<?php
require_once '../shared/config.php';

// Set current user for trigger logging
$pdo = getDBConnection();
$pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");

try {
    // Your existing employee insertion code
    $stmt = $pdo->prepare("INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, gender, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$userId, $firstName, $lastName, $email, $salary, $gender, $contact]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Employee added successfully']);
    }
    
} catch (Exception $e) {
    // Trigger validation errors will be caught here with descriptive messages
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
```

#### **In `ajax/update_employee.php`:**
```php
<?php
require_once '../shared/config.php';

// Set current user for trigger logging
$pdo = getDBConnection();
$pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");

try {
    // Your existing employee update code
    $stmt = $pdo->prepare("UPDATE employees SET first_name = ?, last_name = ?, email = ?, basic_salary = ? WHERE employee_id = ?");
    $result = $stmt->execute([$firstName, $lastName, $email, $salary, $employeeId]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
    }
    
} catch (Exception $e) {
    // Trigger validation errors will be caught here
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
```

### **Step 3: Using the Helper Class (Optional)**

For more advanced integration, use the `DatabaseTriggersHelper` class:

```php
require_once 'libs/DatabaseTriggersHelper.php';

$pdo = getDBConnection();
$triggersHelper = new DatabaseTriggersHelper($pdo);

try {
    // Create employee with automatic validation and logging
    $employeeId = $triggersHelper->createEmployee([
        'user_id' => $userId,
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'basic_salary' => $_POST['basic_salary'],
        'gender' => $_POST['gender'],
        'contact_number' => $_POST['contact_number']
    ]);
    
    echo json_encode(['success' => true, 'employee_id' => $employeeId]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
```

---

## Trigger Specifications

### **1. before_employee_insert_validation**

**Purpose**: Validates employee data before insertion

**Validation Rules**:
- ✅ **First Name**: Cannot be NULL or empty
- ✅ **Last Name**: Cannot be NULL or empty  
- ✅ **Email**: Cannot be NULL, empty, or invalid format
- ✅ **Basic Salary**: Must be greater than zero
- ✅ **Auto-Defaults**: Sets `date_hired` to current date if NULL
- ✅ **Auto-Defaults**: Sets `employment_status` to 1 (active) if NULL

**Error Messages**:
```
"First name cannot be empty"
"Last name cannot be empty"
"Email cannot be empty"
"Invalid email format"
"Basic salary must be greater than zero"
```

### **2. before_employee_update_validation**

**Purpose**: Validates employee data before updates

**Validation Rules**: Same as insert validation (except auto-defaults)

### **3. after_employee_insert_logging**

**Purpose**: Logs new employee creation to `system_logs` table

**Log Entry Format**:
```
Action: CREATE
Description: "New Employee Created: ID 16 - John Doe (Email: john@example.com, Salary: 25000.00)"
User ID: [Current session user]
Timestamp: [Current datetime]
IP Address: 127.0.0.1
```

### **4. after_employee_update_logging**

**Purpose**: Logs employee updates to `system_logs` table

**Log Entry Format**:
```
Action: UPDATE  
Description: "Employee Updated: ID 16 - John Doe (Old Salary: 25000.00, New Salary: 30000.00)"
User ID: [Current session user]
Timestamp: [Current datetime]
IP Address: 127.0.0.1
```

---

## Usage Examples

### **Example 1: Basic Employee Creation**

```php
// Set user context
$pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");

// Insert employee (triggers fire automatically)
$stmt = $pdo->prepare("
    INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, gender, contact_number) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

try {
    $stmt->execute([1, 'John', 'Doe', 'john@example.com', 25000, 'male', '09123456789']);
    echo "Employee created successfully!";
} catch (Exception $e) {
    echo "Validation error: " . $e->getMessage();
}
```

### **Example 2: Employee Update with Logging**

```php
// Set user context
$pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");

// Update employee (triggers fire automatically)
$stmt = $pdo->prepare("UPDATE employees SET basic_salary = ?, first_name = ? WHERE employee_id = ?");

try {
    $stmt->execute([30000, 'Johnny', 16]);
    echo "Employee updated successfully!";
} catch (Exception $e) {
    echo "Validation error: " . $e->getMessage();
}
```

### **Example 3: Error Handling**

```php
try {
    $pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");
    
    // This will fail validation
    $stmt = $pdo->prepare("INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, gender, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([1, '', 'Doe', 'invalid-email', -1000, 'male', '09123456789']);
    
} catch (Exception $e) {
    // Will catch: "First name cannot be empty"
    echo "Error: " . $e->getMessage();
}
```

---

## Testing & Validation

### **Run Comprehensive Tests**

```bash
# Run the complete trigger test suite
php final_trigger_test.php
```

**Expected Output**:
```
🎯 Final Comprehensive Trigger Test
==================================

1. VALIDATION TESTS (These should all FAIL):
-------------------------------------------
✅ PASSED - SQLSTATE[45000]: <<Unknown error>>: 1644 First name cannot be empty
✅ PASSED - SQLSTATE[45000]: <<Unknown error>>: 1644 Last name cannot be empty
✅ PASSED - SQLSTATE[45000]: <<Unknown error>>: 1644 Invalid email format
✅ PASSED - SQLSTATE[45000]: <<Unknown error>>: 1644 Basic salary must be greater than zero

2. SUCCESSFUL INSERT TEST:
--------------------------
✅ SUCCESS - Employee created with ID: 16

🎉 TRIGGER TEST SUMMARY:
========================
✅ before_employee_insert_validation - Working perfectly!
✅ before_employee_update_validation - Working perfectly!
✅ after_employee_insert_logging - Working perfectly!
✅ after_employee_update_logging - Working perfectly!
```

### **Manual Testing Commands**

```sql
-- Test 1: Invalid data (should fail)
SET @current_user_id = 1;
INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, gender, contact_number) 
VALUES (1, '', 'Test', 'invalid-email', -1000, 'male', '09123456789');

-- Test 2: Valid data (should succeed)
SET @current_user_id = 1;
INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, gender, contact_number) 
VALUES (1, 'John', 'Doe', 'john@example.com', 25000, 'male', '09123456789');

-- Check logs
SELECT * FROM system_logs WHERE full_description LIKE '%Employee%' ORDER BY date_performed DESC LIMIT 5;
```

---

## Troubleshooting

### **Common Issues & Solutions**

#### **Issue 1: "Trigger does not exist"**
```
Solution: Run the installation script again
Command: php create_triggers.php
```

#### **Issue 2: "Column 'contact_number' doesn't have a default value"**
```
Solution: Ensure all required fields are provided
Required: user_id, first_name, last_name, email, basic_salary, gender, contact_number
```

#### **Issue 3: "Unknown column 'address' in field list"**
```
Solution: Use correct column names. The employees table doesn't have an 'address' column.
```

#### **Issue 4: Logs not appearing**
```
Solution: Set the user context before operations
Code: $pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");
```

#### **Issue 5: "Access denied for user 'emp_user'@'localhost'"**
```
Solution: Check database grants and credentials
1. Verify user exists: SELECT User FROM mysql.user WHERE User = 'emp_user';
2. Check grants: SHOW GRANTS FOR 'emp_user'@'localhost';
3. Update shared/config.php with correct credentials
4. Run grant setup: mysql -u root -p < setup_grants.sql
```

#### **Issue 6: "Cannot set session variable @current_user_id"**
```
Solution: Grant session variable permissions
For MySQL 8.0+: GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_app'@'localhost';
For older versions: GRANT SUPER ON *.* TO 'emp_app'@'localhost';
Then: FLUSH PRIVILEGES;
```

#### **Issue 7: "Trigger does not have privileges to access table"**
```
Solution: Ensure trigger definer has access to all referenced tables
GRANT SELECT ON emp.users TO 'emp_app'@'localhost';
GRANT SELECT ON emp.department TO 'emp_app'@'localhost';
GRANT SELECT ON emp.job_position TO 'emp_app'@'localhost';
FLUSH PRIVILEGES;
```

#### **Issue 8: "Table 'emp.employees' doesn't exist"**
```
Solution: Check database name and table existence
1. Verify database: SHOW DATABASES LIKE 'emp';
2. Check tables: SHOW TABLES FROM emp;
3. Update DB_NAME in shared/config.php if needed
```

### **Debugging Commands**

```sql
-- Check if triggers exist
SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE 
FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = DATABASE() AND EVENT_OBJECT_TABLE = 'employees';

-- Check recent logs
SELECT * FROM system_logs ORDER BY date_performed DESC LIMIT 10;

-- Test trigger manually
SET @current_user_id = 1;
INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, gender, contact_number) 
VALUES (1, 'Test', 'User', 'test@example.com', 15000, 'male', '09123456789');
```

---

## Best Practices

### **1. Always Set User Context**
```php
// ALWAYS do this before employee operations
$pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");
```

### **2. Handle Validation Errors Gracefully**
```php
try {
    // Employee operation
} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log("Employee operation failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Please check your input and try again.']);
}
```

### **3. Use Transactions for Complex Operations**
```php
$pdo->beginTransaction();
try {
    $pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");
    
    // Multiple employee operations
    // Triggers will fire for each operation
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollback();
    throw $e;
}
```

### **4. Regular Log Maintenance**
```sql
-- Clean old logs (run periodically)
DELETE FROM system_logs WHERE date_performed < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### **5. Monitor Trigger Performance**
```sql
-- Check trigger execution times
SHOW PROFILE FOR QUERY 1;
```

---

## File Structure

```
emp/
├── create_triggers.php              # ✅ Main installation script
├── final_trigger_test.php          # ✅ Comprehensive test suite
├── database_triggers.sql           # Original trigger definitions
├── simple_triggers.sql             # Simplified versions
├── libs/
│   └── DatabaseTriggersHelper.php  # PHP helper class
├── test_triggers.php               # Web-based test interface
└── TRIGGER_DOCUMENTATION.md        # This documentation
```

---

## Conclusion

The database triggers provide a robust foundation for data validation and audit logging in your Employee Management System. They automatically:

- ✅ **Validate** all employee data before insertion/updates
- ✅ **Log** all operations with full audit trails  
- ✅ **Enforce** business rules at the database level
- ✅ **Prevent** invalid data entry
- ✅ **Maintain** data integrity automatically

**Next Steps:**
1. Install triggers using `php create_triggers.php`
2. Test functionality with `php final_trigger_test.php`
3. Integrate user context setting in your employee management files
4. Monitor logs in the `system_logs` table
5. Set up regular log maintenance procedures

🚀 **Your triggers are ready for production use!**

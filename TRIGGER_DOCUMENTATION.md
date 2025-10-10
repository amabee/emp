# Database Triggers Documentation
## Employee Management System

### 📋 Table of Contents
1. [Overview](#overview)
2. [Trigger Architecture](#trigger-architecture)
3. [Installation Guide](#installation-guide)
4. [Integration Instructions](#integration-instructions)
5. [Trigger Specifications](#trigger-specifications)
6. [Usage Examples](#usage-examples)
7. [Testing & Validation](#testing--validation)
8. [Troubleshooting](#troubleshooting)
9. [Best Practices](#best-practices)

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

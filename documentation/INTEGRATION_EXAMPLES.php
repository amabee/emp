<?php
/**
 * INTEGRATION EXAMPLES - Database Triggers
 * Employee Management System
 * 
 * This file shows practical examples of how to integrate
 * the database triggers into your existing employee management code.
 */

// =============================================
// EXAMPLE 1: Updated add_employee.php
// =============================================
?>

<!-- Example: ajax/add_employee.php -->
<?php
require_once '../shared/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// ✅ IMPORTANT: Set current user for trigger logging
$pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");

try {
    // Get form data
    $userId = $_POST['user_id'];
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $salary = floatval($_POST['basic_salary']);
    $gender = $_POST['gender'];
    $contact = trim($_POST['contact_number']);
    $departmentId = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    $positionId = !empty($_POST['position_id']) ? intval($_POST['position_id']) : null;
    
    // Insert employee - triggers will fire automatically
    $stmt = $pdo->prepare("
        INSERT INTO employees (
            user_id, first_name, last_name, email, basic_salary, 
            gender, contact_number, department_id, position_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $userId, $firstName, $lastName, $email, $salary, 
        $gender, $contact, $departmentId, $positionId
    ]);
    
    if ($result) {
        $employeeId = $pdo->lastInsertId();
        echo json_encode([
            'success' => true, 
            'message' => 'Employee added successfully',
            'employee_id' => $employeeId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add employee']);
    }
    
} catch (Exception $e) {
    // ✅ Trigger validation errors will be caught here with descriptive messages
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
// =============================================
// EXAMPLE 2: Updated update_employee.php  
// =============================================
?>

<!-- Example: ajax/update_employee.php -->
<?php
require_once '../shared/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// ✅ IMPORTANT: Set current user for trigger logging
$pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");

try {
    $employeeId = intval($_POST['employee_id']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $salary = floatval($_POST['basic_salary']);
    $gender = $_POST['gender'];
    $contact = trim($_POST['contact_number']);
    $departmentId = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    $positionId = !empty($_POST['position_id']) ? intval($_POST['position_id']) : null;
    
    // Update employee - triggers will fire automatically  
    $stmt = $pdo->prepare("
        UPDATE employees SET 
            first_name = ?, last_name = ?, email = ?, basic_salary = ?,
            gender = ?, contact_number = ?, department_id = ?, position_id = ?
        WHERE employee_id = ?
    ");
    
    $result = $stmt->execute([
        $firstName, $lastName, $email, $salary,
        $gender, $contact, $departmentId, $positionId, $employeeId
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update employee']);
    }
    
} catch (Exception $e) {
    // ✅ Trigger validation errors will be caught here
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
// =============================================
// EXAMPLE 3: Using the Helper Class
// =============================================

require_once 'libs/DatabaseTriggersHelper.php';

function addEmployeeWithHelper($employeeData) {
    $pdo = getDBConnection();
    $helper = new DatabaseTriggersHelper($pdo);
    
    try {
        $employeeId = $helper->createEmployee([
            'user_id' => $employeeData['user_id'],
            'first_name' => $employeeData['first_name'],
            'last_name' => $employeeData['last_name'],
            'email' => $employeeData['email'],
            'basic_salary' => $employeeData['basic_salary'],
            'gender' => $employeeData['gender'],
            'contact_number' => $employeeData['contact_number'],
            'department_id' => $employeeData['department_id'] ?? null,
            'position_id' => $employeeData['position_id'] ?? null
        ]);
        
        return ['success' => true, 'employee_id' => $employeeId];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// =============================================
// EXAMPLE 4: Bulk Operations with Triggers
// =============================================

function importEmployeesBulk($employeesData) {
    $pdo = getDBConnection();
    
    // Set user context once for all operations
    $pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");
    
    $pdo->beginTransaction();
    $results = [];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO employees (
                user_id, first_name, last_name, email, basic_salary, 
                gender, contact_number, department_id, position_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($employeesData as $index => $employee) {
            try {
                $stmt->execute([
                    $employee['user_id'], $employee['first_name'], $employee['last_name'],
                    $employee['email'], $employee['basic_salary'], $employee['gender'],
                    $employee['contact_number'], $employee['department_id'], $employee['position_id']
                ]);
                
                $results[] = [
                    'index' => $index,
                    'success' => true,
                    'employee_id' => $pdo->lastInsertId()
                ];
                
            } catch (Exception $e) {
                $results[] = [
                    'index' => $index,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        $pdo->commit();
        return $results;
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

// =============================================
// EXAMPLE 5: JavaScript Frontend Integration
// =============================================
?>

<script>
// Updated JavaScript for handling trigger validation errors

function addEmployee(formData) {
    fetch('ajax/add_employee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage('Employee added successfully!');
            refreshEmployeeList();
            closeModal();
        } else {
            // ✅ Trigger validation errors will appear here with descriptive messages
            showErrorMessage(data.message);
            
            // Handle specific validation errors
            if (data.message.includes('First name cannot be empty')) {
                highlightField('first_name');
            } else if (data.message.includes('Invalid email format')) {
                highlightField('email');
            } else if (data.message.includes('Basic salary must be greater than zero')) {
                highlightField('basic_salary');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('An unexpected error occurred');
    });
}

function showErrorMessage(message) {
    // Display user-friendly error message
    const errorDiv = document.getElementById('error-message');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function highlightField(fieldName) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (field) {
        field.classList.add('error-field');
        field.focus();
    }
}
</script>

<?php
// =============================================
// EXAMPLE 6: Viewing Audit Logs
// =============================================

function getEmployeeAuditLogs($employeeId = null, $limit = 50) {
    $pdo = getDBConnection();
    
    $sql = "
        SELECT sl.*, u.username, u.full_name, e.first_name, e.last_name
        FROM system_logs sl
        LEFT JOIN users u ON sl.user_id = u.user_id
        LEFT JOIN employees e ON sl.full_description LIKE CONCAT('%ID ', e.employee_id, '%')
        WHERE sl.full_description LIKE '%Employee%'
    ";
    
    $params = [];
    
    if ($employeeId) {
        $sql .= " AND sl.full_description LIKE ?";
        $params[] = "%ID $employeeId%";
    }
    
    $sql .= " ORDER BY sl.date_performed DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Usage: Get recent employee operations
$recentLogs = getEmployeeAuditLogs();

// Usage: Get logs for specific employee
$employeeLogs = getEmployeeAuditLogs(16); // Employee ID 16

// =============================================
// EXAMPLE 7: Error Handling Best Practices
// =============================================

function handleTriggerError($exception) {
    $errorMessage = $exception->getMessage();
    
    // Map technical errors to user-friendly messages
    $userFriendlyMessages = [
        'First name cannot be empty' => 'Please enter the employee\'s first name.',
        'Last name cannot be empty' => 'Please enter the employee\'s last name.',
        'Email cannot be empty' => 'Please enter a valid email address.',
        'Invalid email format' => 'Please enter a valid email address format (e.g., user@domain.com).',
        'Basic salary must be greater than zero' => 'Please enter a valid salary amount.'
    ];
    
    // Check if we have a user-friendly message
    foreach ($userFriendlyMessages as $technical => $friendly) {
        if (strpos($errorMessage, $technical) !== false) {
            return $friendly;
        }
    }
    
    // Return generic message for unknown errors
    return 'Please check your input and try again.';
}

// Usage in your error handling
try {
    // Employee operation
} catch (Exception $e) {
    $userMessage = handleTriggerError($e);
    echo json_encode(['success' => false, 'message' => $userMessage]);
    
    // Log technical details for debugging
    error_log("Employee operation failed: " . $e->getMessage());
}

?>

<!-- 
=============================================
INTEGRATION CHECKLIST
=============================================

✅ 1. Install triggers: php create_triggers.php
✅ 2. Test triggers: php final_trigger_test.php
✅ 3. Add "$pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");" to:
   - ajax/add_employee.php
   - ajax/update_employee.php
   - Any custom employee management scripts
✅ 4. Update error handling to catch validation exceptions
✅ 5. Update frontend JavaScript to handle validation messages
✅ 6. Test integration with real data
✅ 7. Monitor system_logs table for audit trail
✅ 8. Set up log maintenance procedures

=============================================
VALIDATION MESSAGES YOUR USERS WILL SEE
=============================================

❌ "First name cannot be empty"
❌ "Last name cannot be empty" 
❌ "Email cannot be empty"
❌ "Invalid email format"
❌ "Basic salary must be greater than zero"

These messages come directly from the triggers and will be
caught as exceptions in your PHP code.

=============================================
-->

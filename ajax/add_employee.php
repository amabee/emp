<?php
header('Content-Type: application/json');
session_start();
// Buffer output so we can ensure only JSON is returned
if (!ob_get_level()) ob_start();

function send_json($payload) {
    if (ob_get_length() !== false) { @ob_clean(); }
    echo json_encode($payload);
    exit();
}
require_once '../shared/config.php';
require_once '../controllers/EmployeeManagementController.php';
require_once '../controllers/SystemLogger.php';

// Check if user is logged in
if (!isLoggedIn()) {
    send_json(['success' => false, 'message' => 'Unauthorized access']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Invalid request method']);
}

try {
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            send_json(['success' => false, 'message' => "Field '$field' is required"]);
        }
    }

    // Sanitize input data
    $data = [
        'first_name' => sanitize($_POST['first_name']),
        'middle_name' => isset($_POST['middle_name']) ? sanitize($_POST['middle_name']) : null,
        'last_name' => sanitize($_POST['last_name']),
        'email' => sanitize($_POST['email']),
        'contact_number' => isset($_POST['contact_number']) ? sanitize($_POST['contact_number']) : null,
    'department_id' => !empty($_POST['department']) ? (int)$_POST['department'] : null,
    'position_id' => !empty($_POST['position']) ? (int)$_POST['position'] : null,
    'gender' => isset($_POST['gender']) ? sanitize($_POST['gender']) : null,
    'birthdate' => isset($_POST['birthdate']) ? sanitize($_POST['birthdate']) : null,
    'basic_salary' => isset($_POST['basic_salary']) ? trim($_POST['basic_salary']) : null,
    'image' => null
    ];

    // Handle uploaded image if present
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/avatars';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $tmpName = $_FILES['image']['tmp_name'];
        $origName = basename($_FILES['image']['name']);
        $ext = pathinfo($origName, PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (!in_array(strtolower($ext), $allowed)) {
            send_json(['success' => false, 'message' => 'Invalid image format']);
        }

        $newName = 'avatar_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $uploadDir . DIRECTORY_SEPARATOR . $newName;
        if (move_uploaded_file($tmpName, $dest)) {
            // store relative path
            $data['image'] = 'uploads/avatars/' . $newName;
        }
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        send_json(['success' => false, 'message' => 'Invalid email format']);
    }

    // Check if email already exists
    $controller = new EmployeeManagementController();
    if ($controller->emailExists($data['email'])) {
        send_json(['success' => false, 'message' => 'Email already exists']);
    }

    // Create employee
    $result = $controller->addEmployee($data);
    
    // Handle allowances and deductions if employee was created successfully
    if ($result['success'] && isset($result['employee_id'])) {
        $employeeId = $result['employee_id'];
        
        // Process allowances
        if (isset($_POST['allowances']) && is_array($_POST['allowances'])) {
            $allowances = [];
            foreach ($_POST['allowances'] as $allowanceId) {
                $amount = isset($_POST['allowance_amounts'][$allowanceId]) ? (float)$_POST['allowance_amounts'][$allowanceId] : 0;
                if ($amount > 0) {
                    $allowances[$allowanceId] = $amount;
                }
            }
            
            if (!empty($allowances)) {
                $controller->assignAllowancesToEmployee($employeeId, $allowances);
            }
        }
        
        // Process deductions
        if (isset($_POST['deductions']) && is_array($_POST['deductions'])) {
            $deductions = [];
            foreach ($_POST['deductions'] as $deductionId) {
                $amount = isset($_POST['deduction_amounts'][$deductionId]) ? (float)$_POST['deduction_amounts'][$deductionId] : 0;
                if ($amount > 0) {
                    $deductions[$deductionId] = $amount;
                }
            }
            
            if (!empty($deductions)) {
                $controller->assignDeductionsToEmployee($employeeId, $deductions);
            }
        }
        
        // Automatically create work schedule based on working calendar
        try {
            $controller->createAutomaticEmployeeSchedule($employeeId);
        } catch (Exception $e) {
            error_log("Failed to create automatic employee schedule: " . $e->getMessage());
            // Don't fail the entire employee creation if schedule fails
        }
        
        // Log the action
        if (isset($_SESSION['user_id'])) {
            $employeeName = trim($data['first_name'] . ' ' . $data['last_name']);
            $logger = new SystemLogger();
            $logger->logEmployeeAction($_SESSION['user_id'], 'created', $employeeId, "Employee added to system");
        }
    } else {
        // Optionally log failure or take other actions
        $logger = new SystemLogger();
        $logger->log($_SESSION['user_id'], 'EMPLOYEE_ADD_FAILED', "Failed to add employee - Email: {$data['email']}");
        send_json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to add employee'
        ]);
    }

    send_json($result);

} catch (Exception $e) {
        send_json([
            'success' => false, 
            'message' => 'Server error: ' . $e->getMessage()
        ]);
}
?>

<?php
header('Content-Type: application/json');
require_once '../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'POST method required'
    ]);
    exit();
}

try {
    require_once __DIR__ . '/../controllers/LeaveController.php';
    $leaveController = new LeaveController();
    
    // Get data from POST request
    // Normalize leave_type from UI to DB enum values
    $typeMap = [
        'vacation' => 'Vacation',
        'sick' => 'Sick',
        'personal' => 'Personal',
        'emergency' => 'Emergency',
        'maternity' => 'Maternity',
        'paternity' => 'Paternity'
    ];

    $incomingType = $_POST['leave_type'] ?? '';
    $leaveType = $typeMap[strtolower($incomingType)] ?? ($incomingType ?: '');

    $data = [
        'leave_type' => $leaveType,
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'reason' => $_POST['reason'] ?? '',
        'half_day' => isset($_POST['half_day']) ? true : false
    ];
    
    // If admin/HR is submitting for another employee
    $allowedTypes = ['admin', 'supervisor', 'hr'];
    if (!empty($_POST['employee_id']) && in_array($_SESSION['user_type'], $allowedTypes)) {
        // Get user_id for the selected employee
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT user_id FROM employees WHERE employee_id = ?");
        $stmt->execute([$_POST['employee_id']]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($employee) {
            $userId = $employee['user_id'];
        } else {
            throw new Exception('Selected employee not found');
        }
    } else {
        // Employee submitting for themselves
        $userId = $_SESSION['user_id'];
    }
    
    $result = $leaveController->addLeaveRequest($data, $userId);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error submitting leave request: ' . $e->getMessage()
    ]);
}
?>

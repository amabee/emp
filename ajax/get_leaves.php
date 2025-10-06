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

try {
    require_once __DIR__ . '/../controllers/LeaveController.php';
    $leaveController = new LeaveController();
    
    // Get filters from request
    $filters = [];
    
    if (!empty($_GET['employee_id'])) {
        $filters['employee_id'] = $_GET['employee_id'];
    }
    
    if (!empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    
    if (!empty($_GET['leave_type'])) {
        $filters['leave_type'] = $_GET['leave_type'];
    }
    
    if (!empty($_GET['department_id'])) {
        $filters['department_id'] = $_GET['department_id'];
    }
    
    if (!empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }
    
    if (!empty($_GET['date_from'])) {
        $filters['date_from'] = $_GET['date_from'];
    }
    
    if (!empty($_GET['date_to'])) {
        $filters['date_to'] = $_GET['date_to'];
    }
    
    $result = $leaveController->getLeaveRequests($filters);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching leave requests: ' . $e->getMessage()
    ]);
}
?>

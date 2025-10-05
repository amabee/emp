<?php
header('Content-Type: application/json');
session_start();
require_once '../shared/config.php';
require_once '../controllers/EmployeeManagementController.php';
require_once '../controllers/SystemLogger.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
    exit();
}

try {
    $employeeId = (int)$_POST['id'];
    $controller = new EmployeeManagementController();
    
    $result = $controller->deleteEmployee($employeeId);
    
    // Log the action if successful
    if ($result['success'] && isset($_SESSION['user_id'])) {
        $logger = new SystemLogger();
        $logger->logEmployeeAction($_SESSION['user_id'], 'deleted', $employeeId, "Employee record removed from system");
    }
    
    echo json_encode([
        'status' => $result['success'] ? 'success' : 'error',
        'message' => $result['message']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

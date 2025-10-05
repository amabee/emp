<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/EmployeeManagementController.php';
require_once '../controllers/UserManagementController.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $employeeController = new EmployeeManagementController();
    $userController = new UserManagementController();
    
    $departments = $employeeController->getAllDepartments();
    $positions = $employeeController->getAllPositions();
    $userTypes = $userController->getAllUserTypes();
    
    echo json_encode([
        'success' => true,
        'departments' => $departments,
        'positions' => $positions,
        'user_types' => $userTypes
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'departments' => [],
        'positions' => [],
        'user_types' => []
    ]);
}
?>

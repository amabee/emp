<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    require_once __DIR__ . '/../controllers/AttendanceController.php';
    $controller = new AttendanceController();

    // Get all departments
    $departments = $controller->getDepartments();
    
    echo json_encode([
        'success' => true,
        'data' => $departments
    ]);

} catch (Exception $e) {
    error_log("get_departments.php Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to fetch departments: ' . $e->getMessage()
    ]);
}
?>

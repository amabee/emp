<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/EmployeeManagementController.php';

if (!ob_get_level()) ob_start();
function send_json($payload) { if (ob_get_length() !== false) { @ob_clean(); } echo json_encode($payload); exit(); }

// Check if user is logged in
if (!isLoggedIn()) {
    send_json(['success' => false, 'message' => 'Unauthorized access']);
}

try {
    $controller = new EmployeeManagementController();
    
    // Get filters
    $filters = [];
    if (!empty($_GET['search'])) {
        $filters['search'] = sanitize($_GET['search']);
    }
    if (!empty($_GET['department'])) {
        $filters['department'] = (int)$_GET['department'];
    }
    
    // Get employees, departments, and positions
    $employees = $controller->getAllEmployees($filters);
    $departments = $controller->getAllDepartments();
    $positions = $controller->getAllPositions();
    
    send_json(['success' => true, 'employees' => $employees, 'departments' => $departments, 'positions' => $positions]);

} catch (Exception $e) {
    send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage(), 'employees' => [], 'departments' => [], 'positions' => []]);
}
?>

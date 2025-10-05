<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/EmployeeManagementController.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$dept = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;
try {
    $controller = new EmployeeManagementController();
    $emps = $controller->getEmployeesByDepartment($dept);
    echo json_encode(['success' => true, 'employees' => $emps]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'employees' => []]);
}

?>

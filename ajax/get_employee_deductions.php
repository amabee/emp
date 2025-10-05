<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/EmployeeManagementController.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $controller = new EmployeeManagementController();
    $rows = $controller->getEmployeeDeductions();
    echo json_encode(['success' => true, 'employee_deductions' => $rows]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'employee_deductions' => []]);
}

?>

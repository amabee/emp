<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/EmployeeManagementController.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$pos = isset($_GET['position_id']) ? (int)$_GET['position_id'] : 0;
try {
    $controller = new EmployeeManagementController();
    $emps = $controller->getEmployeesByPosition($pos);
    echo json_encode(['success' => true, 'employees' => $emps]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'employees' => []]);
}

?>

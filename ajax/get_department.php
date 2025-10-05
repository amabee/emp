<?php
header('Content-Type: application/json');
session_start();

require_once '../shared/config.php';
require_once '../controllers/OrganizationController.php';

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check permissions (admin, supervisor, hr can view department details)
$allowedTypes = ['admin', 'supervisor', 'hr'];
if (!in_array($_SESSION['user_type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Department ID is required']);
    exit;
}

try {
    $controller = new OrganizationController();
    
    $departmentId = intval($_GET['id']);
    if ($departmentId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid department ID']);
        exit;
    }
    
    $department = $controller->getDepartment($departmentId);
    
    if (!$department) {
        echo json_encode(['success' => false, 'message' => 'Department not found']);
        exit;
    }
    
    // Get list of employees for department head dropdown
    $employees = $controller->getEmployeesForDepartmentHead();
    
    echo json_encode([
        'success' => true, 
        'data' => $department,
        'employees' => $employees
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

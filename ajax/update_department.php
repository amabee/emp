<?php
header('Content-Type: application/json');
session_start();

require_once '../shared/config.php';
require_once '../controllers/OrganizationController.php';
require_once '../controllers/SystemLogger.php';

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Only admin and supervisor can edit departments
$allowedTypes = ['admin', 'supervisor'];
if (!in_array($_SESSION['user_type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $controller = new OrganizationController();
    
    // Validate department ID
    $departmentId = intval($_POST['department_id'] ?? 0);
    if ($departmentId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid department ID']);
        exit;
    }
    
    // Validate input
    $data = [
        'department_name' => trim($_POST['department_name'] ?? ''),
        'department_head_id' => !empty($_POST['department_head_id']) ? intval($_POST['department_head_id']) : null,
        'active_status' => $_POST['active_status'] ?? 'active'
    ];
    
    if (empty($data['department_name'])) {
        echo json_encode(['success' => false, 'message' => 'Department name is required']);
        exit;
    }
    
    $result = $controller->updateDepartment($departmentId, $data);
    
    // Log the action if successful
    if ($result['success']) {
        $logger = new SystemLogger();
        $logger->logOrganizationalAction($_SESSION['user_id'], 'updated', 'department', $data['department_name']);
    }
    
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

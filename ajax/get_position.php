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

// Check permissions (admin, supervisor, hr can view position details)
$allowedTypes = ['admin', 'supervisor', 'hr'];
if (!in_array($_SESSION['user_type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Position ID is required']);
    exit;
}

try {
    $controller = new OrganizationController();
    
    $positionId = intval($_GET['id']);
    if ($positionId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid position ID']);
        exit;
    }
    
    $position = $controller->getPosition($positionId);
    
    if (!$position) {
        echo json_encode(['success' => false, 'message' => 'Position not found']);
        exit;
    }
    
    // Get departments for dropdown
    $departments = $controller->getAllDepartments();
    
    echo json_encode([
        'success' => true, 
        'data' => $position,
        'departments' => $departments
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

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

// Only admin and supervisor can edit positions
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
    
    // Validate position ID
    $positionId = intval($_POST['position_id'] ?? 0);
    if ($positionId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid position ID']);
        exit;
    }
    
    // Validate input
    $data = [
        'position_name' => trim($_POST['position_name'] ?? ''),
        'department_id' => !empty($_POST['department_id']) ? intval($_POST['department_id']) : null,
        'active_status' => $_POST['active_status'] ?? 'active'
    ];
    
    if (empty($data['position_name'])) {
        echo json_encode(['success' => false, 'message' => 'Position name is required']);
        exit;
    }
    
    $result = $controller->updatePosition($positionId, $data);
    
    // Log the action if successful
    if ($result['success']) {
        $logger = new SystemLogger();
        $logger->logOrganizationalAction($_SESSION['user_id'], 'updated', 'position', $data['position_name']);
    }
    
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

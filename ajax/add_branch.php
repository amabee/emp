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

// Only admin and supervisor can add branches
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
    
    // Validate input
    $data = [
        'branch_name' => trim($_POST['branch_name'] ?? ''),
        'branch_code' => trim($_POST['branch_code'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'manager_id' => !empty($_POST['manager_id']) ? intval($_POST['manager_id']) : null,
        'is_active' => isset($_POST['is_active']) ? intval($_POST['is_active']) : 1,
        'created_by' => $_SESSION['user_id']
    ];
    
    if (empty($data['branch_name'])) {
        echo json_encode(['success' => false, 'message' => 'Branch name is required']);
        exit;
    }
    
    // Validate email format if provided
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    $result = $controller->addBranch($data);
    
    // Log the action if successful
    if ($result['success']) {
        $logger = new SystemLogger();
        $logger->logOrganizationalAction($_SESSION['user_id'], 'created', 'branch', $data['branch_name']);
    }
    
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

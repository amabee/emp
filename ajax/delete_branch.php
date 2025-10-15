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

// Only admin can delete branches
$allowedTypes = ['admin'];
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
    
    // Validate branch ID
    $branchId = intval($_POST['branch_id'] ?? 0);
    if ($branchId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid branch ID']);
        exit;
    }
    
    // Get branch name before deleting for logging
    $branch = $controller->getBranch($branchId);
    $branchName = $branch ? $branch['name'] : "Branch ID: $branchId";
    
    $result = $controller->deleteBranch($branchId);
    
    // Log the action if successful
    if ($result['success']) {
        $logger = new SystemLogger();
        $logger->logOrganizationalAction($_SESSION['user_id'], 'deleted', 'branch', $branchName);
    }
    
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

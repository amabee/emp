<?php
header('Content-Type: application/json');
session_start();

require_once '../shared/config.php';
require_once '../controllers/OrganizationController.php';

if (!ob_get_level()) ob_start();
function send_json($payload) { if (ob_get_length() !== false) { @ob_clean(); } echo json_encode($payload); exit(); }

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id'])) {
    send_json(['success' => false, 'message' => 'Not authenticated']);
}

// Check permissions
$allowedTypes = ['admin', 'supervisor', 'hr'];
if (!in_array($_SESSION['user_type'], $allowedTypes)) {
    send_json(['success' => false, 'message' => 'Access denied']);
}

try {
    $controller = new OrganizationController();
    
    // Get optional branch_id parameter to exclude current branch's manager
    $excludeBranchId = isset($_GET['exclude_branch_id']) ? intval($_GET['exclude_branch_id']) : null;
    
    $employees = $controller->getAvailableBranchManagers($excludeBranchId);
    send_json(['success' => true, 'data' => $employees]);

} catch (Exception $e) {
    send_json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

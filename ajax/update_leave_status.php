<?php
header('Content-Type: application/json');
require_once '../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

// Check if user has permission to approve/reject leaves
$allowedTypes = ['admin', 'supervisor', 'hr'];
if (!in_array($_SESSION['user_type'], $allowedTypes)) {
    echo json_encode([
        'success' => false,
        'message' => 'You do not have permission to perform this action'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'POST method required'
    ]);
    exit();
}

try {
    require_once __DIR__ . '/../controllers/LeaveController.php';
    $leaveController = new LeaveController();
    
    // Get data from POST request
    $leaveId = $_POST['leave_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $comments = $_POST['comments'] ?? '';
    $userId = $_SESSION['user_id'];
    
    if (empty($leaveId) || empty($status)) {
        throw new Exception('Leave ID and status are required');
    }
    
    if (!in_array($status, ['Approved', 'Rejected'])) {
        throw new Exception('Invalid status. Must be Approved or Rejected');
    }
    
    $result = $leaveController->updateLeaveStatus($leaveId, $status, $userId, $comments);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating leave status: ' . $e->getMessage()
    ]);
}
?>

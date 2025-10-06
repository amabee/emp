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

try {
    require_once __DIR__ . '/../controllers/LeaveController.php';
    $leaveController = new LeaveController();
    
    $result = $leaveController->getLeaveStatistics();
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching leave statistics: ' . $e->getMessage()
    ]);
}
?>

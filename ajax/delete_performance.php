<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../shared/session_handler.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

// Only supervisors can delete performance evaluations
if (!isSupervisor()) {
    echo json_encode(['success' => false, 'message' => 'Only supervisors can delete performance evaluations']);
    exit();
}

try {
    if (empty($_POST['performance_id'])) {
        echo json_encode(['success' => false, 'message' => 'Performance ID is required']);
        exit();
    }
    
    // Check if performance evaluation exists
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT performance_id, employee_id FROM performance WHERE performance_id = ?");
    $stmt->execute([$_POST['performance_id']]);
    $performance = $stmt->fetch();
    
    if (!$performance) {
        echo json_encode(['success' => false, 'message' => 'Performance evaluation not found']);
        exit();
    }
    
    // Delete performance evaluation directly
    $stmt = $db->prepare("DELETE FROM performance WHERE performance_id = ?");
    $result = $stmt->execute([$_POST['performance_id']]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Performance evaluation deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete performance evaluation']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting performance evaluation: ' . $e->getMessage()
    ]);
}
?>

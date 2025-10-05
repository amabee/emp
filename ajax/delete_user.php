<?php
header('Content-Type: application/json');
session_start();
require_once '../shared/config.php';
require_once '../controllers/UserManagementController.php';
require_once '../controllers/SystemLogger.php';

// Check if user is logged in and has admin access
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

try {
    $userId = $_POST['id'];
    $controller = new UserManagementController();
    $result = $controller->deleteUser($userId);
    
    // Log the action if successful
    if ($result['success'] && isset($_SESSION['user_id'])) {
        $logger = new SystemLogger();
        $logger->logUserAction($_SESSION['user_id'], 'deleted', $userId, "User account removed from system");
    }
    
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

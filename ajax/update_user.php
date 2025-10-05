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

if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

try {
    $controller = new UserManagementController();
    
    // Prepare update data
    $updateData = [
        'username' => sanitize($_POST['username'] ?? ''),
        'user_type_id' => (int)($_POST['user_type_id'] ?? 0),
        'active_status' => sanitize($_POST['active_status'] ?? 'active'),
        'first_name' => sanitize($_POST['first_name'] ?? ''),
        'last_name' => sanitize($_POST['last_name'] ?? ''),
        'email' => sanitize($_POST['email'] ?? '')
    ];

    // Validate required fields
    if (empty($updateData['username'])) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit();
    }

    if (empty($updateData['user_type_id'])) {
        echo json_encode(['success' => false, 'message' => 'User type is required']);
        exit();
    }

    $result = $controller->updateUser($_POST['user_id'], $updateData);
    
    // Log the action if successful
    if ($result['success'] && isset($_SESSION['user_id'])) {
        $logger = new SystemLogger();
        $logger->logUserAction($_SESSION['user_id'], 'updated', $_POST['user_id'], "User account information updated");
    }
    
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

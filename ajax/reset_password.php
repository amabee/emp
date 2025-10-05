<?php
header('Content-Type: application/json');
session_start();

require_once '../shared/config.php';
require_once '../controllers/UserManagementController.php';
require_once '../controllers/SystemLogger.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check if user has permission to reset passwords (admin, supervisor, hr)
$allowedTypes = ['admin', 'supervisor', 'hr'];
if (!in_array($_SESSION['user_type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get input data
    $userId = intval($_POST['user_id'] ?? 0);
    $newPassword = trim($_POST['new_password'] ?? '');

    // Use UserManagementController to reset password
    $userController = new UserManagementController();
    $result = $userController->resetPassword($userId, $newPassword);

    if ($result['success']) {
        // Log the password reset action
        $logger = new SystemLogger();
        $logger->logUserAction($_SESSION['user_id'], 'reset password for', $userId, 'Password was successfully reset');
    }

    // Return the result from controller
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

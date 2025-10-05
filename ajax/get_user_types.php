<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/UserManagementController.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $controller = new UserManagementController();
    $userTypes = $controller->getAllUserTypes();
    
    echo json_encode([
        'success' => true,
        'userTypes' => $userTypes
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'userTypes' => []
    ]);
}
?>
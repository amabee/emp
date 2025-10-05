<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/SystemSettingsController.php';

// Check if user is logged in and has admin access
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $controller = new SystemSettingsController();
    $stats = $controller->getSystemStats();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'stats' => []
    ]);
}
?>
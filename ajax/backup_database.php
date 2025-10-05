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
    $result = $controller->createDatabaseBackup();
    
    if ($result['success']) {
        // Return download link
        $result['download_url'] = '../backups/' . $result['filename'];
    }
    
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
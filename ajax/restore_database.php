<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/SystemSettingsController.php';

// Check if user is logged in and has admin access
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_FILES['backupFile']) || $_FILES['backupFile']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No backup file uploaded']);
    exit();
}

try {
    $controller = new SystemSettingsController();
    $result = $controller->restoreDatabase($_FILES['backupFile']);
    
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
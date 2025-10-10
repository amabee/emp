<?php
require_once '../controllers/DatabaseViewsController.php';

header('Content-Type: application/json');

if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $controller = new DatabaseViewsController();
    $result = $controller->getViewStatistics();
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

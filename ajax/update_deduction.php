<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/DeductionController.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

try {
    $controller = new DeductionController();
    $result = $controller->updateDeduction($_POST, $_SESSION['user_id'] ?? null);
    // return controller result directly for consistent API shape
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

?>

<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/DeductionController.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $controller = new DeductionController();
    $types = $controller->getDeductionTypes();
    echo json_encode(['success' => true, 'deduction_types' => $types]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'deduction_types' => []]);
}

?>

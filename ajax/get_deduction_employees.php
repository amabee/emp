<?php
header('Content-Type: application/json');
require_once '../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$deductionId = isset($_GET['deduction_type_id']) ? intval($_GET['deduction_type_id']) : 0;
if ($deductionId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Deduction ID required', 'employees' => []]);
    exit();
}

try {
    require_once __DIR__ . '/../controllers/DeductionController.php';
    $controller = new DeductionController();
    $result = $controller->getDeductionEmployees($deductionId);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage(), 'employees' => []]);
}

?>

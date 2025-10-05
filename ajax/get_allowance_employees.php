<?php
header('Content-Type: application/json');
require_once '../shared/config.php';

if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized', 'employees' => []]);
  exit();
}

$allowanceId = isset($_GET['allowance_type_id']) ? intval($_GET['allowance_type_id']) : 0;
if ($allowanceId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Allowance ID required', 'employees' => []]);
  exit();
}

try {
  require_once __DIR__ . '/../controllers/AllowanceController.php';
  $controller = new AllowanceController();
  $result = $controller->getAllowanceEmployees($allowanceId);
  echo json_encode($result);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage(), 'employees' => []]);
}

?>

<?php
header('Content-Type: application/json');
require_once '../shared/config.php';

if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$data = $_POST;
try {
  require_once __DIR__ . '/../controllers/AllowanceController.php';
  $c = new AllowanceController();
  $res = $c->updateAllowance($data, $_SESSION['user_id'] ?? null);
  echo json_encode($res);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

?>

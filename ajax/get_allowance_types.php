<?php
header('Content-Type: application/json');
require_once '../shared/config.php';

if (!isLoggedIn()) {
  echo json_encode([]);
  exit();
}

try {
  require_once __DIR__ . '/../controllers/AllowanceController.php';
  $c = new AllowanceController();
  $res = $c->getAllowanceTypes();
  echo json_encode($res);
} catch (Exception $e) {
  echo json_encode([]);
}

?>

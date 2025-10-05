<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/DeductionController.php';

if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Assignment id required']);
  exit();
}

try {

  $controller = new DeductionController();
  $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
  $result = $controller->deleteEmployeeAssignment($id, $userId);
  echo json_encode($result);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

?>


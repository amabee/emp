<?php
header('Content-Type: application/json');
require_once '../shared/config.php';

if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

try {
  require_once __DIR__ . '/../controllers/UserManagementController.php';
  $c = new UserManagementController();
  $user = $c->getUserById($_SESSION['user_id']);
  if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
  }
  echo json_encode(['success' => true, 'user' => $user]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

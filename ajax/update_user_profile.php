<?php
header('Content-Type: application/json');
// Buffer output so we ensure only JSON is returned
if (!ob_get_level()) ob_start();
function send_json($payload) {
  if (ob_get_length() !== false) { @ob_clean(); }
  echo json_encode($payload);
  exit();
}
require_once '../shared/config.php';

if (!isLoggedIn()) {
  send_json(['success' => false, 'message' => 'Unauthorized']);
}

try {
  require_once __DIR__ . '/../controllers/UserManagementController.php';
  $c = new UserManagementController();

  $action = $_POST['action'] ?? 'update';
  $userId = $_SESSION['user_id'];

  if ($action === 'update') {
    $data = [
      'username' => trim($_POST['username'] ?? ''),
      'first_name' => trim($_POST['first_name'] ?? ''),
      'last_name' => trim($_POST['last_name'] ?? ''),
      'email' => trim($_POST['email'] ?? ''),
    ];
    $res = $c->updateUser($userId, $data);
    send_json($res);
  }

  if ($action === 'password') {
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    if ($newPassword === '' || $confirm === '') {
      send_json(['success' => false, 'message' => 'Password fields are required']);
    }
    if ($newPassword !== $confirm) {
      send_json(['success' => false, 'message' => 'Passwords do not match']);
    }
    $res = $c->resetPassword($userId, $newPassword);
    send_json($res);
  }

  send_json(['success' => false, 'message' => 'Invalid action']);
} catch (Exception $e) {
  send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

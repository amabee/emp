<?php
require_once __DIR__ . '/../shared/config.php';
require_once __DIR__ . '/../controllers/AllowanceController.php';

function send_json($payload)
{
  if (headers_sent() === false) header('Content-Type: application/json');
  while (ob_get_level() > 0) ob_end_clean();
  echo json_encode($payload);
  exit;
}

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') send_json(['success' => false, 'message' => 'POST required']);
  $post = $_POST;
  // normalize keys: allow employee_allowance_id or id
  if (isset($post['id']) && !isset($post['employee_allowance_id'])) $post['employee_allowance_id'] = $post['id'];

  // omit amount if it's present but empty string
  if (isset($post['amount']) && trim($post['amount']) === '') unset($post['amount']);

  $ctrl = new AllowanceController();
  // user id from session if available
  $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
  $res = $ctrl->updateEmployeeAssignment($post, $userId);
  send_json($res);
} catch (Exception $e) {
  send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

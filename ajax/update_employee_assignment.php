<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/DeductionController.php';

function send_json($payload) { if (ob_get_length() !== false) { @ob_clean(); } echo json_encode($payload); exit(); }

if (!isLoggedIn()) {
  send_json(['success' => false, 'message' => 'Unauthorized']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  send_json(['success' => false, 'message' => 'Invalid request method']);
}

$id = isset($_POST['employee_deduction_id']) ? intval($_POST['employee_deduction_id']) : 0;
if ($id <= 0) {
  send_json(['success' => false, 'message' => 'Assignment id required']);
}

// build payload and omit empty amount
$payload = ['employee_deduction_id' => $id];
if (isset($_POST['amount']) && $_POST['amount'] !== '') {
  $payload['amount'] = $_POST['amount'];
}

try {
  $c = new DeductionController();
  $res = $c->updateEmployeeAssignment($payload, $_SESSION['user_id'] ?? null);
  send_json($res);
} catch (Exception $e) {
  send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

?>

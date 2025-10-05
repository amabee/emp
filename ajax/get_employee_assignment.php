<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/DeductionController.php';

function send_json($payload) { if (ob_get_length() !== false) { @ob_clean(); } echo json_encode($payload); exit(); }

if (!isLoggedIn()) {
  send_json(['success' => false, 'message' => 'Unauthorized']);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
  send_json(['success' => false, 'message' => 'Assignment id required']);
}

try {
  $c = new DeductionController();
  // Reuse getDeductionEmployees to find the row by id
  $res = $c->getDeductionEmployees(0);
  // fallback: query specific assignment
  $db = getDBConnection();
  $stmt = $db->prepare("SELECT ed.employee_deduction_id, ed.employee_id, ed.amount, ed.deduction_type_id, dt.amount_type AS deduction_amount_type, CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) AS employee_name FROM employee_deduction ed JOIN employees e ON ed.employee_id = e.employee_id LEFT JOIN deduction_type dt ON ed.deduction_type_id = dt.deduction_type_id WHERE ed.employee_deduction_id = ? LIMIT 1");
  $stmt->execute([$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    send_json(['success' => false, 'message' => 'Assignment not found']);
  }
  send_json(['success' => true, 'assignment' => $row]);
} catch (Exception $e) {
  send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

?>

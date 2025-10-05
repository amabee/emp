<?php
require_once __DIR__ . '/../shared/config.php';
require_once __DIR__ . '/../controllers/AllowanceController.php';

// small helper to ensure clean JSON output
function send_json($payload)
{
  if (headers_sent() === false) header('Content-Type: application/json');
  // clear output buffers
  while (ob_get_level() > 0) ob_end_clean();
  echo json_encode($payload);
  exit;
}

try {
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  if ($id <= 0) send_json(['success' => false, 'message' => 'Assignment id required']);

  $ctrl = new AllowanceController();
  // reuse getAllowanceEmployees? we need single assignment - create a query
  $dbh = getDBConnection();
  $sql = "SELECT ea.employee_allowance_id, ea.allowance_id, ea.allowance_amount as amount, e.employee_id, CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) AS name, a.allowance_type AS allowance_name
          FROM employee_allowance ea
          LEFT JOIN employees e ON ea.employee_id = e.employee_id
          LEFT JOIN allowance a ON ea.allowance_id = a.allowance_id
          WHERE ea.employee_allowance_id = ? LIMIT 1";
  $stmt = $dbh->prepare($sql);
  $stmt->execute([$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) send_json(['success' => false, 'message' => 'Assignment not found']);
  send_json(['success' => true, 'assignment' => $row]);
} catch (Exception $e) {
  send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}


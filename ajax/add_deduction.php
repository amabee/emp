<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/DeductionController.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Only admin and hr can add deductions
$allowed = ['admin', 'hr'];
if (!in_array($_SESSION['user_type'] ?? '', $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$db = getDBConnection();
if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit();
}

// Basic validation
$name = trim($_POST['name'] ?? '');
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.0;
$amount_type = $_POST['amount_type'] ?? 'fixed';
$apply_to = $_POST['apply_to'] ?? 'all';
$department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
$position_id = !empty($_POST['position_id']) ? intval($_POST['position_id']) : null;
$employee_id = !empty($_POST['employee_id']) ? intval($_POST['employee_id']) : null;

if ($name === '') {
    echo json_encode(['status' => 'error', 'message' => 'Deduction name is required']);
    exit();
}

if (!in_array($amount_type, ['fixed', 'percentage'])) {
    $amount_type = 'fixed';
}

if (!in_array($apply_to, ['all', 'department', 'position', 'employee'])) {
    $apply_to = 'all';
}

try {
    // Delegate the insertion and assignment logic to the DeductionController
    $controller = new DeductionController();
    $result = $controller->addDeduction($_POST, $_SESSION['user_id'] ?? null);
    // normalize response shape to include success boolean
    if (!isset($result['success'])) {
        $result['success'] = false;
    }
    $response = $result;

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response);
exit();

?>

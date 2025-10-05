<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/EmployeeManagementController.php';

if (!ob_get_level()) ob_start();
function send_json($payload) { if (ob_get_length() !== false) { @ob_clean(); } echo json_encode($payload); exit(); }

// Check if user is logged in
if (!isLoggedIn()) {
    send_json(['success' => false, 'message' => 'Unauthorized access']);
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    send_json(['success' => false, 'message' => 'Employee ID is required']);
}

try {
    $controller = new EmployeeManagementController();
    $employee = $controller->getEmployeeById((int)$_GET['id']);
    
    if ($employee) {
        send_json(['success' => true, 'employee' => $employee]);
    } else {
        send_json(['success' => false, 'message' => 'Employee not found']);
    }

} catch (Exception $e) {
    send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

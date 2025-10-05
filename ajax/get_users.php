<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/UserManagementController.php';

if (!ob_get_level()) ob_start();
function send_json($payload) { if (ob_get_length() !== false) { @ob_clean(); } echo json_encode($payload); exit(); }

// Check if user is logged in and has admin access
if (!isLoggedIn()) {
    send_json(['success' => false, 'message' => 'Unauthorized access']);
}

try {
    $controller = new UserManagementController();
    $users = $controller->getAllUsers();
    send_json(['success' => true, 'users' => $users]);

} catch (Exception $e) {
    send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage(), 'users' => []]);
}
?>

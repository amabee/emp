<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/UserManagementController.php';

// Buffer output so we can ensure only JSON is returned
if (!ob_get_level()) ob_start();
function send_json($payload) { if (ob_get_length() !== false) { @ob_clean(); } echo json_encode($payload); exit(); }

// Check if user is logged in and has admin access
if (!isLoggedIn()) {
    send_json(['success' => false, 'message' => 'Unauthorized access']);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    send_json(['success' => false, 'message' => 'Invalid user ID']);
}

try {
    $controller = new UserManagementController();
    $user = $controller->getUserById($_GET['id']);
    
    if ($user) {
        send_json(['success' => true, 'user' => $user]);
    } else {
        send_json(['success' => false, 'message' => 'User not found']);
    }

} catch (Exception $e) {
    send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

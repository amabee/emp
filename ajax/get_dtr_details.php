<?php
session_start();
require_once '../controllers/DTRController.php';

header('Content-Type: application/json');

// Add error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log that file was accessed
error_log("get_dtr_details.php accessed - Method: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

try {
    $controller = new DTRController();
    
    // Get attendance_id from request
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $attendanceId = isset($input['attendance_id']) ? (int)$input['attendance_id'] : 0;
    
    // Debug: Log the received data
    error_log("Received attendance_id: " . $attendanceId);
    error_log("Raw input: " . $rawInput);
    
    if (!$attendanceId) {
        echo json_encode(['success' => false, 'message' => 'Attendance ID is required']);
        exit;
    }
    
    $result = $controller->getDTRDetails($attendanceId);
    
    // Debug: Log the result
    error_log("DTR Details Result: " . json_encode($result));
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching DTR details: ' . $e->getMessage()
    ]);
}
?>

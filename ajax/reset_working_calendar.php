<?php
session_start();
require_once '../controllers/WorkingCalendarController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is admin
$user_type = $_SESSION['user_type'] ?? 'employee';
if ($user_type !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Only administrators can reset working calendar.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Try form data if JSON is not available
        $input = $_POST;
    }
    
    if (!isset($input['month']) || !isset($input['year'])) {
        throw new Exception('Missing required fields: month and year');
    }
    
    $month = (int)$input['month'];
    $year = (int)$input['year'];
    
    if ($month < 1 || $month > 12 || $year < 2020 || $year > 2030) {
        throw new Exception('Invalid month or year');
    }
    
    $controller = new WorkingCalendarController();
    $result = $controller->resetMonth($month, $year);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

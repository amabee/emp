<?php
session_start();
require_once '../controllers/WorkingCalendarController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $month = $_GET['month'] ?? date('n');
    $year = $_GET['year'] ?? date('Y');
    
    if (!is_numeric($month) || !is_numeric($year)) {
        throw new Exception('Invalid month or year');
    }
    
    $controller = new WorkingCalendarController();
    $result = $controller->getEmployeeSchedules($month, $year);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

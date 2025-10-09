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
    $controller = new WorkingCalendarController();
    
    // Handle different request types
    $type = $_GET['type'] ?? 'calendar';
    
    switch ($type) {
        case 'today_status':
            $date = $_GET['date'] ?? date('Y-m-d');
            $result = $controller->getTodayWorkStatus($date);
            break;
            
        case 'upcoming_holidays':
            $limit = (int)($_GET['limit'] ?? 5);
            $result = $controller->getUpcomingHolidays($limit);
            break;
            
        case 'week_schedule':
            $startDate = $_GET['start_date'] ?? date('Y-m-d');
            $result = $controller->getWeekSchedule($startDate);
            break;
            
        case 'calendar':
        default:
            $month = $_GET['month'] ?? date('n');
            $year = $_GET['year'] ?? date('Y');
            
            if (!is_numeric($month) || !is_numeric($year)) {
                throw new Exception('Invalid month or year');
            }
            
            $result = $controller->getWorkingCalendar($month, $year);
            break;
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

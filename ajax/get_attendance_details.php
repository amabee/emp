<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    // Get attendance ID from request
    $input = json_decode(file_get_contents('php://input'), true);
    $attendanceId = isset($input['attendance_id']) ? (int)$input['attendance_id'] : 0;
    
    // Also check GET parameters for flexibility
    if (!$attendanceId && isset($_GET['attendance_id'])) {
        $attendanceId = (int)$_GET['attendance_id'];
    }
    
    if (!$attendanceId) {
        echo json_encode(['success' => false, 'message' => 'Attendance ID is required']);
        exit();
    }
    
    require_once __DIR__ . '/../controllers/AttendanceController.php';
    $controller = new AttendanceController();
    
    // Get attendance details
    $details = $controller->getAttendanceDetails($attendanceId);
    
    if ($details) {
        echo json_encode([
            'success' => true,
            'data' => $details
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Attendance record not found'
        ]);
    }

} catch (Exception $e) {
    error_log("get_attendance_details.php Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to fetch attendance details: ' . $e->getMessage()
    ]);
}
?>

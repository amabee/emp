<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/DashboardController.php';

// Check if user is logged in
if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
  exit();
}

try {
  $controller = new DashboardController();

  // Get all dashboard data
  $stats = $controller->getDashboardStats();
  $recentActivity = $controller->getRecentActivity(5);
  $attendanceData = $controller->getAttendanceData();
  $departmentStats = $controller->getDepartmentStats();

  echo json_encode([
    'success' => true,
    'data' => [
      'stats' => $stats,
      'recent_activity' => $recentActivity,
      'attendance' => $attendanceData,
      'departments' => $departmentStats
    ]
  ]);

} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Server error: ' . $e->getMessage(),
    'data' => [
      'stats' => [
        'total_employees' => 0,
        'new_employees_this_month' => 0,
        'employee_growth_percentage' => 0,
        'pending_leaves' => 0,
        'total_departments' => 0,
        'total_users' => 0
      ],
      'recent_activity' => [],
      'attendance' => [
        'labels' => [],
        'present' => [],
        'absent' => [],
        'late' => []
      ],
      'departments' => []
    ]
  ]);
}
?>


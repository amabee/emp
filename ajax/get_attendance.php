<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    require_once __DIR__ . '/../controllers/AttendanceController.php';
    $controller = new AttendanceController();

    // Get filters from request
    $filters = [];
    
    // Date filter (default to today if not specified)
    $filters['date'] = $_GET['date'] ?? date('Y-m-d');
    
    // Department filter
    if (!empty($_GET['department'])) {
        $filters['department'] = $_GET['department'];
    }
    
    // Status filter
    if (!empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    
    // Search filter
    if (!empty($_GET['search'])) {
        $filters['search'] = trim($_GET['search']);
    }
    
    // Pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50; // Max 100 records per page
    $offset = ($page - 1) * $limit;
    
    $filters['limit'] = $limit;
    $filters['offset'] = $offset;
    
    // Get attendance records
    $records = $controller->getAttendanceRecords($filters);
    
    // Get total count for pagination
    $totalRecords = $controller->getAttendanceCount($filters);
    $totalPages = ceil($totalRecords / $limit);
    
    // Get attendance summary for the selected date
    $summary = $controller->getAttendanceSummary($filters['date']);
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $records,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'records_per_page' => $limit,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ],
        'summary' => $summary,
        'filters_applied' => [
            'date' => $filters['date'],
            'department' => $filters['department'] ?? null,
            'status' => $filters['status'] ?? null,
            'search' => $filters['search'] ?? null
        ]
    ];
    
    echo json_encode($response);

} catch (Exception $e) {
    error_log("get_attendance.php Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to fetch attendance records: ' . $e->getMessage()
    ]);
}
?>

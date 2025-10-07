<?php
header('Content-Type: application/json');
require_once '../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

try {
    require_once __DIR__ . '/../controllers/LeaveController.php';
    $leaveController = new LeaveController();
    
    // Get filters from request and normalize for backend
    $filters = [];

    if (!empty($_GET['employee_id'])) {
        $filters['employee_id'] = (int) $_GET['employee_id'];
    }

    if (!empty($_GET['status'])) {
        // Accept lowercase values from UI and convert to DB enum format
        $statusMap = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        ];
        $in = strtolower($_GET['status']);
        if (isset($statusMap[$in])) {
            $filters['status'] = $statusMap[$in];
        }
    }

    if (!empty($_GET['leave_type'])) {
        // Map UI leave types to DB enum values (case-sensitive)
        $typeMap = [
            'vacation' => 'Vacation',
            'sick' => 'Sick',
            'personal' => 'Personal',
            'emergency' => 'Emergency',
            'maternity' => 'Maternity',
            'paternity' => 'Paternity'
        ];
        $lt = strtolower($_GET['leave_type']);
        if (isset($typeMap[$lt])) {
            $filters['leave_type'] = $typeMap[$lt];
        }
    }

    if (!empty($_GET['department_id'])) {
        // UI may send department id or department name; try numeric id first
        if (is_numeric($_GET['department_id'])) {
            $filters['department_id'] = (int) $_GET['department_id'];
        } else {
            // Lookup department id by name
            $db = getDBConnection();
            $stmt = $db->prepare("SELECT department_id FROM department WHERE department_name = :name LIMIT 1");
            $stmt->execute(['name' => $_GET['department_id']]);
            $dept = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($dept) $filters['department_id'] = $dept['department_id'];
        }
    }

    if (!empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }

    if (!empty($_GET['date_from'])) {
        $filters['date_from'] = $_GET['date_from'];
    }

    if (!empty($_GET['date_to'])) {
        $filters['date_to'] = $_GET['date_to'];
    }
    
    $result = $leaveController->getLeaveRequests($filters);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching leave requests: ' . $e->getMessage()
    ]);
}
?>

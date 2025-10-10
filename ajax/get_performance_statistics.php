<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../shared/session_handler.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    require_once __DIR__ . '/../controllers/PerformanceController.php';
    $controller = new PerformanceController();
    
    // Build filters from GET parameters
    $filters = [];
    
    if (!empty($_GET['department_id'])) {
        $filters['department_id'] = $_GET['department_id'];
    }
    
    if (!empty($_GET['period_year'])) {
        $filters['period_year'] = $_GET['period_year'];
    }
    
    // For employees, only show their own statistics
    if (isEmployee()) {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT employee_id FROM employees WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($employee) {
            $filters['employee_id'] = $employee['employee_id'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Employee record not found']);
            exit();
        }
    }
    
    $result = $controller->getPerformanceStatistics($filters);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching performance statistics: ' . $e->getMessage()
    ]);
}
?>

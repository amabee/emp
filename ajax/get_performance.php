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
    
    // Check if specific performance ID is requested
    if (isset($_GET['performance_id'])) {
        $result = $controller->getPerformanceById($_GET['performance_id']);
        if ($result['success']) {
            // Wrap single result in array for consistency with frontend
            $result['data'] = [$result['data']];
        }
        echo json_encode($result);
        exit();
    }
    
    // Build filters from GET parameters
    $filters = [];
    
    if (!empty($_GET['employee_id'])) {
        $filters['employee_id'] = $_GET['employee_id'];
    }
    
    if (!empty($_GET['department_id'])) {
        $filters['department_id'] = $_GET['department_id'];
    }
    
    if (!empty($_GET['period_year'])) {
        $filters['period_year'] = $_GET['period_year'];
    }
    
    if (!empty($_GET['rating_min'])) {
        $filters['rating_min'] = $_GET['rating_min'];
    }
    
    if (!empty($_GET['rating_max'])) {
        $filters['rating_max'] = $_GET['rating_max'];
    }
    
    // For employees, only show their own performance evaluations
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
    
    $result = $controller->getAllPerformances($filters);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching performance data: ' . $e->getMessage()
    ]);
}
?>

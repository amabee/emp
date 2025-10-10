<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../shared/session_handler.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Build base conditions
    $conditions = [];
    $params = [];
    
    // For employees, only show their own statistics
    if (isEmployee()) {
        $stmt = $db->prepare("SELECT employee_id FROM employees WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($employee) {
            $conditions[] = "p.employee_id = ?";
            $params[] = $employee['employee_id'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Employee record not found']);
            exit();
        }
    }
    
    // Add filters
    if (!empty($_GET['department_id'])) {
        $conditions[] = "e.department_id = ?";
        $params[] = $_GET['department_id'];
    }
    
    if (!empty($_GET['period_year'])) {
        $conditions[] = "YEAR(p.period_start) = ?";
        $params[] = $_GET['period_year'];
    }
    
    $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
    
    // Get overall statistics
    $sql = "SELECT 
                COUNT(*) as total_evaluations,
                AVG(p.rating) as average_rating,
                COUNT(DISTINCT p.employee_id) as total_employees_evaluated,
                SUM(CASE WHEN p.rating >= 4 THEN 1 ELSE 0 END) as excellent_performers
            FROM performance p
            LEFT JOIN employees e ON p.employee_id = e.employee_id
            $whereClause";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $overall = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'overall' => [
            'total_evaluations' => (int)$overall['total_evaluations'],
            'average_rating' => round((float)$overall['average_rating'], 1),
            'total_employees_evaluated' => (int)$overall['total_employees_evaluated'],
            'excellent_performers' => (int)$overall['excellent_performers']
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching performance statistics: ' . $e->getMessage()
    ]);
}
?>

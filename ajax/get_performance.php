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
    
    // Check if specific performance ID is requested
    if (isset($_GET['performance_id'])) {
        $stmt = $db->prepare("SELECT 
                    p.performance_id,
                    p.employee_id,
                    p.period_start,
                    p.period_end,
                    p.rating,
                    p.remarks,
                    p.evaluated_by,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    d.department_name,
                    pos.position_name,
                    CONCAT(eval_emp.first_name, ' ', eval_emp.last_name) as evaluator_name
                FROM performance p
                LEFT JOIN employees e ON p.employee_id = e.employee_id
                LEFT JOIN department d ON e.department_id = d.department_id
                LEFT JOIN job_position pos ON e.position_id = pos.position_id
                LEFT JOIN users eval_u ON p.evaluated_by = eval_u.user_id
                LEFT JOIN employees eval_emp ON eval_u.user_id = eval_emp.user_id
                WHERE p.performance_id = ?");
        
        $stmt->execute([$_GET['performance_id']]);
        $performance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($performance) {
            echo json_encode(['success' => true, 'data' => [$performance]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Performance evaluation not found']);
        }
        exit();
    }
    
    // Build SQL query with filters
    $sql = "SELECT 
                p.performance_id,
                p.employee_id,
                p.period_start,
                p.period_end,
                p.rating,
                p.remarks,
                p.evaluated_by,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                d.department_name,
                pos.position_name,
                CONCAT(eval_emp.first_name, ' ', eval_emp.last_name) as evaluator_name
            FROM performance p
            LEFT JOIN employees e ON p.employee_id = e.employee_id
            LEFT JOIN department d ON e.department_id = d.department_id
            LEFT JOIN job_position pos ON e.position_id = pos.position_id
            LEFT JOIN users eval_u ON p.evaluated_by = eval_u.user_id
            LEFT JOIN employees eval_emp ON eval_u.user_id = eval_emp.user_id";
    
    $conditions = [];
    $params = [];
    
    // For employees, only show their own performance evaluations
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
    
    // Add other filters
    if (!empty($_GET['employee_id'])) {
        $conditions[] = "p.employee_id = ?";
        $params[] = $_GET['employee_id'];
    }
    
    if (!empty($_GET['department_id'])) {
        $conditions[] = "e.department_id = ?";
        $params[] = $_GET['department_id'];
    }
    
    if (!empty($_GET['rating_min'])) {
        $conditions[] = "p.rating >= ?";
        $params[] = $_GET['rating_min'];
    }
    
    if (!empty($_GET['rating_max'])) {
        $conditions[] = "p.rating <= ?";
        $params[] = $_GET['rating_max'];
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY p.period_end DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $performances = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $performances]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching performance data: ' . $e->getMessage()
    ]);
}
?>

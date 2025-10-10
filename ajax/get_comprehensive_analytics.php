<?php
require_once '../controllers/DatabaseViewsController.php';

header('Content-Type: application/json');

if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $controller = new DatabaseViewsController();

    // Get comprehensive employee analytics
    $filters = [];
    
    if (isset($_GET['department'])) {
        $filters['department'] = $_GET['department'];
    }
    
    if (isset($_GET['performance_category'])) {
        $filters['performance_category'] = $_GET['performance_category'];
    }
    
    if (isset($_GET['evaluation_recency'])) {
        $filters['evaluation_recency'] = $_GET['evaluation_recency'];
    }
    
    if (isset($_GET['min_salary']) && is_numeric($_GET['min_salary'])) {
        $filters['min_salary'] = (float)$_GET['min_salary'];
    }
    
    if (isset($_GET['max_dept_rank']) && is_numeric($_GET['max_dept_rank'])) {
        $filters['max_dept_rank'] = (int)$_GET['max_dept_rank'];
    }
    
    if (isset($_GET['order_by'])) {
        $allowedOrderBy = [
            'company_performance_rank', 'dept_performance_rank', 
            'avg_performance_rating', 'basic_salary', 'employee_name'
        ];
        if (in_array($_GET['order_by'], $allowedOrderBy)) {
            $filters['order_by'] = $_GET['order_by'];
        }
    }
    
    if (isset($_GET['order_direction'])) {
        if (in_array(strtoupper($_GET['order_direction']), ['ASC', 'DESC'])) {
            $filters['order_direction'] = strtoupper($_GET['order_direction']);
        }
    }
    
    // Pagination
    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        $filters['page'] = (int)$_GET['page'];
    }
    
    if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
        $filters['limit'] = (int)$_GET['limit'];
    }

    $result = $controller->getComprehensiveEmployeeAnalytics($filters);
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

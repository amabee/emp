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

    // Get filtered active employees with pre-applied business logic
    $filters = [];
    
    if (isset($_GET['tenure_category'])) {
        $filters['tenure_category'] = $_GET['tenure_category'];
    }
    
    if (isset($_GET['salary_category'])) {
        $filters['salary_category'] = $_GET['salary_category'];
    }
    
    if (isset($_GET['department'])) {
        $filters['department'] = $_GET['department'];
    }
    
    if (isset($_GET['has_recent_evaluation'])) {
        $filters['has_recent_evaluation'] = $_GET['has_recent_evaluation'] === 'true' ? 1 : 0;
    }
    
    if (isset($_GET['min_performance']) && is_numeric($_GET['min_performance'])) {
        $filters['min_performance'] = (float)$_GET['min_performance'];
    }
    
    // Pagination
    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        $filters['page'] = (int)$_GET['page'];
    }
    
    if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
        $filters['limit'] = (int)$_GET['limit'];
    }

    $result = $controller->getActiveEmployeesWithFilters($filters);
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

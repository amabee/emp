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

    // Get department summary statistics
    $filters = [];
    
    if (isset($_GET['min_employees']) && is_numeric($_GET['min_employees'])) {
        $filters['min_employees'] = (int)$_GET['min_employees'];
    }
    
    if (isset($_GET['min_performance']) && is_numeric($_GET['min_performance'])) {
        $filters['min_performance'] = (float)$_GET['min_performance'];
    }
    
    if (isset($_GET['department_name']) && !empty($_GET['department_name'])) {
        $filters['department_name'] = $_GET['department_name'];
    }

    $result = $controller->getDepartmentSummaryStats($filters);
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

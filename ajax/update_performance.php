<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../shared/session_handler.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

// Only supervisors can update performance evaluations
if (!isSupervisor()) {
    echo json_encode(['success' => false, 'message' => 'Only supervisors can update performance evaluations']);
    exit();
}

try {
    // Validate required fields
    $required_fields = ['performance_id', 'employee_id', 'evaluator_id', 'period_year', 'quality_rating', 'productivity_rating', 'teamwork_rating', 'communication_rating', 'attendance_rating'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            exit();
        }
    }
    
    // Validate rating values (1-5)
    $rating_fields = ['quality_rating', 'productivity_rating', 'teamwork_rating', 'communication_rating', 'attendance_rating'];
    foreach ($rating_fields as $field) {
        $rating = intval($_POST[$field]);
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => "Rating for $field must be between 1 and 5"]);
            exit();
        }
    }
    
    // Check if performance evaluation exists
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT performance_id FROM performance WHERE performance_id = ?");
    $stmt->execute([$_POST['performance_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Performance evaluation not found']);
        exit();
    }
    
    // Check if employee exists
    $stmt = $db->prepare("SELECT employee_id FROM employees WHERE employee_id = ?");
    $stmt->execute([$_POST['employee_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
        exit();
    }
    
    // Check if evaluator exists
    $stmt = $db->prepare("SELECT employee_id FROM employees WHERE employee_id = ?");
    $stmt->execute([$_POST['evaluator_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Evaluator not found']);
        exit();
    }
    
    require_once __DIR__ . '/../controllers/PerformanceController.php';
    $controller = new PerformanceController();
    
    $performance_data = [
        'employee_id' => $_POST['employee_id'],
        'evaluator_id' => $_POST['evaluator_id'],
        'period_year' => $_POST['period_year'],
        'quality_rating' => $_POST['quality_rating'],
        'productivity_rating' => $_POST['productivity_rating'],
        'teamwork_rating' => $_POST['teamwork_rating'],
        'communication_rating' => $_POST['communication_rating'],
        'attendance_rating' => $_POST['attendance_rating'],
        'comments' => $_POST['comments'] ?? '',
        'goals' => $_POST['goals'] ?? '',
        'achievements' => $_POST['achievements'] ?? ''
    ];
    
    $result = $controller->updatePerformance($_POST['performance_id'], $performance_data);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating performance evaluation: ' . $e->getMessage()
    ]);
}
?>

<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../shared/session_handler.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

// Only supervisors can add performance evaluations
if (!isSupervisor()) {
    echo json_encode(['success' => false, 'message' => 'Only supervisors can create performance evaluations']);
    exit();
}

try {
    // Validate required fields
    $required_fields = ['employee_id', 'period_start', 'period_end', 'rating'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            exit();
        }
    }
    
    // Validate rating value (1-5)
    $rating = intval($_POST['rating']);
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => "Rating must be between 1 and 5"]);
        exit();
    }
    
    // Validate dates
    if (strtotime($_POST['period_start']) >= strtotime($_POST['period_end'])) {
        echo json_encode(['success' => false, 'message' => "Period start date must be before end date"]);
        exit();
    }
    
    // Check if employee exists
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT employee_id FROM employees WHERE employee_id = ?");
    $stmt->execute([$_POST['employee_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
        exit();
    }
    
    // Check if performance evaluation already exists for this employee and period
    $stmt = $db->prepare("SELECT performance_id FROM performance WHERE employee_id = ? AND period_start = ? AND period_end = ?");
    $stmt->execute([$_POST['employee_id'], $_POST['period_start'], $_POST['period_end']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Performance evaluation already exists for this employee and period']);
        exit();
    }
    
    // Get current user as evaluator
    $evaluator_id = $_SESSION['user_id'];
    
    // Insert performance evaluation directly
    $stmt = $db->prepare("INSERT INTO performance (employee_id, period_start, period_end, rating, remarks, evaluated_by) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        $_POST['employee_id'],
        $_POST['period_start'],
        $_POST['period_end'],
        $_POST['rating'],
        $_POST['remarks'] ?? '',
        $evaluator_id
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Performance evaluation created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create performance evaluation']);
    }

    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error creating performance evaluation: ' . $e->getMessage()
    ]);
}
?>

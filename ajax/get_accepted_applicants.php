<?php
require_once '../shared/config.php';
require_once '../controllers/ApplicantController.php';

header('Content-Type: application/json');

// Check if user is logged in (employee/HR)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = getDBConnection();
    if (!$db) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $applicantController = new ApplicantController($db);
    
    // Get only accepted applicants who haven't been hired yet
    $filters = ['status' => 'accepted'];
    $result = $applicantController->getAllApplicants($filters);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Get accepted applicants error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching applicants']);
}

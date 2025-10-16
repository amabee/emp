<?php
require_once '../shared/config.php';
require_once '../controllers/ApplicantController.php';

header('Content-Type: application/json');

try {
    $db = getDBConnection();
    if (!$db) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $applicantController = new ApplicantController($db);
    
    // Check if applicant_id is provided (for HR/employee use)
    if (isset($_POST['applicant_id']) || isset($_GET['applicant_id'])) {
        // HR/Employee accessing applicant data - check if logged in as employee
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        $applicantId = intval($_POST['applicant_id'] ?? $_GET['applicant_id']);
    } else {
        // Applicant accessing their own data
        if (!isset($_SESSION['applicant_logged_in']) || !$_SESSION['applicant_logged_in']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        $applicantId = $_SESSION['applicant_id'];
    }
    
    $result = $applicantController->getProfile($applicantId);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Get applicant profile error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching profile']);
}


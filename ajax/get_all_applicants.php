<?php
session_start();
require_once '../config/database.php';
require_once '../controllers/ApplicantController.php';

header('Content-Type: application/json');

// Check if user is logged in (employee/HR)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $applicantController = new ApplicantController($db);
    
    // Get filters from request
    $filters = [];
    if (!empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    if (!empty($_GET['position_id'])) {
        $filters['position_id'] = intval($_GET['position_id']);
    }
    if (!empty($_GET['branch_id'])) {
        $filters['branch_id'] = intval($_GET['branch_id']);
    }
    
    $result = $applicantController->getAllApplicants($filters);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Get all applicants error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching applicants']);
}

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $applicantController = new ApplicantController($db);
    
    $applicantId = intval($_POST['applicant_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    $performedBy = $_SESSION['user_id'];
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($applicantId) || empty($newStatus)) {
        echo json_encode(['success' => false, 'message' => 'Applicant ID and status are required']);
        exit;
    }
    
    $result = $applicantController->updateStatus($applicantId, $newStatus, $performedBy, $notes);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Update applicant status error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating status']);
}

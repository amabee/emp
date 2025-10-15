<?php
session_start();
require_once '../config/database.php';
require_once '../controllers/ApplicantController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $applicantController = new ApplicantController($db);
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }
    
    $result = $applicantController->login($email, $password);
    
    if ($result['success']) {
        // Set session variables
        $_SESSION['applicant_logged_in'] = true;
        $_SESSION['applicant_id'] = $result['applicant']['applicant_id'];
        $_SESSION['applicant_name'] = $result['applicant']['first_name'] . ' ' . $result['applicant']['last_name'];
        $_SESSION['applicant_email'] = $result['applicant']['email'];
        $_SESSION['applicant_status'] = $result['applicant']['status'];
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Applicant login error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during login']);
}

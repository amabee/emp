<?php
session_start();
require_once '../config/database.php';
require_once '../controllers/ApplicantController.php';

header('Content-Type: application/json');

// Check if applicant is logged in
if (!isset($_SESSION['applicant_logged_in']) || !$_SESSION['applicant_logged_in']) {
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
    
    $applicantId = $_SESSION['applicant_id'];
    
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'middle_name' => trim($_POST['middle_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'alternative_phone' => trim($_POST['alternative_phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'zip_code' => trim($_POST['zip_code'] ?? ''),
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'gender' => $_POST['gender'] ?? null,
        'skills' => trim($_POST['skills'] ?? ''),
        'experience_years' => !empty($_POST['experience_years']) ? floatval($_POST['experience_years']) : null,
        'expected_salary' => !empty($_POST['expected_salary']) ? floatval($_POST['expected_salary']) : null,
        'available_start_date' => $_POST['available_start_date'] ?? null,
        'reference_name' => trim($_POST['reference_name'] ?? ''),
        'reference_contact' => trim($_POST['reference_contact'] ?? ''),
        'reference_relationship' => trim($_POST['reference_relationship'] ?? '')
    ];
    
    $result = $applicantController->updateProfile($applicantId, $data);
    
    if ($result['success']) {
        // Update session name if changed
        $_SESSION['applicant_name'] = $data['first_name'] . ' ' . $data['last_name'];
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Update applicant profile error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating profile']);
}

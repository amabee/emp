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
    
    // Collect form data
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'middle_name' => trim($_POST['middle_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'phone' => trim($_POST['phone'] ?? ''),
        'alternative_phone' => trim($_POST['alternative_phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'zip_code' => trim($_POST['zip_code'] ?? ''),
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'gender' => $_POST['gender'] ?? null,
        'position_applied' => !empty($_POST['position_applied']) ? intval($_POST['position_applied']) : null,
        'branch_applied' => !empty($_POST['branch_applied']) ? intval($_POST['branch_applied']) : null,
        'department_applied' => !empty($_POST['department_applied']) ? intval($_POST['department_applied']) : null,
        'skills' => trim($_POST['skills'] ?? ''),
        'experience_years' => !empty($_POST['experience_years']) ? floatval($_POST['experience_years']) : null,
        'expected_salary' => !empty($_POST['expected_salary']) ? floatval($_POST['expected_salary']) : null,
        'available_start_date' => $_POST['available_start_date'] ?? null
    ];
    
    $result = $applicantController->register($data);
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Applicant registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during registration']);
}

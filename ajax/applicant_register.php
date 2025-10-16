<?php
require_once '../shared/config.php';
require_once '../controllers/ApplicantController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = getDBConnection();
    if (!$db) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $applicantController = new ApplicantController($db);
    
    // Create uploads directory if it doesn't exist
    $uploadBaseDir = '../uploads/applicants';
    if (!file_exists($uploadBaseDir)) {
        mkdir($uploadBaseDir, 0777, true);
    }
    
    // Handle file uploads
    $uploadedFiles = [];
    $resumePath = null;
    
    // Upload Resume (Required)
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['resume'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['pdf', 'doc', 'docx'];
        
        if (!in_array($fileExt, $allowedExt)) {
            echo json_encode(['success' => false, 'message' => 'Resume must be PDF, DOC, or DOCX']);
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Resume file size must not exceed 5MB']);
            exit;
        }
        
        $fileName = 'resume_' . time() . '_' . uniqid() . '.' . $fileExt;
        $filePath = $uploadBaseDir . '/' . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $resumePath = 'uploads/applicants/' . $fileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload resume']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Resume is required']);
        exit;
    }
    
    // Collect form data (no password required from user)
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'middle_name' => trim($_POST['middle_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => bin2hex(random_bytes(16)), // Generate random password (not used for login)
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
        'cover_letter' => trim($_POST['cover_letter'] ?? ''),
        'skills' => trim($_POST['skills'] ?? ''),
        'experience_years' => !empty($_POST['experience_years']) ? floatval($_POST['experience_years']) : null,
        'expected_salary' => !empty($_POST['expected_salary']) ? floatval($_POST['expected_salary']) : null,
        'available_start_date' => $_POST['available_start_date'] ?? null,
        'reference_name' => trim($_POST['reference_name'] ?? ''),
        'reference_contact' => trim($_POST['reference_contact'] ?? ''),
        'reference_relationship' => trim($_POST['reference_relationship'] ?? ''),
        'resume_path' => $resumePath
    ];
    
    $result = $applicantController->register($data);
    
    if ($result['success']) {
        $applicantId = $result['applicant_id'];
        
        // Upload Cover Letter (Optional)
        if (isset($_FILES['cover_letter_file']) && $_FILES['cover_letter_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['cover_letter_file'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExt = ['pdf', 'doc', 'docx'];
            
            if (in_array($fileExt, $allowedExt) && $file['size'] <= 5 * 1024 * 1024) {
                $fileName = 'cover_letter_' . $applicantId . '_' . time() . '.' . $fileExt;
                $filePath = $uploadBaseDir . '/' . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $documentPath = 'uploads/applicants/' . $fileName;
                    // Insert into applicant_documents table
                    $stmt = $db->prepare("INSERT INTO applicant_documents (applicant_id, document_type, document_name, document_path, file_size) VALUES (?, 'cover_letter', ?, ?, ?)");
                    $stmt->execute([$applicantId, $file['name'], $documentPath, $file['size']]);
                }
            }
        }
        
        // Upload Additional Documents (Optional, Multiple)
        if (isset($_FILES['additional_documents']) && is_array($_FILES['additional_documents']['name'])) {
            $files = $_FILES['additional_documents'];
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < min($fileCount, 5); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $fileExt = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    $allowedExt = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                    
                    if (in_array($fileExt, $allowedExt) && $files['size'][$i] <= 5 * 1024 * 1024) {
                        $fileName = 'document_' . $applicantId . '_' . time() . '_' . $i . '.' . $fileExt;
                        $filePath = $uploadBaseDir . '/' . $fileName;
                        
                        if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                            $documentPath = 'uploads/applicants/' . $fileName;
                            // Determine document type based on extension
                            $docType = in_array($fileExt, ['jpg', 'jpeg', 'png']) ? 'portfolio' : 'certificate';
                            
                            // Insert into applicant_documents table
                            $stmt = $db->prepare("INSERT INTO applicant_documents (applicant_id, document_type, document_name, document_path, file_size) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$applicantId, $docType, $files['name'][$i], $documentPath, $files['size'][$i]]);
                        }
                    }
                }
            }
        }
    }
    
    // TODO: Send email notification to applicant here
    // Email should contain: Thank you message, what to expect next
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Applicant registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during registration']);
}


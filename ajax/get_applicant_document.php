<?php
require_once '../shared/config.php';

// Check if user is logged in (HR/Admin)
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die('Unauthorized access');
}

// Check user role (only admin and hr can view documents)
if (!in_array($_SESSION['user_type'], ['admin', 'hr'])) {
    http_response_code(403);
    die('Insufficient permissions');
}

try {
    $db = getDBConnection();
    if (!$db) {
        http_response_code(500);
        die('Database connection failed');
    }
    
    $applicantId = isset($_GET['applicant_id']) ? intval($_GET['applicant_id']) : 0;
    $documentType = isset($_GET['type']) ? $_GET['type'] : 'resume';
    
    if ($applicantId <= 0) {
        http_response_code(400);
        die('Invalid applicant ID');
    }
    
    // Get document path based on type
    if ($documentType === 'resume') {
        // Get resume from applicants table
        $query = "SELECT resume_path, first_name, last_name FROM applicants WHERE applicant_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$applicantId]);
        $applicant = $stmt->fetch();
        
        if (!$applicant || empty($applicant['resume_path'])) {
            http_response_code(404);
            die('Resume not found');
        }
        
        $filePath = '../' . $applicant['resume_path'];
        $fileName = $applicant['first_name'] . '_' . $applicant['last_name'] . '_Resume' . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
        
    } else {
        // Get other documents from applicant_documents table
        $query = "SELECT ad.document_path, ad.document_name, a.first_name, a.last_name 
                  FROM applicant_documents ad
                  JOIN applicants a ON ad.applicant_id = a.applicant_id
                  WHERE ad.applicant_id = ? AND ad.document_type = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$applicantId, $documentType]);
        $document = $stmt->fetch();
        
        if (!$document) {
            http_response_code(404);
            die('Document not found');
        }
        
        $filePath = '../' . $document['document_path'];
        $fileName = $document['document_name'];
    }
    
    // Check if file exists
    if (!file_exists($filePath)) {
        http_response_code(404);
        die('File not found on server');
    }
    
    // Determine content type
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $contentTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png'
    ];
    
    $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
    
    // Set headers for file download/view
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: inline; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Output file
    readfile($filePath);
    exit;
    
} catch (Exception $e) {
    error_log("Error fetching document: " . $e->getMessage());
    http_response_code(500);
    die('Error retrieving document');
}

<?php
require_once '../shared/config.php';
require_once '../controllers/ApplicantController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

// Check user role (only admin and hr can view documents)
if (!in_array($_SESSION['user_type'], ['admin', 'hr'])) {
  echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
  exit;
}

try {
  $db = getDBConnection();
  if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
  }

  $applicantId = isset($_GET['applicant_id']) ? intval($_GET['applicant_id']) : 0;

  if ($applicantId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid applicant ID']);
    exit;
  }

  // Get resume from applicants table
  $query = "SELECT resume_path FROM applicants WHERE applicant_id = ?";
  $stmt = $db->prepare($query);
  $stmt->execute([$applicantId]);
  $applicant = $stmt->fetch();

  $documents = [];

  // Add resume if exists
  if ($applicant && !empty($applicant['resume_path'])) {
    $documents[] = [
      'type' => 'resume',
      'name' => 'Resume/CV',
      'path' => $applicant['resume_path'],
      'url' => '../ajax/get_applicant_document.php?applicant_id=' . $applicantId . '&type=resume'
    ];
  }

  // Get other documents from applicant_documents table
  $docQuery = "SELECT document_id, document_type, document_name, document_path, file_size, uploaded_at 
                 FROM applicant_documents 
                 WHERE applicant_id = ?
                 ORDER BY uploaded_at DESC";
  $stmt = $db->prepare($docQuery);
  $stmt->execute([$applicantId]);
  $otherDocs = $stmt->fetchAll();

  foreach ($otherDocs as $doc) {
    $documents[] = [
      'type' => $doc['document_type'],
      'name' => $doc['document_name'],
      'path' => $doc['document_path'],
      'size' => $doc['file_size'],
      'uploaded_at' => $doc['uploaded_at'],
      'url' => '../ajax/get_applicant_document.php?applicant_id=' . $applicantId . '&type=' . $doc['document_type']
    ];
  }

  echo json_encode([
    'success' => true,
    'documents' => $documents
  ]);

} catch (Exception $e) {
  error_log("Error fetching documents: " . $e->getMessage());
  echo json_encode(['success' => false, 'message' => 'Error retrieving documents']);
}

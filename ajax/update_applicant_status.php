<?php
require_once '../shared/config.php';
require_once '../controllers/ApplicantController.php';
require_once '../shared/EmailService.php';

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
    $db = getDBConnection();
    if (!$db) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $applicantController = new ApplicantController($db);
    
    $applicantId = intval($_POST['applicant_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    $performedBy = $_SESSION['user_id'];
    $notes = trim($_POST['notes'] ?? '');
    $interviewDate = trim($_POST['interview_date'] ?? '');
    $interviewLocation = trim($_POST['interview_location'] ?? '');
    
    if (empty($applicantId) || empty($newStatus)) {
        echo json_encode(['success' => false, 'message' => 'Applicant ID and status are required']);
        exit;
    }
    
    // Update status
    $result = $applicantController->updateStatus($applicantId, $newStatus, $performedBy, $notes);
    
    if ($result['success']) {
        // Get applicant details for email
        $applicant = $applicantController->getProfile($applicantId);
        
        // Debug: Log the structure of the response
        error_log("Applicant profile response - Success: " . ($applicant['success'] ? 'true' : 'false'));
        
        if ($applicant['success'] && !empty($applicant['applicant']['email'])) {
            $applicantData = $applicant['applicant'];
            
            // Log applicant email
            error_log("✉️ Applicant email: " . $applicantData['email'] . ", New Status: " . $newStatus);
            
            // Send email for specific status changes
            $sendEmailStatuses = ['reviewing', 'interview_scheduled', 'interviewed', 'accepted', 'rejected', 'hired'];
            
            if (in_array($newStatus, $sendEmailStatuses)) {
                error_log("Attempting to send email to: " . $applicantData['email']);
                
                $emailResult = EmailService::sendStatusUpdateEmail(
                    $applicantData,
                    $newStatus,
                    $notes,
                    $interviewDate,
                    $interviewLocation
                );
                
                // Log email result
                if ($emailResult['success']) {
                    error_log("✅ Status update email sent successfully to: " . $applicantData['email']);
                    $result['email_sent'] = true;
                    $result['email_message'] = 'Notification email sent';
                } else {
                    error_log("❌ Failed to send status update email: " . $emailResult['message']);
                    $result['email_sent'] = false;
                    $result['email_error'] = $emailResult['message'];
                }
            } else {
                error_log("Status '$newStatus' does not trigger email notification");
            }
        } else {
            error_log("Could not get applicant profile or email is empty");
        }
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Update applicant status error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating status']);
}

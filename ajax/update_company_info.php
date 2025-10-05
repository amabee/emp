<?php
header('Content-Type: application/json');
session_start();
require_once '../shared/config.php';
require_once '../controllers/SystemSettingsController.php';
require_once '../controllers/SystemLogger.php';

// Check if user is logged in and has admin access
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $controller = new SystemSettingsController();
    
    // Handle logo upload if present
    $logoFilename = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = $controller->uploadLogo($_FILES['logo']);
        if ($uploadResult['success']) {
            $logoFilename = $uploadResult['filename'];
        } else {
            echo json_encode($uploadResult);
            exit();
        }
    }

    // Prepare update data
    $updateData = [
        'name' => sanitize($_POST['company_name'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'contact_number' => sanitize($_POST['contact_number'] ?? ''),
        'website' => sanitize($_POST['website'] ?? ''),
        'address' => sanitize($_POST['address'] ?? ''),
        'logo' => $logoFilename
    ];

    // Validate required fields
    if (empty($updateData['name'])) {
        echo json_encode(['success' => false, 'message' => 'Company name is required']);
        exit();
    }

    $result = $controller->updateCompanyInfo($updateData);
    
    // Log the action if successful
    if ($result['success'] && isset($_SESSION['user_id'])) {
        $logger = new SystemLogger();
        $logger->log($_SESSION['user_id'], 'COMPANY_UPDATE', "Company information updated - Name: {$updateData['name']}");
    }
    
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

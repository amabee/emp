<?php
header('Content-Type: application/json');
require_once '../shared/config.php';
require_once '../controllers/SystemSettingsController.php';

// Public endpoint - no authentication required
try {
    $controller = new SystemSettingsController();
    $companyInfo = $controller->getCompanyInfo();
    
    // Only return public-safe information
    $publicInfo = [
        'company_name' => $companyInfo['name'] ?? 'Employee Management System',
        'company_address' => $companyInfo['address'] ?? '',
        'company_email' => $companyInfo['email'] ?? '',
        'company_phone' => $companyInfo['contact_number'] ?? '',
        'company_website' => $companyInfo['website'] ?? '',
        'company_logo' => $companyInfo['logo'] ?? '../assets/img/default-logo.png',
        'company_description' => $companyInfo['company_description'] ?? 'Welcome to our Employee Management System'
    ];
    
    echo json_encode([
        'success' => true,
        'company' => $publicInfo
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load company information',
        'company' => [
            'company_name' => 'Employee Management System',
            'company_description' => 'Welcome to our Employee Management System'
        ]
    ]);
}
?>

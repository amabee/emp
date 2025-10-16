<?php
header('Content-Type: application/json');

require_once '../shared/config.php';
require_once '../controllers/OrganizationController.php';

try {
    $controller = new OrganizationController();
    $departments = $controller->getAllDepartments();
    
    // Normalize the department data
    $departments = array_map(function($d) {
        return [
            'department_id' => $d['department_id'] ?? $d['id'] ?? null,
            'department_name' => $d['department_name'] ?? $d['name'] ?? 'Unknown'
        ];
    }, $departments);
    
    echo json_encode([
        'success' => true,
        'departments' => $departments
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching public departments: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch departments'
    ]);
}

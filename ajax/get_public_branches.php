<?php
header('Content-Type: application/json');

require_once '../shared/config.php';
require_once '../controllers/OrganizationController.php';

try {
    $controller = new OrganizationController();
    $branches = $controller->getAllBranches();
    
    // Normalize the branch data
    $branches = array_map(function($b) {
        return [
            'branch_id' => $b['branch_id'] ?? $b['id'] ?? null,
            'branch_name' => $b['branch_name'] ?? $b['name'] ?? 'Unknown'
        ];
    }, $branches);
    
    echo json_encode([
        'success' => true,
        'branches' => $branches
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching public branches: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch branches'
    ]);
}

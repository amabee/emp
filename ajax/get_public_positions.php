<?php
header('Content-Type: application/json');

require_once '../shared/config.php';
require_once '../controllers/OrganizationController.php';

try {
    $controller = new OrganizationController();
    $positions = $controller->getAllPositions();
    
    // Normalize the position data
    $positions = array_map(function($p) {
        if (isset($p['title']) && !isset($p['name'])) {
            $p['name'] = $p['title'];
        }
        if (isset($p['title']) && !isset($p['position_name'])) {
            $p['position_name'] = $p['title'];
        }
        return [
            'position_id' => $p['position_id'] ?? $p['id'] ?? null,
            'position_name' => $p['position_name'] ?? $p['name'] ?? $p['title'] ?? 'Unknown'
        ];
    }, $positions);
    
    echo json_encode([
        'success' => true,
        'positions' => $positions
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching public positions: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch positions'
    ]);
}

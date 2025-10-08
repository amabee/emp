<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../shared/config.php';
require_once __DIR__ . '/../controllers/EmployeeManagementController.php';

if (!ob_get_level()) ob_start();
function send_json($payload) { if (ob_get_length() !== false) { @ob_clean(); } echo json_encode($payload); exit(); }

// Check if user is logged in
if (!isLoggedIn()) {
    send_json(['success' => false, 'message' => 'Unauthorized access']);
}

try {
    $controller = new EmployeeManagementController();
    
    // Determine if Select2 remote is requested
    $format = $_GET['format'] ?? '';
    if ($format === 'select2') {
        $q = isset($_GET['q']) ? sanitize($_GET['q']) : '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = max(10, intval($_GET['per_page'] ?? 20));

        $res = $controller->getEmployeesPaginated(['search' => $q, 'department' => $_GET['department'] ?? null, 'page' => $page, 'per_page' => $perPage]);
        $results = array_map(function($e) {
            return ['id' => $e['id'], 'text' => $e['name']];
        }, $res['results']);
        $more = ($page * $perPage) < $res['total'];
        send_json(['results' => $results, 'pagination' => ['more' => $more]]);
    } else {
        // Legacy response: full lists for internal UI
        $filters = [];
        if (!empty($_GET['search'])) {
            $filters['search'] = sanitize($_GET['search']);
        }
        if (!empty($_GET['department'])) {
            $filters['department'] = (int)$_GET['department'];
        }

        // Get employees, departments, and positions
        $employees = $controller->getAllEmployees($filters);
        $departments = $controller->getAllDepartments();
        $positions = $controller->getAllPositions();

        send_json(['success' => true, 'employees' => $employees, 'departments' => $departments, 'positions' => $positions]);
    }

} catch (Exception $e) {
    send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage(), 'employees' => [], 'departments' => [], 'positions' => []]);
}
?>

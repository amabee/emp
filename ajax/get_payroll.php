<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    require_once __DIR__ . '/../controllers/PayrollController.php';
    $pc = new PayrollController();

    $pay_period = $_GET['pay_period'] ?? date('Y-m');
    $filters = [];
    if (!empty($_GET['department'])) $filters['department'] = $_GET['department'];
    if (!empty($_GET['employee_ids'])) $filters['employee_ids'] = array_map('intval', explode(',', $_GET['employee_ids']));

    $rows = $pc->generatePayroll($pay_period, $filters);
    echo json_encode(['success' => true, 'data' => $rows]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

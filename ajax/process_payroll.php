<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    require_once __DIR__ . '/../controllers/PayrollController.php';
    require_once __DIR__ . '/../controllers/SystemLogger.php';
    $pc = new PayrollController();
    $logger = new SystemLogger();

    $pay_period = $_POST['pay_period'] ?? null;
    if (empty($pay_period)) {
        $pay_period = date('Y-m');
    }
    
    $department = $_POST['department'] ?? null;
    $employee_ids = [];
    // Accept employee_ids either as CSV string in 'employee_ids' or as array 'employees' (from form arrays)
    if (!empty($_POST['employee_ids'])) {
        if (is_array($_POST['employee_ids'])) {
            $employee_ids = array_map('intval', $_POST['employee_ids']);
        } else {
            $employee_ids = array_map('intval', explode(',', $_POST['employee_ids']));
        }
    } elseif (!empty($_POST['employees']) && is_array($_POST['employees'])) {
        $employee_ids = array_map('intval', $_POST['employees']);
    }

    // Debug log for backend
    error_log("process_payroll.php - pay_period: $pay_period, department: $department, employee_ids: " . json_encode($employee_ids) . ", pay_date: $pay_date");

    $filters = [];
    if ($department) $filters['department'] = $department;
    if (!empty($employee_ids)) $filters['employee_ids'] = $employee_ids;

    $processedBy = $_SESSION['user_id'] ?? null;
    
    // Get pay_date from POST data
    $pay_date = $_POST['pay_date'] ?? null;
    if (!empty($pay_date)) {
        // Validate the date format
        $dateTime = DateTime::createFromFormat('Y-m-d', $pay_date);
        if (!$dateTime || $dateTime->format('Y-m-d') !== $pay_date) {
            $pay_date = null; // Invalid date, set to null
        }
    }

    // options: updateExisting may be passed as 'updateExisting' => true
    $options = [];
    if (isset($_POST['updateExisting'])) $options['updateExisting'] = boolval($_POST['updateExisting']);

    try {
        $res = $pc->processPayroll($pay_period, $filters, $processedBy, $options, $pay_date);
        try { $logger->logAuthAction($processedBy, 'PROCESS PAYROLL', "Processed payroll for {$pay_period} - {$res['inserted']} inserted, {$res['updated']} updated"); } catch (Exception $e) {}
        // Also return which employees were stored for this period for debugging/visibility
        try {
            $periodStart = (new DateTime($pay_period . '-01'))->format('Y-m-d');
            $db = getDBConnection();
            if (!empty($employee_ids)) {
                $placeholders = implode(',', array_fill(0, count($employee_ids), '?'));
                $q = $db->prepare("SELECT employee_id FROM payroll WHERE period_start = ? AND employee_id IN ($placeholders)");
                $params = array_merge([$periodStart], $employee_ids);
                $q->execute($params);
            } else {
                $q = $db->prepare("SELECT employee_id FROM payroll WHERE period_start = ?");
                $q->execute([$periodStart]);
            }
            $processedEmployees = $q->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $_e) {
            $processedEmployees = [];
        }

        echo json_encode(['success' => true, 'message' => "Processed payroll for {$pay_period}", 'inserted' => $res['inserted'], 'updated' => $res['updated'], 'processed_employee_ids' => $processedEmployees]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

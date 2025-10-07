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

    $pay_period = $_POST['pay_period'] ?? date('Y-m');
    $department = $_POST['department'] ?? null;

    $filters = [];
    if ($department) $filters['department'] = $department;

    $rows = $pc->generatePayroll($pay_period, $filters);

    // Ensure payroll_records table exists (simple schema)
    $db = getDBConnection();
    $db->exec("CREATE TABLE IF NOT EXISTS payroll_records (
        payroll_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        pay_period VARCHAR(7) NOT NULL,
        basic_salary DECIMAL(12,2) DEFAULT 0,
        allowances_total DECIMAL(12,2) DEFAULT 0,
        deductions_total DECIMAL(12,2) DEFAULT 0,
        overtime_total DECIMAL(12,2) DEFAULT 0,
        gross_pay DECIMAL(12,2) DEFAULT 0,
        net_pay DECIMAL(12,2) DEFAULT 0,
        status VARCHAR(32) DEFAULT 'pending',
        processed_by INT NULL,
        processed_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Insert rows
    $ins = $db->prepare("INSERT INTO payroll_records (employee_id, pay_period, basic_salary, allowances_total, deductions_total, overtime_total, gross_pay, net_pay, status, processed_by, processed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'processed', ?, NOW())");
    $inserted = 0;
    $processedBy = $_SESSION['user_id'] ?? null;
    foreach ($rows as $r) {
        $ins->execute([
            $r['employee_id'],
            $pay_period,
            $r['basic_salary'],
            $r['allowances_total'],
            $r['deductions_total'],
            $r['overtime_total'],
            $r['gross_pay'],
            $r['net_pay'],
            $processedBy
        ]);
        $inserted++;
    }

    // Log processing
    try { $logger->logAuthAction($processedBy, 'PROCESS PAYROLL', "Processed payroll for {$pay_period} - {$inserted} records"); } catch (Exception $e) {}

    echo json_encode(['success' => true, 'message' => "Processed payroll for {$pay_period}", 'inserted' => $inserted]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

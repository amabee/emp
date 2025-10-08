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

    // Only use default period if no period is provided at all
    $pay_period = $_GET['pay_period'] ?? null;
    if (empty($pay_period)) {
        $pay_period = date('Y-m');
    }
    
    $filters = [];
    if (!empty($_GET['department'])) $filters['department'] = $_GET['department'];
    if (!empty($_GET['employee_ids'])) $filters['employee_ids'] = array_map('intval', explode(',', $_GET['employee_ids']));

    // Debug log for backend
    error_log("get_payroll.php - pay_period: $pay_period, filters: " . json_encode($filters));

    // If there are persisted payroll rows for the requested period, return them instead of generating on-the-fly
    $db = getDBConnection();
    $periodStart = (new DateTime($pay_period . '-01'))->format('Y-m-d');

    // Check for persisted payroll rows
    $check = $db->prepare("SELECT COUNT(*) as cnt FROM payroll WHERE period_start = ?");
    $check->execute([$periodStart]);
    $cnt = intval($check->fetchColumn());

    if ($cnt > 0) {
        // Fetch payroll rows with aggregated allowances/deductions
    $sql = "SELECT p.payroll_id, p.employee_id, CONCAT(e.first_name, ' ', e.last_name) as employee_name, d.department_name, e.department_id as department_id,
        p.period_start, p.period_end, p.basic_salary, p.pay_date,
        COALESCE((SELECT SUM(amount) FROM payroll_allowance pa WHERE pa.payroll_id = p.payroll_id),0) as allowances_total,
        COALESCE((SELECT SUM(amount) FROM payroll_deduction pd WHERE pd.payroll_id = p.payroll_id),0) as deductions_total,
        COALESCE((SELECT SUM(hours * rate) FROM overtime_records o WHERE o.employee_id = p.employee_id AND o.date BETWEEN p.period_start AND p.period_end AND o.approved_by IS NOT NULL),0) as overtime_total,
        (p.basic_salary + COALESCE((SELECT SUM(amount) FROM payroll_allowance pa WHERE pa.payroll_id = p.payroll_id),0) + COALESCE((SELECT SUM(hours * rate) FROM overtime_records o WHERE o.employee_id = p.employee_id AND o.date BETWEEN p.period_start AND p.period_end AND o.approved_by IS NOT NULL),0)) as gross_pay,
        p.net_pay, p.processed_by, p.processed_at
        FROM payroll p
        LEFT JOIN employees e ON p.employee_id = e.employee_id
        LEFT JOIN department d ON e.department_id = d.department_id
        WHERE p.period_start = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$periodStart]);
        $persisted = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Apply filters (department / specific employees) if provided
        $out = [];
        foreach ($persisted as $r) {
            // department filter may be a numeric id or a name string
            if (!empty($filters['department'])) {
                if (is_numeric($filters['department'])) {
                    if (intval($r['department_id']) !== intval($filters['department'])) continue;
                } else {
                    if ($r['department_name'] !== $filters['department']) continue;
                }
            }
            if (!empty($filters['employee_ids']) && is_array($filters['employee_ids']) && !in_array(intval($r['employee_id']), $filters['employee_ids'])) continue;
            $out[] = [
                'id' => $r['payroll_id'],
                'payroll_id' => $r['payroll_id'],
                'employee_id' => $r['employee_id'],
                'employee' => $r['employee_name'],
                'employee_name' => $r['employee_name'],
                'department' => $r['department_name'],
                'department_name' => $r['department_name'],
                'pay_period' => $r['period_start'],
                'period_start' => $r['period_start'],
                'period_end' => $r['period_end'],
                'basic_salary' => floatval($r['basic_salary']),
                'allowances' => floatval($r['allowances_total']),
                'allowances_total' => floatval($r['allowances_total']),
                'deductions' => floatval($r['deductions_total']),
                'deductions_total' => floatval($r['deductions_total']),
                'gross_pay' => floatval($r['gross_pay']),
                'net_pay' => floatval($r['net_pay']),
                'status' => $r['pay_date'] ? 'paid' : 'processed', // Use pay_date to determine if actually paid
                'pay_date' => $r['pay_date'] // Use the actual pay_date column
            ];
        }

        echo json_encode(['success' => true, 'data' => $out, 'persisted' => true]);
    } else {
        // Fallback to on-the-fly generation
        $rows = $pc->generatePayroll($pay_period, $filters);
        
        // Map the generated rows to match frontend expectations
        $mappedRows = [];
        foreach ($rows as $r) {
            $mappedRows[] = [
                'id' => null, // No persisted ID yet
                'payroll_id' => null,
                'employee_id' => $r['employee_id'],
                'employee' => $r['employee_name'],
                'employee_name' => $r['employee_name'],
                'department' => $r['department_name'],
                'department_name' => $r['department_name'],
                'pay_period' => $pay_period . '-01',
                'period_start' => $pay_period . '-01',
                'period_end' => date('Y-m-t', strtotime($pay_period . '-01')),
                'basic_salary' => floatval($r['basic_salary']),
                'allowances' => floatval($r['allowances_total']),
                'allowances_total' => floatval($r['allowances_total']),
                'deductions' => floatval($r['deductions_total']),
                'deductions_total' => floatval($r['deductions_total']),
                'gross_pay' => floatval($r['gross_pay']),
                'net_pay' => floatval($r['net_pay']),
                'status' => 'preview', // Mark as preview for generated data
                'pay_date' => null // No payment date for preview data
            ];
        }
        
        echo json_encode(['success' => true, 'data' => $mappedRows, 'persisted' => false]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>

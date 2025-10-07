<?php
require_once __DIR__ . '/SystemLogger.php';

class PayrollController
{
    private $db;
    private $logger;

    public function __construct()
    {
        $this->db = getDBConnection();
        $this->logger = new SystemLogger();
        if (!$this->db) throw new Exception('DB connection failed');
    }

    /**
     * Compute payroll rows for a given pay period (format YYYY-MM)
     * Returns an array of rows with basic_salary, allowances_total, deductions_total, overtime_total, net_pay
     */
    public function generatePayroll($payPeriod, $filters = [])
    {
        // parse payPeriod into date range (assume full month)
        try {
            $start = new DateTime($payPeriod . '-01');
        } catch (Exception $e) {
            throw new Exception('Invalid pay period');
        }
        $end = (clone $start)->modify('last day of this month');

        // Load employees (filter by department or specific employees if provided)
        $params = [];
        $where = "WHERE e.employment_status = 1";
        if (!empty($filters['department'])) {
            $where .= " AND d.department_name = :dept";
            $params['dept'] = $filters['department'];
        }
        if (!empty($filters['employee_ids']) && is_array($filters['employee_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['employee_ids']), '?'));
            $where .= " AND e.employee_id IN ($placeholders)";
            $params = array_merge($params, $filters['employee_ids']);
        }

        $sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.last_name) as employee_name, COALESCE(e.basic_salary,0) as basic_salary, d.department_name
                FROM employees e
                LEFT JOIN department d ON e.department_id = d.department_id
                $where
                ORDER BY e.first_name, e.last_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Preload allowances per employee
        $allowStmt = $this->db->prepare("SELECT ea.employee_id, COALESCE(ea.allowance_amount,0) as amount FROM employee_allowance ea WHERE ea.is_active = 1");
        $allowStmt->execute();
        $allowRows = $allowStmt->fetchAll(PDO::FETCH_ASSOC);
        $allowMap = [];
        foreach ($allowRows as $r) {
            $allowMap[$r['employee_id']][] = floatval($r['amount']);
        }

        // Preload deductions per employee with amount type (join to deduction_type)
        $dedStmt = $this->db->prepare("SELECT ed.employee_id, COALESCE(ed.amount,0) as amount, COALESCE(dt.amount_type,'FIXED') as amount_type FROM employee_deduction ed LEFT JOIN deduction_type dt ON ed.deduction_type_id = dt.deduction_type_id");
        $dedStmt->execute();
        $dedRows = $dedStmt->fetchAll(PDO::FETCH_ASSOC);
        $dedMap = [];
        foreach ($dedRows as $r) {
            // store as array of ['amount'=>float, 'type'=> 'PERCENTAGE'|'FIXED']
            $eid = $r['employee_id'];
            if (!isset($dedMap[$eid])) $dedMap[$eid] = [];
            $dedMap[$eid][] = ['amount' => floatval($r['amount']), 'type' => strtoupper($r['amount_type'] ?? 'FIXED')];
        }

        // Preload overtime for the date range
        $ovStmt = $this->db->prepare("SELECT employee_id, SUM(hours * rate) as ot_amount FROM overtime_records WHERE date BETWEEN :s AND :e AND approved_by IS NOT NULL GROUP BY employee_id");
        $ovStmt->execute(['s' => $start->format('Y-m-d'), 'e' => $end->format('Y-m-d')]);
        $ovRows = $ovStmt->fetchAll(PDO::FETCH_ASSOC);
        $otMap = [];
        foreach ($ovRows as $r) {
            $otMap[$r['employee_id']] = floatval($r['ot_amount']);
        }

        // Build payroll rows
        $rows = [];
        foreach ($employees as $emp) {
            $eid = $emp['employee_id'];
            $basic = floatval($emp['basic_salary'] ?? 0);
            $allowTotal = isset($allowMap[$eid]) ? array_sum($allowMap[$eid]) : 0.0;
            // compute deduction total: fixed amounts + percentage amounts applied to gross (basic + allowances)
            $dedTotal = 0.0;
            if (isset($dedMap[$eid])) {
                foreach ($dedMap[$eid] as $d) {
                    if ($d['type'] === 'PERCENTAGE') {
                        // percentage is applied to basic + allowances (not including overtime)
                        $baseForPercent = $basic + $allowTotal;
                        $dedTotal += ($d['amount'] / 100.0) * $baseForPercent;
                    } else {
                        $dedTotal += $d['amount'];
                    }
                }
            }
            $otTotal = isset($otMap[$eid]) ? $otMap[$eid] : 0.0;

            $gross = $basic + $allowTotal + $otTotal;
            $net = $gross - $dedTotal;

            $rows[] = [
                'employee_id' => $eid,
                'employee_name' => $emp['employee_name'],
                'department_name' => $emp['department_name'] ?? null,
                'basic_salary' => $basic,
                'allowances_total' => $allowTotal,
                'deductions_total' => $dedTotal,
                'overtime_total' => $otTotal,
                'gross_pay' => $gross,
                'net_pay' => $net
            ];
        }

        return $rows;
    }
}

?>

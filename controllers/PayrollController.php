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
        // parse payPeriod into date range - use custom dates if provided, otherwise full month
        if (!empty($filters['custom_start']) && !empty($filters['custom_end'])) {
            try {
                $start = new DateTime($filters['custom_start']);
                $end = new DateTime($filters['custom_end']);
            } catch (Exception $e) {
                throw new Exception('Invalid custom date range');
            }
        } else {
            try {
                $start = new DateTime($payPeriod . '-01');
            } catch (Exception $e) {
                throw new Exception('Invalid pay period');
            }
            $end = (clone $start)->modify('last day of this month');
        }

        // Load employees (filter by department or specific employees if provided)
        $params = [];
        $where = "WHERE e.employment_status = 1";
        if (!empty($filters['department'])) {
            // Accept either numeric department id or department name.
            if (is_numeric($filters['department'])) {
                $where .= " AND e.department_id = ?";
                $params[] = (int)$filters['department'];
            } else {
                // Filter by department name
                $where .= " AND d.department_name = ?";
                $params[] = $filters['department'];
            }
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
        
        // Debug logging
        error_log("PayrollController - generatePayroll: Found " . count($employees) . " employees for period " . $start->format('Y-m-d') . " to " . $end->format('Y-m-d'));
        foreach ($employees as $emp) {
            error_log("Employee: " . $emp['employee_name'] . " (ID: " . $emp['employee_id'] . ") - Basic Salary: " . $emp['basic_salary']);
        }

    // Preload allowances per employee
    // Note: some DB dumps don't have an `is_active` column on employee_allowance,
    // so select all assigned allowances and rely on application logic to mark active/inactive elsewhere.
    $allowStmt = $this->db->prepare("SELECT employee_id, COALESCE(allowance_amount,0) as amount FROM employee_allowance");
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

        // Get working days for the period
        $workingDays = $this->getWorkingDaysInPeriod($start, $end);
        $totalWorkingDays = count($workingDays);
        
        error_log("PayrollController - Period " . $start->format('Y-m-d') . " to " . $end->format('Y-m-d') . ": $totalWorkingDays working days");

        // Build payroll rows
        $rows = [];
        foreach ($employees as $emp) {
            $eid = $emp['employee_id'];
            $monthlyBasic = floatval($emp['basic_salary'] ?? 0);
            
            // Get attendance records for this employee in the period
            $attendanceStmt = $this->db->prepare("SELECT date, status FROM attendance WHERE employee_id = ? AND date BETWEEN ? AND ?");
            $attendanceStmt->execute([$eid, $start->format('Y-m-d'), $end->format('Y-m-d')]);
            $attendanceRecords = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Count present days
            $presentDays = 0;
            $lateDays = 0;
            foreach ($attendanceRecords as $record) {
                $status = strtolower($record['status'] ?? '');
                if (in_array($status, ['present', 'late', 'partial'])) {
                    $presentDays++;
                    if ($status === 'late') $lateDays++;
                }
            }
            
            $absentDays = $totalWorkingDays - $presentDays;
            
            // Calculate pro-rated salary based on attendance
            if ($totalWorkingDays > 0) {
                // For 15-day periods: divide monthly by 2
                // For custom periods: pro-rate based on working days vs full month (typically 22 working days)
                $monthlyWorkingDays = 22; // Standard assumption for PH
                $periodRatio = $totalWorkingDays / $monthlyWorkingDays;
                $expectedSalaryForPeriod = $monthlyBasic * $periodRatio;
                
                // Pro-rate based on attendance
                $attendanceRatio = $totalWorkingDays > 0 ? ($presentDays / $totalWorkingDays) : 0;
                $basic = $expectedSalaryForPeriod * $attendanceRatio;
            } else {
                $basic = 0; // No working days = no pay
            }
            
            $attendanceDetails = [
                'monthly_basic' => $monthlyBasic,
                'working_days' => $totalWorkingDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'attendance_ratio' => $attendanceRatio ?? 0,
                'expected_for_period' => $expectedSalaryForPeriod ?? 0,
                'daily_rate' => $totalWorkingDays > 0 ? ($expectedSalaryForPeriod / $totalWorkingDays) : 0
            ];
            
            error_log("PayrollController - Employee $eid ($monthlyBasic monthly): $presentDays/$totalWorkingDays days = " . number_format($basic, 2));
            
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
                'monthly_basic_salary' => $monthlyBasic, // Original monthly salary
                'basic_salary' => $basic, // Attendance-adjusted salary
                'allowances_total' => $allowTotal,
                'deductions_total' => $dedTotal,
                'overtime_total' => $otTotal,
                'gross_pay' => $gross,
                'net_pay' => $net,
                'attendance_details' => $attendanceDetails ?? []
            ];
        }

        return $rows;
    }


    
    /**
     * Get working days within a date range based on working calendar
     */
    private function getWorkingDaysInPeriod($startDate, $endDate)
    {
        // Get working calendar data
        $sql = "SELECT work_date, is_working FROM working_calendar 
                WHERE work_date BETWEEN ? AND ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            $calendarData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $workingDays = [];
            foreach ($calendarData as $day) {
                if (intval($day['is_working']) === 1) {
                    $workingDays[] = $day['work_date'];
                }
            }
            
            // If no calendar data, fall back to Monday-Friday as working days
            if (empty($workingDays)) {
                $current = clone $startDate;
                while ($current <= $endDate) {
                    $dayOfWeek = intval($current->format('N')); // 1=Monday, 7=Sunday
                    if ($dayOfWeek >= 1 && $dayOfWeek <= 5) { // Monday to Friday
                        $workingDays[] = $current->format('Y-m-d');
                    }
                    $current->modify('+1 day');
                }
            }
            
            return $workingDays;
        } catch (Exception $e) {
            // Fallback to Monday-Friday
            $workingDays = [];
            $current = clone $startDate;
            while ($current <= $endDate) {
                $dayOfWeek = intval($current->format('N'));
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                    $workingDays[] = $current->format('Y-m-d');
                }
                $current->modify('+1 day');
            }
            return $workingDays;
        }
    }

    /**
     * Persist generated payroll into canonical tables.
     * $options: ['updateExisting' => false]
     * Returns array: ['inserted' => n, 'updated' => m]
     */
    public function processPayroll($payPeriod, $filters = [], $processedBy = null, $options = [], $payDate = null, $startDate = null, $cutoffDate = null)
    {
        $opts = array_merge(['updateExisting' => false], $options ?: []);

        // parse period start/end - use custom dates if provided, otherwise default to full month
        if (!empty($startDate) && !empty($cutoffDate)) {
            $periodStart = $startDate;
            $periodEnd = $cutoffDate;
        } else {
            try {
                $startDt = new DateTime($payPeriod . '-01');
            } catch (Exception $e) {
                throw new Exception('Invalid pay period');
            }
            $periodStart = $startDt->format('Y-m-d');
            $periodEnd = (clone $startDt)->modify('last day of this month')->format('Y-m-d');
        }

        // Ensure payroll table has processed_by and processed_at columns; migrate existing rows
        $schemaStmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payroll' AND COLUMN_NAME = 'processed_by'");
        $schemaStmt->execute();
        $hasProcessedBy = intval($schemaStmt->fetchColumn());
        if (!$hasProcessedBy) {
            $this->db->exec("ALTER TABLE payroll ADD COLUMN processed_by INT DEFAULT NULL, ADD COLUMN processed_at DATETIME DEFAULT NULL");
            // For existing rows, set processed_at to created_at so there's a timestamp (processed_by left NULL)
            $this->db->exec("UPDATE payroll SET processed_at = created_at WHERE processed_at IS NULL");
        }

        // Generate rows - pass custom date range if available
        $generateFilters = $filters;
        if (!empty($startDate) && !empty($cutoffDate)) {
            $generateFilters['custom_start'] = $startDate;
            $generateFilters['custom_end'] = $cutoffDate;
        }
        $rows = $this->generatePayroll($payPeriod, $generateFilters);

        $inserted = 0;
        $updated = 0;

        try {
            $this->db->beginTransaction();

            // Prepare statements
            $checkStmt = $this->db->prepare("SELECT payroll_id FROM payroll WHERE employee_id = ? AND period_start = ?");
            $insertPayroll = $this->db->prepare("INSERT INTO payroll (employee_id, period_start, period_end, basic_salary, net_pay, pay_date, processed_by, processed_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $updatePayroll = $this->db->prepare("UPDATE payroll SET basic_salary = ?, net_pay = ?, pay_date = ?, created_at = created_at WHERE payroll_id = ?");

            $insertAllowance = $this->db->prepare("INSERT INTO payroll_allowance (payroll_id, allowance_id, amount) VALUES (?, ?, ?)");
            $deleteAllowance = $this->db->prepare("DELETE FROM payroll_allowance WHERE payroll_id = ?");

            $insertDeduction = $this->db->prepare("INSERT INTO payroll_deduction (payroll_id, deduction_type_id, amount) VALUES (?, ?, ?)");
            $deleteDeduction = $this->db->prepare("DELETE FROM payroll_deduction WHERE payroll_id = ?");

            $empAllowStmt = $this->db->prepare("SELECT allowance_id, COALESCE(allowance_amount,0) as amount FROM employee_allowance WHERE employee_id = ?");
            $empDedStmt = $this->db->prepare("SELECT ed.deduction_type_id, COALESCE(ed.amount,0) as amount, COALESCE(dt.amount_type,'FIXED') as amount_type FROM employee_deduction ed LEFT JOIN deduction_type dt ON ed.deduction_type_id = dt.deduction_type_id WHERE ed.employee_id = ?");

            foreach ($rows as $r) {
                $eid = $r['employee_id'];

                $checkStmt->execute([$eid, $periodStart]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    if ($opts['updateExisting']) {
                        $payrollId = $existing['payroll_id'];
                        // update master
                        $updatePayroll->execute([$r['basic_salary'], $r['net_pay'], $payDate, $payrollId]);
                        // replace child rows
                        $deleteAllowance->execute([$payrollId]);
                        $deleteDeduction->execute([$payrollId]);
                        $updated++;
                    } else {
                        // skip
                        continue;
                    }
                } else {
                    // insert master row
                    $insertPayroll->execute([$eid, $periodStart, $periodEnd, $r['basic_salary'], $r['net_pay'], $payDate, $processedBy]);
                    $payrollId = $this->db->lastInsertId();
                    $inserted++;
                }

                // Insert allowances
                $empAllowStmt->execute([$eid]);
                $allRows = $empAllowStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allRows as $a) {
                    if (floatval($a['amount']) == 0) continue;
                    $insertAllowance->execute([$payrollId, $a['allowance_id'], $a['amount']]);
                }

                // Insert deductions (compute percentage same as generatePayroll)
                $empDedStmt->execute([$eid]);
                $dedRows = $empDedStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($dedRows as $d) {
                    $amt = floatval($d['amount']);
                    $type = strtoupper($d['amount_type'] ?? 'FIXED');
                    if ($type === 'PERCENTAGE') {
                        $allowTotal = $r['allowances_total'] ?? 0;
                        $baseForPercent = $r['basic_salary'] + $allowTotal;
                        $amt = ($amt / 100.0) * $baseForPercent;
                    }
                    if ($amt == 0) continue;
                    $insertDeduction->execute([$payrollId, $d['deduction_type_id'], $amt]);
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            try { $this->db->rollBack(); } catch (Exception $_) {}
            throw $e;
        }

        return ['inserted' => $inserted, 'updated' => $updated];
    }
}

?>

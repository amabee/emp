<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Check if HR user has branch restriction
    $branchCondition = "";
    $branchParams = [];
    if (isHRWithBranchRestriction()) {
        $branchCondition = " AND e.branch_id = ?";
        $branchParams[] = $user_branch_id;
    }
    
    // Get current month/year
    $currentMonth = date('Y-m');
    $currentYear = date('Y');
    
    // Total payroll amount for current year (filtered by branch if HR)
    $totalPayrollSql = "SELECT COALESCE(SUM(p.net_pay), 0) as total_payroll 
                        FROM payroll p 
                        JOIN employees e ON p.employee_id = e.employee_id 
                        WHERE YEAR(p.period_start) = ?" . $branchCondition;
    $totalPayrollStmt = $db->prepare($totalPayrollSql);
    $totalPayrollStmt->execute(array_merge([$currentYear], $branchParams));
    $totalPayroll = $totalPayrollStmt->fetchColumn();
    
    // Employees paid (distinct employees with payroll records this year, filtered by branch if HR)
    $employeesPaidSql = "SELECT COUNT(DISTINCT p.employee_id) as employees_paid 
                         FROM payroll p 
                         JOIN employees e ON p.employee_id = e.employee_id 
                         WHERE YEAR(p.period_start) = ?" . $branchCondition;
    $employeesPaidStmt = $db->prepare($employeesPaidSql);
    $employeesPaidStmt->execute(array_merge([$currentYear], $branchParams));
    $employeesPaid = $employeesPaidStmt->fetchColumn();
    
    // Pending payroll (employees without payroll for current month, filtered by branch if HR)
    $totalEmployeesSql = "SELECT COUNT(*) as total_employees 
                          FROM employees e 
                          WHERE e.employment_status = 1" . $branchCondition;
    $totalEmployeesStmt = $db->prepare($totalEmployeesSql);
    $totalEmployeesStmt->execute($branchParams);
    $totalEmployees = $totalEmployeesStmt->fetchColumn();
    
    $currentMonthPaidSql = "SELECT COUNT(DISTINCT p.employee_id) as current_month_paid 
                            FROM payroll p 
                            JOIN employees e ON p.employee_id = e.employee_id 
                            WHERE DATE_FORMAT(p.period_start, '%Y-%m') = ?" . $branchCondition;
    $currentMonthPaidStmt = $db->prepare($currentMonthPaidSql);
    $currentMonthPaidStmt->execute(array_merge([$currentMonth], $branchParams));
    $currentMonthPaid = $currentMonthPaidStmt->fetchColumn();
    
    $pending = $totalEmployees - $currentMonthPaid;
    
    // Current period
    $currentPeriod = date('M Y');
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_payroll' => floatval($totalPayroll),
            'employees_paid' => intval($employeesPaid),
            'pending' => intval($pending),
            'current_period' => $currentPeriod
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

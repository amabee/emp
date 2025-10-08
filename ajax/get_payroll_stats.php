<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Get current month/year
    $currentMonth = date('Y-m');
    $currentYear = date('Y');
    
    // Total payroll amount for current year
    $totalPayrollStmt = $db->prepare("SELECT COALESCE(SUM(net_pay), 0) as total_payroll FROM payroll WHERE YEAR(period_start) = ?");
    $totalPayrollStmt->execute([$currentYear]);
    $totalPayroll = $totalPayrollStmt->fetchColumn();
    
    // Employees paid (distinct employees with payroll records this year)
    $employeesPaidStmt = $db->prepare("SELECT COUNT(DISTINCT employee_id) as employees_paid FROM payroll WHERE YEAR(period_start) = ?");
    $employeesPaidStmt->execute([$currentYear]);
    $employeesPaid = $employeesPaidStmt->fetchColumn();
    
    // Pending payroll (employees without payroll for current month)
    $totalEmployeesStmt = $db->prepare("SELECT COUNT(*) as total_employees FROM employees WHERE employment_status = 1");
    $totalEmployeesStmt->execute();
    $totalEmployees = $totalEmployeesStmt->fetchColumn();
    
    $currentMonthPaidStmt = $db->prepare("SELECT COUNT(DISTINCT employee_id) as current_month_paid FROM payroll WHERE DATE_FORMAT(period_start, '%Y-%m') = ?");
    $currentMonthPaidStmt->execute([$currentMonth]);
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

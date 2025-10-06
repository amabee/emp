<?php
header('Content-Type: application/json');
require_once '../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

try {
    require_once __DIR__ . '/../controllers/LeaveController.php';
    $leaveController = new LeaveController();
    
    // Determine which employee balances to fetch based on user role
    $allowedTypes = ['admin', 'supervisor', 'hr'];
    
    if (in_array($_SESSION['user_type'], $allowedTypes)) {
        // Admin/HR/Supervisor can view all employee balances
        $employeeId = $_GET['employee_id'] ?? null;
        $result = $leaveController->getEmployeeLeaveBalances($employeeId);
    } else {
        // Regular employees can only view their own balance  
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT employee_id FROM employees WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$employee) {
            throw new Exception('Employee record not found');
        }
        
        $result = $leaveController->getEmployeeLeaveBalances($employee['employee_id']);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching leave balances: ' . $e->getMessage()
    ]);
}
?>

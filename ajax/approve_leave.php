<?php
require_once __DIR__ . '/../shared/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

// Check if user can approve leaves
$userType = $_SESSION['user_type'] ?? '';
if (!in_array($userType, ['admin', 'hr', 'supervisor'])) {
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['leave_id']) || !isset($input['status']) || !isset($input['comments'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$leaveId = (int)$input['leave_id'];
$status = $input['status'];
$comments = trim($input['comments']);

// Validate status
if (!in_array($status, ['approved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    $db = getDBConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    // Get leave request details
    $stmt = $db->prepare("
        SELECT lr.*, e.first_name, e.last_name, e.employee_id
        FROM leave_records lr 
        JOIN employees e ON lr.employee_id = e.employee_id 
        WHERE lr.leave_id = ? AND lr.status = 'pending'
    ");
    $stmt->execute([$leaveId]);
    $leaveRequest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$leaveRequest) {
        throw new Exception('Leave request not found or already processed');
    }
    
    // Update leave request
    $stmt = $db->prepare("
        UPDATE leave_records 
        SET status = ?, 
            approved_by = ?, 
            approved_date = NOW(),
            admin_comments = ?
        WHERE leave_id = ?
    ");
    $stmt->execute([$status, $_SESSION['user_id'], $comments, $leaveId]);
    
    // If approved, update leave balance
    if ($status === 'approved') {
        $leaveType = $leaveRequest['leave_type'];
        $leaveDays = $leaveRequest['days_requested'];
        
        // Map leave types to balance columns
        $leaveTypeMap = [
            'vacation' => 'vacation_used',
            'sick' => 'sick_used', 
            'personal' => 'personal_used',
            'emergency' => 'emergency_used',
            'maternity' => 'maternity_used',
            'paternity' => 'paternity_used'
        ];
        
        if (isset($leaveTypeMap[$leaveType])) {
            $balanceColumn = $leaveTypeMap[$leaveType];
            
            // Update leave balance
            $stmt = $db->prepare("
                UPDATE leave_balances 
                SET {$balanceColumn} = {$balanceColumn} + ?
                WHERE employee_id = ? AND year = YEAR(CURDATE())
            ");
            $stmt->execute([$leaveDays, $leaveRequest['employee_id']]);
            
            // Create balance record if it doesn't exist
            if ($stmt->rowCount() === 0) {
                $stmt = $db->prepare("
                    INSERT INTO leave_balances (employee_id, year, {$balanceColumn}) 
                    VALUES (?, YEAR(CURDATE()), ?)
                    ON DUPLICATE KEY UPDATE {$balanceColumn} = {$balanceColumn} + ?
                ");
                $stmt->execute([$leaveRequest['employee_id'], $leaveDays, $leaveDays]);
            }
        }
    }
    
    // Log the action
    $actionMessage = ucfirst($status) . " leave request for " . $leaveRequest['first_name'] . " " . $leaveRequest['last_name'];
    
    // Log to system logs
    $stmt = $db->prepare("INSERT INTO system_logs (user_id, action, description, date_performed, ip_address) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->execute([$_SESSION['user_id'], 'UPDATE', $actionMessage, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
    
    $db->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Leave request {$status} successfully",
        'data' => [
            'leave_id' => $leaveId,
            'status' => $status,
            'employee_name' => $leaveRequest['first_name'] . ' ' . $leaveRequest['last_name']
        ]
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    error_log("Leave approval error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to process leave request: ' . $e->getMessage()]);
}
?>

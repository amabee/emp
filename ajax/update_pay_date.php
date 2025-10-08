<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../shared/config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    $payroll_id = $_POST['payroll_id'] ?? null;
    $pay_date = $_POST['pay_date'] ?? null;
    
    if (!$payroll_id) {
        echo json_encode(['success' => false, 'message' => 'Payroll ID is required']);
        exit();
    }
    
    // If pay_date is not provided, use current date
    if (!$pay_date) {
        $pay_date = date('Y-m-d');
    } else {
        // Validate the date format
        $dateTime = DateTime::createFromFormat('Y-m-d', $pay_date);
        if (!$dateTime || $dateTime->format('Y-m-d') !== $pay_date) {
            echo json_encode(['success' => false, 'message' => 'Invalid date format']);
            exit();
        }
    }
    
    $db = getDBConnection();
    
    // Update the pay_date for the specified payroll record
    $stmt = $db->prepare("UPDATE payroll SET pay_date = ? WHERE payroll_id = ?");
    $result = $stmt->execute([$pay_date, $payroll_id]);
    
    if ($result && $stmt->rowCount() > 0) {
        // Log the action
        try {
            require_once __DIR__ . '/../controllers/SystemLogger.php';
            $logger = new SystemLogger();
            $logger->logAuthAction($_SESSION['user_id'], 'UPDATE PAY DATE', "Updated pay date for payroll ID {$payroll_id} to {$pay_date}");
        } catch (Exception $e) {
            // Ignore logging errors
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Pay date updated successfully',
            'pay_date' => $pay_date
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update pay date or payroll record not found']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

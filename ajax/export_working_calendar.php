<?php
session_start();
require_once '../controllers/WorkingCalendarController.php';

// Check if user is admin
$user_type = $_SESSION['user_type'] ?? 'employee';
if ($user_type !== 'admin') {
    header('Content-Type: text/plain');
    echo "Error: Access denied. Only administrators can export working calendar.";
    exit;
}

// Set proper headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="working-calendar.csv"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

try {
    $month = intval($_GET['month'] ?? date('n'));
    $year = intval($_GET['year'] ?? date('Y'));
    
    // Validate inputs
    if ($month < 1 || $month > 12 || $year < 2020 || $year > 2050) {
        throw new Exception('Invalid month or year provided');
    }
    
    $controller = new WorkingCalendarController();
    $result = $controller->exportCalendarToExcel($month, $year);
    
    if (!$result['success']) {
        // If error, output error message as plain text
        header('Content-Type: text/plain');
        echo "Error: " . $result['message'];
        exit;
    }
    
    // Set the correct filename in header
    header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
    
    // Output CSV data
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 (helps with Excel compatibility)
    fwrite($output, "\xEF\xBB\xBF");
    
    foreach ($result['data'] as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    header('Content-Type: text/plain');
    echo "Error: " . $e->getMessage();
}
?>

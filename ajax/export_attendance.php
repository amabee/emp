<?php
require_once __DIR__ . '/../shared/config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit('Authentication required');
}

try {
    require_once __DIR__ . '/../controllers/AttendanceController.php';
    $controller = new AttendanceController();

    // Get filters from request (support both GET and POST)
    $request = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    $filters = [];
    
    // Date filter (default to today if not specified)
    $filters['date'] = $request['date'] ?? date('Y-m-d');
    
    // Department filter
    if (!empty($request['department'])) {
        $filters['department'] = $request['department'];
    }
    
    // Status filter
    if (!empty($request['status'])) {
        $filters['status'] = $request['status'];
    }
    
    // Search filter
    if (!empty($request['search'])) {
        $filters['search'] = trim($request['search']);
    }

    // Get export format
    $format = $request['format'] ?? 'csv';
    
    // Get all records (no pagination for export)
    // Remove pagination parameters to get all records
    unset($filters['page']);
    unset($filters['limit']);
    unset($filters['offset']);
    
    $records = $controller->getAttendanceRecords($filters);
    
    if ($format === 'pdf') {
        exportAsPDF($records, $filters);
    } else {
        exportAsCSV($records, $filters);
    }

} catch (Exception $e) {
    error_log("export_attendance.php Error: " . $e->getMessage());
    http_response_code(500);
    echo 'Export failed: ' . $e->getMessage();
}

function exportAsCSV($records, $filters) {
    $filename = 'attendance_report_' . ($filters['date'] ?? date('Y-m-d')) . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // CSV headers
    fputcsv($output, [
        'Employee Number',
        'Employee Name', 
        'Department',
        'Date',
        'Time In',
        'Time Out',
        'Status',
        'Remarks'
    ]);
    
    // CSV data
    foreach ($records as $record) {
        fputcsv($output, [
            $record['employee_number'],
            $record['employee_name'],
            $record['department_name'],
            $record['date'],
            $record['time_in'] ?: 'Not recorded',
            $record['time_out'] ?: 'Not recorded', 
            $record['status'],
            $record['remarks'] ?: ''
        ]);
    }
    
    fclose($output);
}

function exportAsPDF($records, $filters) {
    // For PDF export, we'll create a simple HTML-to-PDF solution
    $filename = 'attendance_report_' . ($filters['date'] ?? date('Y-m-d')) . '.pdf';
    
    // Create HTML content
    $html = generatePDFContent($records, $filters);
    
    // Simple PDF generation using wkhtmltopdf (if available) or DomPDF
    // For now, let's use a simple HTML response that can be printed to PDF
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo $html;
}

function generatePDFContent($records, $filters) {
    $date = $filters['date'] ?? date('Y-m-d');
    $department = $filters['department'] ?? 'All Departments';
    $status = $filters['status'] ?? 'All Statuses';
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - ' . $date . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #333; }
        .report-title { font-size: 18px; color: #666; margin: 10px 0; }
        .report-info { font-size: 14px; color: #888; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .status-present { color: #28a745; }
        .status-absent { color: #dc3545; }
        .status-late { color: #ffc107; }
        .status-leave { color: #17a2b8; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
        @media print {
            body { margin: 0; }
            .header { margin-bottom: 20px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Employee Management System</div>
        <div class="report-title">Daily Attendance Report</div>
        <div class="report-info">Date: ' . date('F j, Y', strtotime($date)) . '</div>
        <div class="report-info">Department: ' . htmlspecialchars($department) . ' | Status: ' . htmlspecialchars($status) . '</div>
        <div class="report-info">Generated on: ' . date('F j, Y g:i A') . '</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee No.</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>';

    if (empty($records)) {
        $html .= '<tr><td colspan="8" style="text-align: center; color: #888;">No attendance records found for the selected criteria</td></tr>';
    } else {
        foreach ($records as $index => $record) {
            $statusClass = 'status-' . strtolower(str_replace(' ', '-', $record['status']));
            $html .= '<tr>
                <td>' . ($index + 1) . '</td>
                <td>' . htmlspecialchars($record['employee_number']) . '</td>
                <td>' . htmlspecialchars($record['employee_name']) . '</td>
                <td>' . htmlspecialchars($record['department_name']) . '</td>
                <td>' . ($record['time_in'] ?: 'Not recorded') . '</td>
                <td>' . ($record['time_out'] ?: 'Not recorded') . '</td>
                <td class="' . $statusClass . '">' . htmlspecialchars($record['status']) . '</td>
                <td>' . htmlspecialchars($record['remarks'] ?: '') . '</td>
            </tr>';
        }
    }

    $html .= '</tbody>
    </table>
    
    <div class="footer">
        <p>Total Records: ' . count($records) . '</p>
        <p>This report was generated automatically by the Employee Management System.</p>
    </div>
    
    <script>
        // Auto-print for PDF generation
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>';

    return $html;
}
?>

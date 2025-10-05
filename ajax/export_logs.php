<?php
require_once('../shared/config.php');

try {
    $db = getDBConnection();
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    $where = [];
    $params = [];

    // Build WHERE clause based on filters
    if (!empty($_GET['type'])) {
        $filterType = $_GET['type'];
        switch ($filterType) {
            case 'login':
                $where[] = "sl.action_performed IN ('LOGIN', 'LOGOUT')";
                break;
            case 'user':
                $where[] = "sl.action_performed IN ('CREATE', 'UPDATE', 'DELETE') AND sl.full_description LIKE '%user%'";
                break;
            case 'employee':
                $where[] = "sl.action_performed IN ('CREATE', 'UPDATE', 'DELETE') AND sl.full_description LIKE '%employee%'";
                break;
            case 'organization':
                $where[] = "(sl.full_description LIKE '%department%' OR sl.full_description LIKE '%position%')";
                break;
            case 'settings':
                $where[] = "sl.action_performed = 'UPDATE' AND sl.full_description LIKE '%company%'";
                break;
        }
    }

    if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
        $where[] = "DATE(sl.date_performed) BETWEEN ? AND ?";
        $params[] = $_GET['date_from'];
        $params[] = $_GET['date_to'];
    }

    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $query = "
        SELECT 
            sl.log_id,
            sl.action_performed,
            sl.full_description,
            sl.date_performed,
            sl.ip_address,
            u.username,
            CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as full_name
        FROM system_logs sl
        LEFT JOIN users u ON sl.user_id = u.user_id
        LEFT JOIN employees e ON u.user_id = e.user_id
        $whereClause 
        ORDER BY sl.date_performed DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="system_logs_' . date('Y-m-d_His') . '.csv"');

    // Output CSV
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Timestamp', 'User', 'Activity Type', 'Description', 'IP Address']);

    foreach ($logs as $log) {
        $displayName = trim($log['full_name']) ?: $log['username'];
        fputcsv($output, [
            date('M d, Y H:i:s', strtotime($log['date_performed'])),
            $displayName . ' (@' . $log['username'] . ')',
            $log['action_performed'],
            $log['full_description'],
            $log['ip_address']
        ]);
    }

    fclose($output);

} catch (Exception $e) {
    // Handle errors gracefully
    header('Content-Type: text/plain');
    echo "Error exporting logs: " . $e->getMessage();
}

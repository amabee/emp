<?php
header('Content-Type: application/json');
session_start();

require_once '../shared/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $db = getDBConnection();
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Pagination settings
    $logsPerPage = 10;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $logsPerPage;

    // Base query with joins to get user information
    $baseQuery = "
        FROM system_logs sl
        LEFT JOIN users u ON sl.user_id = u.user_id
        LEFT JOIN employees e ON u.user_id = e.user_id
    ";

    // Build WHERE clause based on filters
    $whereClause = "WHERE 1=1";
    $params = [];

    // Filter by action type
    if (!empty($_GET['type'])) {
        $filterType = $_GET['type'];
        switch ($filterType) {
            case 'login':
                $whereClause .= " AND sl.action_performed IN ('LOGIN', 'LOGOUT')";
                break;
            case 'user':
                $whereClause .= " AND sl.action_performed IN ('CREATE', 'UPDATE', 'DELETE') AND sl.full_description LIKE '%user%'";
                break;
            case 'employee':
                $whereClause .= " AND sl.action_performed IN ('CREATE', 'UPDATE', 'DELETE') AND sl.full_description LIKE '%employee%'";
                break;
            case 'organization':
                $whereClause .= " AND sl.action_performed IN ('CREATE', 'UPDATE', 'DELETE') AND (sl.full_description LIKE '%department%' OR sl.full_description LIKE '%position%')";
                break;
            case 'settings':
                $whereClause .= " AND sl.action_performed = 'UPDATE' AND sl.full_description LIKE '%company%'";
                break;
        }
    }

    // Filter by date
    if (!empty($_GET['date'])) {
        $whereClause .= " AND DATE(sl.date_performed) = ?";
        $params[] = $_GET['date'];
    }

    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total " . $baseQuery . " " . $whereClause;
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $totalLogs = $countStmt->fetch()['total'];
    $totalPages = ceil($totalLogs / $logsPerPage);

    // Get logs with pagination
    $logsQuery = "
        SELECT 
            sl.log_id,
            sl.user_id,
            sl.action_performed,
            sl.full_description,
            sl.date_performed,
            sl.ip_address,
            u.username,
            CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as full_name
        " . $baseQuery . " " . $whereClause . "
        ORDER BY sl.date_performed DESC
        LIMIT " . $logsPerPage . " OFFSET " . $offset . "
    ";

    $logsStmt = $db->prepare($logsQuery);
    $logsStmt->execute($params);
    $logs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format logs for display
    $formattedLogs = [];
    foreach ($logs as $log) {
        $displayName = trim($log['full_name']) ?: $log['username'];
        
        $formattedLogs[] = [
            'log_id' => $log['log_id'],
            'user_id' => $log['user_id'],
            'action' => $log['action_performed'],
            'description' => $log['full_description'],
            'timestamp' => date('M d, Y H:i:s', strtotime($log['date_performed'])),
            'raw_timestamp' => $log['date_performed'],
            'ip_address' => $log['ip_address'],
            'username' => $log['username'],
            'display_name' => $displayName,
            'type' => strtolower($log['action_performed'])
        ];
    }

    echo json_encode([
        'success' => true,
        'logs' => $formattedLogs,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_logs' => $totalLogs,
            'logs_per_page' => $logsPerPage
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'logs' => [],
        'pagination' => [
            'current_page' => 1,
            'total_pages' => 0,
            'total_logs' => 0,
            'logs_per_page' => 10
        ]
    ]);
}
?>

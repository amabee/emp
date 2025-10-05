<?php
// Test script to debug reset functionality
require_once './shared/config.php';

try {
    $pdo = getDBConnection();
    
    $month = $_GET['month'] ?? date('n');
    $year = $_GET['year'] ?? date('Y');
    
    echo "<h2>Reset Month Debug Test</h2>";
    echo "<p>Testing month: $month, year: $year</p>";
    
    // Check what data exists
    echo "<h3>Current working_calendar data for this month:</h3>";
    $stmt = $pdo->prepare("SELECT * FROM working_calendar WHERE MONTH(work_date) = ? AND YEAR(work_date) = ?");
    $stmt->execute([$month, $year]);
    $calendar_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($calendar_data) . " working_calendar entries</p>";
    if (!empty($calendar_data)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Work Date</th><th>Is Working</th><th>Is Holiday</th><th>Is Half Day</th><th>Holiday Name</th></tr>";
        foreach ($calendar_data as $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['work_date'] . "</td>";
            echo "<td>" . $row['is_working'] . "</td>";
            echo "<td>" . $row['is_holiday'] . "</td>";
            echo "<td>" . ($row['is_half_day'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['holiday_name'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Current employee_schedule data for this month:</h3>";
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employee_schedule WHERE MONTH(work_date) = ? AND YEAR(work_date) = ?");
    $stmt->execute([$month, $year]);
    $schedule_count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . $schedule_count['count'] . " employee_schedule entries</p>";
    
    if (isset($_GET['test_reset']) && $_GET['test_reset'] == '1') {
        echo "<h3>Testing Reset Queries:</h3>";
        
        // Test the actual reset queries
        $pdo->beginTransaction();
        
        // First query
        $deleteScheduleStmt = $pdo->prepare("
            DELETE es FROM employee_schedule es
            INNER JOIN working_calendar wc ON es.calendar_id = wc.id
            WHERE MONTH(wc.work_date) = ? AND YEAR(wc.work_date) = ?
        ");
        $deleteScheduleStmt->execute([$month, $year]);
        $scheduleRowsAffected = $deleteScheduleStmt->rowCount();
        echo "<p>Would delete $scheduleRowsAffected employee schedule rows</p>";
        
        // Second query
        $deleteCalendarStmt = $pdo->prepare("
            DELETE FROM working_calendar 
            WHERE MONTH(work_date) = ? AND YEAR(work_date) = ?
        ");
        $deleteCalendarStmt->execute([$month, $year]);
        $calendarRowsAffected = $deleteCalendarStmt->rowCount();
        echo "<p>Would delete $calendarRowsAffected working calendar rows</p>";
        
        // Rollback to not actually delete
        $pdo->rollBack();
        echo "<p><strong>Transaction rolled back - no actual data was deleted</strong></p>";
    }
    
    echo "<p><a href='?month=$month&year=$year&test_reset=1'>Test Reset Queries (without deleting)</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}
?>

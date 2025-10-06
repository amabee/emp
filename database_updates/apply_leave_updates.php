<?php
require_once './shared/config.php';

try {
    $db = getDBConnection();
    
    echo "Starting database updates for leave management...\n";
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/leave_management_schema.sql');
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $db->beginTransaction();
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            $db->exec($statement);
        }
    }
    
    $db->commit();
    
    echo "✅ Database updates completed successfully!\n";
    echo "Added columns: reason, comments, half_day, created_at, updated_at to leave_records\n";
    echo "Updated leave_type enum to include Personal leave\n";
    echo "Created leave_balances table\n";
    echo "Initialized leave balances for existing employees\n";
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo "❌ Error updating database: " . $e->getMessage() . "\n";
    exit(1);
}
?>

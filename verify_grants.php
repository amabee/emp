<?php
/**
 * Database Grants Verification Script
 * Employee Management System
 * 
 * This script helps verify that database permissions are correctly configured
 * for the triggers and application to work properly.
 */

require_once 'shared/config.php';

echo "🔐 Database Grants Verification\n";
echo "==============================\n\n";

$pdo = getDBConnection();
if (!$pdo) {
    die("❌ Database connection failed! Check your credentials in shared/config.php\n");
}

echo "✅ Database connection successful\n";
echo "Connected as: " . DB_USER . "@" . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n\n";

// Test results array
$tests = [];

// =============================================
// Test 1: Basic Connection and Database Access
// =============================================
echo "1. BASIC DATABASE ACCESS:\n";
echo "-------------------------\n";

try {
    $stmt = $pdo->query("SELECT DATABASE() as current_db, CURRENT_USER() as current_user, VERSION() as mysql_version");
    $info = $stmt->fetch();
    
    echo "✅ Current Database: " . $info['current_db'] . "\n";
    echo "✅ Current User: " . $info['current_user'] . "\n";
    echo "✅ MySQL Version: " . $info['mysql_version'] . "\n";
    $tests['basic_access'] = true;
    
} catch (Exception $e) {
    echo "❌ Basic access failed: " . $e->getMessage() . "\n";
    $tests['basic_access'] = false;
}

echo "\n";

// =============================================
// Test 2: Table Access Permissions
// =============================================
echo "2. TABLE ACCESS PERMISSIONS:\n";
echo "----------------------------\n";

$tables = ['employees', 'system_logs', 'users', 'department', 'job_position'];
$permissions = ['SELECT', 'INSERT', 'UPDATE'];

foreach ($tables as $table) {
    echo "Testing table: $table\n";
    
    // Test SELECT
    try {
        $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
        echo "  ✅ SELECT permission\n";
        $tests["select_$table"] = true;
    } catch (Exception $e) {
        echo "  ❌ SELECT permission: " . $e->getMessage() . "\n";
        $tests["select_$table"] = false;
    }
    
    // Test INSERT (only for certain tables)
    if (in_array($table, ['employees', 'system_logs'])) {
        try {
            // Just test the permission, don't actually insert
            $stmt = $pdo->prepare("INSERT INTO $table SET employee_id = ?");
            echo "  ✅ INSERT permission (prepared statement created)\n";
            $tests["insert_$table"] = true;
        } catch (Exception $e) {
            echo "  ❌ INSERT permission: " . $e->getMessage() . "\n";
            $tests["insert_$table"] = false;
        }
    }
    
    echo "\n";
}

// =============================================
// Test 3: Session Variable Permissions
// =============================================
echo "3. SESSION VARIABLE PERMISSIONS:\n";
echo "--------------------------------\n";

try {
    $pdo->exec("SET @test_variable = 1");
    echo "✅ Can set session variables\n";
    $tests['session_variables'] = true;
    
    // Test the specific variable used by triggers
    $pdo->exec("SET @current_user_id = 1");
    echo "✅ Can set @current_user_id variable\n";
    $tests['current_user_id'] = true;
    
} catch (Exception $e) {
    echo "❌ Session variable permission failed: " . $e->getMessage() . "\n";
    echo "   This is required for trigger logging to work properly.\n";
    $tests['session_variables'] = false;
    $tests['current_user_id'] = false;
}

echo "\n";

// =============================================
// Test 4: Trigger Existence and Permissions
// =============================================
echo "4. TRIGGER VERIFICATION:\n";
echo "-----------------------\n";

try {
    $stmt = $pdo->query("
        SELECT TRIGGER_NAME, EVENT_MANIPULATION, DEFINER 
        FROM information_schema.TRIGGERS 
        WHERE TRIGGER_SCHEMA = DATABASE() 
        AND EVENT_OBJECT_TABLE = 'employees'
        ORDER BY TRIGGER_NAME
    ");
    
    $triggers = $stmt->fetchAll();
    
    if (empty($triggers)) {
        echo "⚠️  No triggers found for employees table\n";
        echo "   Run: php create_triggers.php\n";
        $tests['triggers_exist'] = false;
    } else {
        echo "✅ Found " . count($triggers) . " triggers:\n";
        foreach ($triggers as $trigger) {
            echo "   • {$trigger['TRIGGER_NAME']} ({$trigger['EVENT_MANIPULATION']}) - Definer: {$trigger['DEFINER']}\n";
        }
        $tests['triggers_exist'] = true;
    }
    
} catch (Exception $e) {
    echo "❌ Could not check triggers: " . $e->getMessage() . "\n";
    $tests['triggers_exist'] = false;
}

echo "\n";

// =============================================
// Test 5: Practical Trigger Test
// =============================================
echo "5. PRACTICAL TRIGGER TEST:\n";
echo "--------------------------\n";

if ($tests['triggers_exist'] && $tests['current_user_id']) {
    try {
        // Set user context
        $pdo->exec("SET @current_user_id = 1");
        
        // Test validation trigger (should fail)
        try {
            $pdo->exec("INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, gender, contact_number) VALUES (1, '', 'Test', 'invalid', -1000, 'male', '09123456789')");
            echo "❌ Validation trigger not working - invalid data was accepted\n";
            $tests['trigger_validation'] = false;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'First name cannot be empty') !== false || 
                strpos($e->getMessage(), 'Invalid email format') !== false ||
                strpos($e->getMessage(), 'Basic salary must be greater than zero') !== false) {
                echo "✅ Validation triggers working correctly\n";
                $tests['trigger_validation'] = true;
            } else {
                echo "⚠️  Validation trigger error (unexpected): " . $e->getMessage() . "\n";
                $tests['trigger_validation'] = false;
            }
        }
        
        // Test successful operation
        try {
            $testEmail = 'grant_test_' . time() . '@example.com';
            $testContact = '091' . substr(time(), -8);
            
            $stmt = $pdo->prepare("INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, gender, contact_number) VALUES (1, 'Grant', 'Test', ?, 25000, 'male', ?)");
            $result = $stmt->execute([$testEmail, $testContact]);
            
            if ($result) {
                $employeeId = $pdo->lastInsertId();
                echo "✅ Employee creation successful (ID: $employeeId)\n";
                
                // Check if it was logged
                $stmt = $pdo->prepare("SELECT COUNT(*) as log_count FROM system_logs WHERE full_description LIKE ? AND date_performed > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
                $stmt->execute(["%ID $employeeId%"]);
                $logResult = $stmt->fetch();
                
                if ($logResult['log_count'] > 0) {
                    echo "✅ Logging trigger working correctly\n";
                    $tests['trigger_logging'] = true;
                } else {
                    echo "⚠️  Logging trigger may not be working - no recent logs found\n";
                    $tests['trigger_logging'] = false;
                }
                
                // Clean up test employee
                $stmt = $pdo->prepare("DELETE FROM employees WHERE employee_id = ?");
                $stmt->execute([$employeeId]);
                echo "🧹 Test employee cleaned up\n";
                
            } else {
                echo "❌ Employee creation failed\n";
                $tests['trigger_logging'] = false;
            }
            
        } catch (Exception $e) {
            echo "❌ Employee creation test failed: " . $e->getMessage() . "\n";
            $tests['trigger_logging'] = false;
        }
        
    } catch (Exception $e) {
        echo "❌ Trigger test setup failed: " . $e->getMessage() . "\n";
        $tests['trigger_validation'] = false;
        $tests['trigger_logging'] = false;
    }
} else {
    echo "⚠️  Skipping trigger tests - prerequisites not met\n";
    $tests['trigger_validation'] = false;
    $tests['trigger_logging'] = false;
}

echo "\n";

// =============================================
// Test 6: Current User Grants Display
// =============================================
echo "6. CURRENT USER GRANTS:\n";
echo "----------------------\n";

try {
    $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current grants for " . DB_USER . ":\n";
    foreach ($grants as $grant) {
        echo "  • $grant\n";
    }
    
} catch (Exception $e) {
    echo "⚠️  Could not display grants: " . $e->getMessage() . "\n";
}

echo "\n";

// =============================================
// Summary and Recommendations
// =============================================
echo "📊 VERIFICATION SUMMARY:\n";
echo "========================\n";

$passedTests = array_filter($tests);
$totalTests = count($tests);
$passedCount = count($passedTests);

echo "Passed: $passedCount / $totalTests tests\n\n";

if ($passedCount === $totalTests) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "Your database permissions are correctly configured for triggers.\n";
} else {
    echo "⚠️  SOME TESTS FAILED:\n";
    
    $failedTests = array_diff_key($tests, $passedTests);
    foreach ($failedTests as $test => $result) {
        echo "❌ $test\n";
    }
    
    echo "\n📋 RECOMMENDATIONS:\n";
    
    if (!$tests['basic_access']) {
        echo "• Check database connection settings in shared/config.php\n";
        echo "• Verify user credentials and database exists\n";
    }
    
    if (!$tests['session_variables']) {
        echo "• Grant SESSION_VARIABLES_ADMIN permission:\n";
        echo "  GRANT SESSION_VARIABLES_ADMIN ON *.* TO '" . DB_USER . "'@'localhost';\n";
        echo "• For older MySQL versions, may need SUPER privilege\n";
    }
    
    if (!$tests['triggers_exist']) {
        echo "• Install triggers: php create_triggers.php\n";
    }
    
    foreach (['employees', 'system_logs'] as $table) {
        if (!($tests["select_$table"] ?? true) || !($tests["insert_$table"] ?? true)) {
            echo "• Grant permissions on $table table:\n";
            echo "  GRANT SELECT, INSERT, UPDATE ON emp.$table TO '" . DB_USER . "'@'localhost';\n";
        }
    }
}

echo "\n🔗 NEXT STEPS:\n";
echo "==============\n";
echo "1. Fix any failed permission tests above\n";
echo "2. Run: FLUSH PRIVILEGES; in MySQL\n";
echo "3. Re-run this script: php verify_grants.php\n";
echo "4. Once all tests pass, run: php final_trigger_test.php\n";

echo "\n📁 Related Files:\n";
echo "• setup_grants.sql - Production grants setup\n";
echo "• create_triggers.php - Trigger installation\n";
echo "• final_trigger_test.php - Complete functionality test\n";
echo "• TRIGGER_DOCUMENTATION.md - Full documentation\n";
?>

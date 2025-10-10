<?php

class SystemSettingsController
{
  private $db;

  public function __construct()
  {
    $this->db = getDBConnection();
    if (!$this->db) {
      throw new Exception("Database connection failed");
    }
  }

  public function getCompanyInfo()
  {
    try {
      $stmt = $this->db->prepare("SELECT * FROM company_info ORDER BY company_id DESC LIMIT 1");
      $stmt->execute();
      return $stmt->fetch();
    } catch (Exception $e) {
      throw new Exception('Failed to get company information: ' . $e->getMessage());
    }
  }

  public function updateCompanyInfo($data)
  {
    try {
      $this->db->beginTransaction();

      // Check if company info exists
      $existing = $this->getCompanyInfo();

      if ($existing) {
        // Update existing record
        $stmt = $this->db->prepare("
                    UPDATE company_info 
                    SET name = ?, address = ?, email = ?, contact_number = ?, website = ?, logo = ?, updated_at = NOW()
                    WHERE company_id = ?
                ");
        $stmt->execute([
          $data['name'],
          $data['address'] ?? null,
          $data['email'] ?? null,
          $data['contact_number'] ?? null,
          $data['website'] ?? null,
          $data['logo'] ?? $existing['logo'], // Keep existing logo if not updated
          $existing['company_id']
        ]);
      } else {
        // Insert new record
        $stmt = $this->db->prepare("
                    INSERT INTO company_info (name, address, email, contact_number, website, logo, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
        $stmt->execute([
          $data['name'],
          $data['address'] ?? null,
          $data['email'] ?? null,
          $data['contact_number'] ?? null,
          $data['website'] ?? null,
          $data['logo'] ?? null
        ]);
      }

      $this->db->commit();

      return [
        'success' => true,
        'message' => 'Company information updated successfully'
      ];

    } catch (Exception $e) {
      $this->db->rollBack();
      return [
        'success' => false,
        'message' => 'Failed to update company information: ' . $e->getMessage()
      ];
    }
  }

  public function uploadLogo($file)
  {
    try {
      // Create uploads directory if it doesn't exist
      $uploadDir = '../uploads/company/';
      if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }

      // Validate file
      $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
      if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, and GIF are allowed.');
      }

      if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        throw new Exception('File size too large. Maximum 5MB allowed.');
      }

      // Generate unique filename
      $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
      $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
      $filepath = $uploadDir . $filename;

      if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
          'success' => true,
          'filename' => $filename,
          'path' => $filepath
        ];
      } else {
        throw new Exception('Failed to upload file');
      }

    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function createDatabaseBackup()
  {
    try {
      $backupDir = '../backups/';
      if (!file_exists($backupDir)) {
        mkdir($backupDir, 0777, true);
      }

      $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
      $filepath = $backupDir . $filename;

      // Try mysqldump first, then fall back to PHP-based backup
      $mysqldumpSuccess = $this->tryMysqldumpBackup($filepath);

      if (!$mysqldumpSuccess) {
        // Fall back to PHP-based backup
        $this->createPhpBasedBackup($filepath);
      }

      if (file_exists($filepath) && filesize($filepath) > 0) {
        return [
          'success' => true,
          'filename' => $filename,
          'filepath' => $filepath,
          'message' => 'Database backup created successfully'
        ];
      } else {
        throw new Exception('Failed to create database backup - file not created or is empty');
      }

    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => 'Backup failed: ' . $e->getMessage()
      ];
    }
  }

  private function tryMysqldumpBackup($filepath)
  {
    try {
      // Try different possible paths for mysqldump
      $possiblePaths = [
        'mysqldump',
        'C:\laragon\bin\mysql\mysql-8.0.30\bin\mysqldump.exe',
        'C:\xampp\mysql\bin\mysqldump.exe',
        'C:\wamp64\bin\mysql\mysql8.0.31\bin\mysqldump.exe',
      ];

      foreach ($possiblePaths as $mysqldumpPath) {
        $command = sprintf(
          '"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --add-drop-table %s > "%s" 2>&1',
          $mysqldumpPath,
          DB_HOST,
          DB_PORT,
          DB_USER,
          DB_PASS,
          DB_NAME,
          $filepath
        );

        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($filepath) && filesize($filepath) > 0) {
          return true;
        }
      }
      return false;
    } catch (Exception $e) {
      return false;
    }
  }

  private function createPhpBasedBackup($filepath)
  {
    // Start with backup header
    $sql = "-- Database Backup Generated on " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- Database: " . DB_NAME . "\n";
    $sql .= "-- Generated by PHP-based backup method\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $sql .= "SET time_zone = \"+00:00\";\n\n";

    try {
      // Get all tables in dependency order (parent tables first)
      $tables = $this->getTablesInDependencyOrder();

      if (empty($tables)) {
        throw new Exception('No tables found in database');
      }

      foreach ($tables as $table) {
        $sql .= "-- --------------------------------------------------------\n";
        $sql .= "-- Table structure for table `$table`\n";
        $sql .= "-- --------------------------------------------------------\n\n";

        // Add DROP TABLE statement
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";

        // Get CREATE TABLE statement
        $createStmt = $this->db->query("SHOW CREATE TABLE `$table`");
        $createTable = $createStmt->fetch(PDO::FETCH_ASSOC);
        $sql .= $createTable['Create Table'] . ";\n\n";

        // Get table data
        $sql .= "-- Dumping data for table `$table`\n\n";

        $rowStmt = $this->db->query("SELECT * FROM `$table`");
        $rows = $rowStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rows)) {
          $columns = array_keys($rows[0]);
          $sql .= "INSERT INTO `$table` (`" . implode("`, `", $columns) . "`) VALUES\n";

          $valuesSets = [];
          foreach ($rows as $row) {
            $rowValues = [];
            foreach ($row as $value) {
              if ($value === null) {
                $rowValues[] = 'NULL';
              } elseif (is_numeric($value) && !is_string($value)) {
                $rowValues[] = $value;
              } else {
                // Properly escape strings
                $escapedValue = str_replace(
                  ["\\", "\x00", "\n", "\r", "'", '"', "\x1a"],
                  ["\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z"],
                  $value
                );
                $rowValues[] = "'" . $escapedValue . "'";
              }
            }
            $valuesSets[] = "(" . implode(", ", $rowValues) . ")";
          }
          $sql .= implode(",\n", $valuesSets) . ";\n\n";
        } else {
          $sql .= "-- No data found for table `$table`\n\n";
        }
      }

      $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
      $sql .= "-- Backup completed on " . date('Y-m-d H:i:s') . "\n";

      // Write to file
      $bytesWritten = file_put_contents($filepath, $sql);
      if ($bytesWritten === false) {
        throw new Exception('Failed to write backup file');
      }

      if ($bytesWritten === 0) {
        throw new Exception('Backup file is empty - no data written');
      }

    } catch (Exception $e) {
      throw new Exception('PHP backup failed: ' . $e->getMessage());
    }
  }

  public function restoreDatabase($file)
  {
    try {
      if (!file_exists($file['tmp_name'])) {
        throw new Exception('Backup file not found');
      }

      if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'sql') {
        throw new Exception('Invalid file type. Only SQL files are allowed.');
      }

      $sqlContent = file_get_contents($file['tmp_name']);
      if ($sqlContent === false || empty(trim($sqlContent))) {
        throw new Exception('Backup file is empty or unreadable');
      }

      // Disable constraints for restore
      $this->db->exec("SET FOREIGN_KEY_CHECKS=0");
      $this->db->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
      $this->db->exec("SET time_zone = '+00:00'");

      $statements = $this->splitSqlStatements($sqlContent);

      // Separate schema and data statements
      $schemaStmts = [];
      $dataStmts = [];

      foreach ($statements as $s) {
        $s = trim($s);
        if (empty($s) || $this->isCommentLine($s))
          continue;

        if (preg_match('/^(CREATE|ALTER)\s+TABLE/i', $s)) {
          $schemaStmts[] = $s;
        } else {
          $dataStmts[] = $s;
        }
      }

      $executed = 0;
      $skipped = 0;
      $processedPrimaryKeys = []; // Track tables that already have PRIMARY KEY processed
      $processedForeignKeys = []; // Track foreign key constraints processed

      // 🧱 1️⃣ Process structure first
      foreach ($schemaStmts as $stmt) {
        // CREATE TABLE IF NOT EXISTS - Extract columns and ensure they exist
        if (preg_match('/^CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?\s*\((.*)\)/is', $stmt, $createMatch)) {
          $table = $createMatch[1];
          $stmt = preg_replace('/^CREATE\s+TABLE/i', 'CREATE TABLE IF NOT EXISTS', $stmt);

          // Extract column definitions from CREATE TABLE
          $columnDefs = $createMatch[2];
          $this->ensureTableColumns($table, $columnDefs);
        } elseif (preg_match('/^CREATE\s+TABLE\s+/i', $stmt)) {
          $stmt = preg_replace('/^CREATE\s+TABLE/i', 'CREATE TABLE IF NOT EXISTS', $stmt);
        }

        // Skip duplicate ALTER COLUMNs
        if (preg_match('/ALTER\s+TABLE\s+`?(\w+)`?.*ADD\s+(?:COLUMN\s+)?`?(\w+)`?/i', $stmt, $m)) {
          $table = $m[1];
          $column = $m[2];
          if ($this->columnExists($table, $column)) {
            $skipped++;
            continue;
          }
        }

        // ✅ Enhanced PRIMARY KEY check - catches more patterns
        if (preg_match('/ALTER\s+TABLE\s+`?(\w+)`?\s+ADD\s+(?:CONSTRAINT\s+.*?\s+)?PRIMARY\s+KEY/i', $stmt, $pkMatch)) {
          $table = $pkMatch[1];

          // Check if we've already processed a PRIMARY KEY for this table in this restore
          if (isset($processedPrimaryKeys[$table])) {
            error_log("⚠️ Skipping duplicate PRIMARY KEY definition for `$table` (already processed in this restore)");
            $skipped++;
            continue;
          }

          // Check if table already has a PRIMARY KEY in the database
          if ($this->hasPrimaryKey($table)) {
            error_log("⚠️ Skipping duplicate PRIMARY KEY definition for `$table` (already exists in database)");
            $skipped++;
            continue;
          }

          // Mark this table as having PRIMARY KEY processed
          $processedPrimaryKeys[$table] = true;
        }

        // ✅ FOREIGN KEY constraint check
        if (preg_match('/ALTER\s+TABLE\s+`?(\w+)`?\s+ADD\s+(?:CONSTRAINT\s+`?(\w+)`?\s+)?FOREIGN\s+KEY/i', $stmt, $fkMatch)) {
          $table = $fkMatch[1];
          $constraintName = isset($fkMatch[2]) ? $fkMatch[2] : null;

          // If constraint name is specified, check if it exists
          if ($constraintName) {
            if (isset($processedForeignKeys[$constraintName])) {
              error_log("⚠️ Skipping duplicate FOREIGN KEY constraint `$constraintName` (already processed in this restore)");
              $skipped++;
              continue;
            }

            if ($this->foreignKeyExists($table, $constraintName)) {
              error_log("⚠️ Skipping duplicate FOREIGN KEY constraint `$constraintName` on `$table` (already exists in database)");
              $skipped++;
              continue;
            }

            $processedForeignKeys[$constraintName] = true;
          }
        }

        try {
          $this->db->exec($stmt);
          $executed++;
        } catch (PDOException $e) {
          $msg = $e->getMessage();
          if (
            str_contains($msg, 'already exists') ||
            str_contains($msg, 'Duplicate column name') ||
            str_contains($msg, 'Multiple primary key defined') ||
            str_contains($msg, 'Duplicate key name') ||
            str_contains($msg, 'Duplicate foreign key constraint')
          ) {
            $skipped++;
            continue;
          }
          error_log("❌ Schema SQL Failed: " . substr($stmt, 0, 200) . "...");
          error_log("Error: " . $msg);
          throw $e;
        }
      }

      // 💾 2️⃣ Then process data (INSERT/UPDATE)
      foreach ($dataStmts as $stmt) {
        // INSERT IGNORE
        if (preg_match('/^INSERT\s+INTO/i', $stmt)) {
          $stmt = preg_replace('/^INSERT\s+INTO/i', 'INSERT IGNORE INTO', $stmt);
        }

        try {
          $this->db->exec($stmt);
          $executed++;
        } catch (PDOException $e) {
          $msg = $e->getMessage();

          // Handle missing table or column dynamically
          if (preg_match('/Unknown column \'(\w+)\'/', $msg, $matches)) {
            $missingColumn = $matches[1];
            if (preg_match('/(?:INSERT\s+INTO|UPDATE|FROM)\s+`?(\w+)`?/i', $stmt, $tm)) {
              $table = $tm[1];
              error_log("⚠️ Missing column `$missingColumn` in `$table` — creating...");
              $this->addMissingColumn($table, $missingColumn);

              // Retry
              $this->db->exec($stmt);
              $executed++;
              continue;
            }
          }

          if (str_contains($msg, 'doesn\'t exist')) {
            // If table missing, create automatically
            if (preg_match('/(?:INSERT\s+INTO|UPDATE)\s+`?(\w+)`?/i', $stmt, $m)) {
              $table = $m[1];
              error_log("⚠️ Table `$table` missing — creating placeholder...");
              $this->createTableIfMissing($table);
              $this->db->exec($stmt);
              $executed++;
              continue;
            }
          }

          if (str_contains($msg, 'Duplicate entry')) {
            $skipped++;
            continue;
          }

          // Otherwise, log and rethrow
          error_log("❌ Data SQL Failed: " . substr($stmt, 0, 200) . "...");
          error_log("Error: " . $msg);
          throw $e;
        }
      }

      $this->db->exec("SET FOREIGN_KEY_CHECKS=1");

      return [
        'success' => true,
        'message' => "Database restored successfully. Executed $executed statements, skipped $skipped duplicates or existing entries."
      ];
    } catch (Exception $e) {
      try {
        $this->db->exec("SET FOREIGN_KEY_CHECKS=1");
      } catch (Exception $ignore) {
      }
      return [
        'success' => false,
        'message' => 'Restore failed: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Ensures all columns from a CREATE TABLE definition exist in the table
   */
  private function ensureTableColumns($table, $columnDefs)
  {
    try {
      // Check if table exists first
      $tableExists = $this->tableExists($table);

      if (!$tableExists) {
        // Table doesn't exist yet, it will be created by the CREATE TABLE statement
        return;
      }

      // Table exists, so check each column
      // Parse column definitions
      $lines = explode(',', $columnDefs);

      foreach ($lines as $line) {
        $line = trim($line);

        // Skip constraints and keys
        if (preg_match('/^(PRIMARY\s+KEY|KEY|UNIQUE|CONSTRAINT|FOREIGN\s+KEY|INDEX)/i', $line)) {
          continue;
        }

        // Extract column name and type
        if (preg_match('/^`?(\w+)`?\s+(.+?)(?:\s+|$)/i', $line, $colMatch)) {
          $columnName = $colMatch[1];
          $columnType = $colMatch[2];

          // Clean up column type (remove trailing commas, constraints, etc.)
          $columnType = preg_replace('/\s+(NOT\s+NULL|NULL|DEFAULT\s+.*|AUTO_INCREMENT|COMMENT\s+.*|ON\s+UPDATE\s+.*)$/i', '', $columnType);
          $columnType = rtrim($columnType, ',');

          // Check if column exists
          if (!$this->columnExists($table, $columnName)) {
            error_log("⚠️ Column `$columnName` missing in `$table` — adding from CREATE TABLE definition...");

            try {
              // Extract full column definition with constraints
              $fullDef = trim($line);
              $fullDef = rtrim($fullDef, ',');

              $this->db->exec("ALTER TABLE `$table` ADD COLUMN $fullDef");
              error_log("✅ Added column `$columnName` to `$table`");
            } catch (Exception $e) {
              error_log("❌ Failed to add column `$columnName` to `$table`: " . $e->getMessage());
            }
          }
        }
      }
    } catch (Exception $e) {
      error_log("⚠️ Error in ensureTableColumns for `$table`: " . $e->getMessage());
    }
  }

  /**
   * Checks if a table exists
   */
  private function tableExists($table)
  {
    try {
      $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
      $stmt->execute([$table]);
      return $stmt->fetch() !== false;
    } catch (Exception $e) {
      return false;
    }
  }

  /**
   * Adds a missing column with type guessing.
   */
  private function addMissingColumn($table, $column)
  {
    try {
      $type = 'VARCHAR(255)';
      if (str_contains($column, 'date'))
        $type = 'DATE';
      elseif (preg_match('/_id$/', $column))
        $type = 'INT';
      elseif (preg_match('/amount|price|rate|total/', $column))
        $type = 'DECIMAL(10,2)';
      elseif (preg_match('/status|flag|type/', $column))
        $type = 'VARCHAR(50)';

      $this->db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $type NULL DEFAULT NULL");
      error_log("✅ Added missing column `$column` ($type) to `$table`");
    } catch (Exception $e) {
      error_log("❌ Failed to add column `$column` to `$table`: " . $e->getMessage());
    }
  }

  /**
   * Checks if a table already has a PRIMARY KEY.
   */
  private function hasPrimaryKey($table)
  {
    try {
      $stmt = $this->db->prepare("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
      $stmt->execute();
      return $stmt->fetch() !== false;
    } catch (Exception $e) {
      return false;
    }
  }

  /**
   * Checks if a foreign key constraint exists
   */
  private function foreignKeyExists($table, $constraintName)
  {
    try {
      $stmt = $this->db->prepare("
      SELECT CONSTRAINT_NAME 
      FROM information_schema.TABLE_CONSTRAINTS 
      WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = ? 
      AND CONSTRAINT_NAME = ? 
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ");
      $stmt->execute([$table, $constraintName]);
      return $stmt->fetch() !== false;
    } catch (Exception $e) {
      return false;
    }
  }

  /**
   * Creates a placeholder table if missing.
   */
  private function createTableIfMissing($table)
  {
    try {
      $sql = "CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT AUTO_INCREMENT PRIMARY KEY
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
      $this->db->exec($sql);
      error_log("✅ Created placeholder table `$table`");
    } catch (Exception $e) {
      error_log("❌ Failed to create table `$table`: " . $e->getMessage());
    }
  }

  /**
   * Helper: checks if a column exists in a table
   */
  private function columnExists($table, $column)
  {
    try {
      $stmt = $this->db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
      $stmt->execute([$column]);
      return $stmt->fetch() !== false;
    } catch (Exception $e) {
      // table might not exist yet, so assume false
      return false;
    }
  }

  /**
   * Helper: Splits SQL content into individual statements,
   * properly handling strings, comments, and embedded semicolons.
   */
  private function splitSqlStatements($sql)
  {
    $sql = preg_replace('/^--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    $statements = [];
    $current = '';
    $inString = false;
    $stringChar = '';

    for ($i = 0; $i < strlen($sql); $i++) {
      $char = $sql[$i];
      $prev = $i > 0 ? $sql[$i - 1] : '';

      if (($char === '"' || $char === "'") && $prev !== '\\') {
        if (!$inString) {
          $inString = true;
          $stringChar = $char;
        } elseif ($char === $stringChar) {
          $inString = false;
          $stringChar = '';
        }
      }

      if ($char === ';' && !$inString) {
        $statements[] = trim($current);
        $current = '';
      } else {
        $current .= $char;
      }
    }

    // Add the last statement if it exists
    if (!empty(trim($current))) {
      $statements[] = trim($current);
    }

    return array_filter($statements, function ($stmt) {
      return !empty(trim($stmt));
    });
  }

  /**
   * Helper: checks if a line is a comment or empty
   */
  private function isCommentLine($line)
  {
    $line = trim($line);
    return empty($line) ||
      strpos($line, '--') === 0 ||
      strpos($line, '/*') === 0 ||
      strpos($line, '#') === 0;
  }

  /**
   * Gets system statistics like total users, employees, departments, positions, and database size.
   */
  public function getSystemStats()
  {
    try {
      $stats = [];

      // Get total users
      $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE active_status != 'deleted'");
      $stmt->execute();
      $stats['total_users'] = $stmt->fetch()['count'];

      // Get total employees
      $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employees WHERE employment_status = 1");
      $stmt->execute();
      $stats['total_employees'] = $stmt->fetch()['count'];

      // Get total departments
      $stmt = $this->db->prepare("SELECT COUNT(DISTINCT department_name) as count FROM department");
      $stmt->execute();
      $stats['total_departments'] = $stmt->fetch()['count'];

      // Get total positions
      $stmt = $this->db->prepare("SELECT COUNT(DISTINCT position_name) as count FROM job_position");
      $stmt->execute();
      $stats['total_positions'] = $stmt->fetch()['count'];

      // Get database size (approximate)
      $stmt = $this->db->prepare("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS db_size 
                FROM information_schema.tables 
                WHERE table_schema = ?
            ");
      $stmt->execute([DB_NAME]);
      $stats['db_size'] = $stmt->fetch()['db_size'] ?? '0';

      return $stats;

    } catch (Exception $e) {
      return [
        'total_users' => 0,
        'total_employees' => 0,
        'total_departments' => 0,
        'total_positions' => 0,
        'db_size' => '0'
      ];
    }
  }

  /**
   * Helper: Gets all tables in the database ordered by dependencies (parent tables first).
   */
  private function getTablesInDependencyOrder()
  {
    // Define table order based on foreign key dependencies
    // Parent tables (referenced by others) should come first
    $preferredOrder = [
      'user_type',
      'users',
      'department',
      'job_position',
      'position',
      'company_info',
      'employees',
      'working_calendar',
      'employee_schedule',
      'allowance_types',
      'deduction_types',
      'employee_allowance',
      'employee_deduction',
      'leave_records',
      'leave_balances',
      'attendance',
      'overtime_records',
      'payroll',
      'system_logs'
    ];

    // Get all actual tables from database
    $stmt = $this->db->query("SHOW TABLES");
    $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Order tables according to preferred order, then add any remaining tables
    $orderedTables = [];

    // First, add tables in preferred order if they exist
    foreach ($preferredOrder as $table) {
      if (in_array($table, $allTables)) {
        $orderedTables[] = $table;
      }
    }

    // Add any remaining tables that weren't in our preferred list
    foreach ($allTables as $table) {
      if (!in_array($table, $orderedTables)) {
        $orderedTables[] = $table;
      }
    }

    return $orderedTables;
  }
}
?>


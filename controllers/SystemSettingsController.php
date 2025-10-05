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

            // Use mysqldump command
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                DB_HOST,
                DB_PORT,
                DB_USER,
                DB_PASS,
                DB_NAME,
                $filepath
            );

            // Execute backup
            exec($command, $output, $returnVar);

            if ($returnVar === 0 && file_exists($filepath)) {
                return [
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'message' => 'Database backup created successfully'
                ];
            } else {
                throw new Exception('Failed to create database backup');
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ];
        }
    }

    public function restoreDatabase($file)
    {
        try {
            if (!file_exists($file['tmp_name'])) {
                throw new Exception('Backup file not found');
            }

            // Validate file extension
            if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'sql') {
                throw new Exception('Invalid file type. Only SQL files are allowed.');
            }

            // Read SQL file content
            $sqlContent = file_get_contents($file['tmp_name']);
            if ($sqlContent === false) {
                throw new Exception('Failed to read backup file');
            }

            // Execute SQL statements
            $this->db->beginTransaction();
            
            // Split SQL into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sqlContent)),
                function($stmt) {
                    return !empty($stmt) && !preg_match('/^(--|\/\*)/i', $stmt);
                }
            );

            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->db->exec($statement);
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Database restored successfully'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ];
        }
    }

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
}
?>
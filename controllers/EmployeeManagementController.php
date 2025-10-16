<?php

class EmployeeManagementController
{
    private $db;

    public function __construct()
    {
        $this->db = getDBConnection();
        if (!$this->db) {
            throw new Exception("Database connection failed");
        }
    }

    public function addEmployee($data)
    {
        try {
            $this->db->beginTransaction();

            // First create the user account
            $baseUsername = strtolower($data['first_name'] . $data['last_name']);
            $randomNumber = rand(100, 999);
            $username = $baseUsername . $randomNumber;

            // Generate a random password (8 chars, alphanumeric)
            $password = bin2hex(random_bytes(4));
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert into users table (user_type_id = 4 for Employee)
            $userStmt = $this->db->prepare("
                INSERT INTO users (username, password, user_type_id, active_status, created_at) 
                VALUES (?, ?, 4, 'active', NOW())
            ");
            $userStmt->execute([$username, $hashedPassword]);

            // Get the inserted user ID
            $userId = $this->db->lastInsertId();

            // If no image provided, set default avatar based on gender and username
            if (empty($data['image'])) {
                $genderSegment = 'boy';
                if (!empty($data['gender']) && strtolower($data['gender']) === 'female') {
                    $genderSegment = 'girl';
                }
                $safeUser = rawurlencode($username);
                $data['image'] = "https://avatar.iran.liara.run/public/{$genderSegment}?username={$safeUser}";
            }

            // Insert into employees table (include gender, birthdate, image if provided)
            $stmt = $this->db->prepare("
                INSERT INTO employees (user_id, department_id, position_id, branch_id, first_name, middle_name, last_name, 
                                     contact_number, email, gender, birthdate, basic_salary, image, employment_status, date_hired) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, CURDATE())
            ");
            
            $stmt->execute([
                $userId,
                $data['department_id'] ?? null,
                $data['position_id'] ?? null,
                $data['branch_id'] ?? null,
                $data['first_name'],
                $data['middle_name'] ?? null,
                $data['last_name'],
                $data['contact_number'] ?? null,
                $data['email'],
                $data['gender'] ?? null,
                !empty($data['birthdate']) ? $data['birthdate'] : null,
                isset($data['basic_salary']) && $data['basic_salary'] !== '' ? (float)$data['basic_salary'] : null,
                $data['image'] ?? null,
            ]);

            $employeeId = $this->db->lastInsertId();

            $this->db->commit();

            return [
                'success' => true,
                'employee_id' => $employeeId,
                'user_id' => $userId,
                'username' => $username,
                'password' => $password,
                'message' => 'Employee added successfully'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to add employee: ' . $e->getMessage()
            ];
        }
    }

    public function getAllEmployees($filters = [])
    {
        try {
            // Exclude admin user accounts (user_type_id = 1)
            $whereClause = "WHERE e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)";
            $params = [];

            // Filter by branch for HR users (Admin can see all)
            if (!empty($filters['user_branch_id'])) {
                $whereClause .= " AND e.branch_id = ?";
                $params[] = $filters['user_branch_id'];
            }

            if (!empty($filters['search'])) {
                $whereClause .= " AND (CONCAT(e.first_name, ' ', e.last_name) LIKE ? OR e.email LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($filters['department'])) {
                $whereClause .= " AND e.department_id = ?";
                $params[] = $filters['department'];
            }

            $sql = "
                SELECT 
                    e.employee_id as id,
                    CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) as name,
                    e.email,
                    e.contact_number,
                    COALESCE(jp.position_name, 'No Position') as position,
                    CASE WHEN e.employment_status = 1 THEN 'Active' ELSE 'Inactive' END as status,
                    COALESCE(d.department_name, 'No Department') as department,
                    COALESCE(b.branch_name, 'No Branch') as branch,
                    b.branch_code,
                    e.branch_id,
                    e.date_hired as created_at,
                    e.user_id,
                    e.image
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.user_id
                LEFT JOIN department d ON e.department_id = d.department_id
                LEFT JOIN job_position jp ON e.position_id = jp.position_id
                LEFT JOIN branches b ON e.branch_id = b.branch_id
                $whereClause
                ORDER BY e.date_hired DESC, e.employee_id DESC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();

        } catch (Exception $e) {
            throw new Exception('Failed to get employees: ' . $e->getMessage());
        }
    }

    /**
     * Get employees with pagination support. Returns ['results' => [...], 'total' => n]
     * Supports filters: search, department, page (1-based), per_page
     */
    public function getEmployeesPaginated($filters = [])
    {
        try {
            $whereClause = "WHERE e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)";
            $params = [];

            if (!empty($filters['search'])) {
                $whereClause .= " AND (CONCAT(e.first_name, ' ', e.last_name) LIKE ? OR e.email LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($filters['department'])) {
                $whereClause .= " AND e.department_id = ?";
                $params[] = $filters['department'];
            }

            // Branch filtering for HR users
            if (!empty($filters['user_branch_id'])) {
                $whereClause .= " AND e.branch_id = ?";
                $params[] = $filters['user_branch_id'];
            }

            // Count total
            $countSql = "SELECT COUNT(*) as cnt FROM employees e LEFT JOIN users u ON e.user_id = u.user_id $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = intval($countStmt->fetchColumn());

            $page = max(1, intval($filters['page'] ?? 1));
            $perPage = max(10, intval($filters['per_page'] ?? 20));
            $offset = ($page - 1) * $perPage;

            // Inject numeric LIMIT/OFFSET directly to avoid binding issues in some MySQL versions
            $limit = intval($perPage);
            $off = intval($offset);

            $sql = "
                SELECT 
                    e.employee_id as id,
                    CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) as name,
                    e.email,
                    e.contact_number,
                    COALESCE(jp.position_name, 'No Position') as position,
                    CASE WHEN e.employment_status = 1 THEN 'Active' ELSE 'Inactive' END as status,
                    COALESCE(d.department_name, 'No Department') as department,
                    COALESCE(b.branch_name, 'No Branch') as branch,
                    b.branch_code,
                    e.branch_id,
                    e.date_hired as created_at,
                    e.user_id,
                    e.image
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.user_id
                LEFT JOIN department d ON e.department_id = d.department_id
                LEFT JOIN job_position jp ON e.position_id = jp.position_id
                LEFT JOIN branches b ON e.branch_id = b.branch_id
                $whereClause
                ORDER BY e.date_hired DESC, e.employee_id DESC
                LIMIT {$limit} OFFSET {$off}
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            return ['results' => $rows, 'total' => $total];

        } catch (Exception $e) {
            throw new Exception('Failed to get employees (paginated): ' . $e->getMessage());
        }
    }

    public function getAllDepartments()
    {
        try {
            // Get unique department names with the smallest ID for each name
            $stmt = $this->db->prepare("
                SELECT MIN(department_id) as id, department_name as name 
                FROM department 
                GROUP BY department_name 
                ORDER BY department_name
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return []; // Return empty array if departments table doesn't exist yet
        }
    }

    public function getAllPositions()
    {
        try {
            // Get unique position names with the smallest ID for each name
            $stmt = $this->db->prepare("
                SELECT MIN(position_id) as id, position_name as name 
                FROM job_position 
                GROUP BY position_name 
                ORDER BY position_name
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getEmployeeById($id)
    {
        try {
            $sql = "
                SELECT 
                    e.*,
                    d.department_name,
                    jp.position_name,
                    b.branch_name,
                    b.branch_code,
                    u.username,
                    u.user_type_id,
                    ut.type_name as user_type_name
                FROM employees e
                LEFT JOIN department d ON e.department_id = d.department_id
                LEFT JOIN job_position jp ON e.position_id = jp.position_id
                LEFT JOIN branches b ON e.branch_id = b.branch_id
                LEFT JOIN users u ON e.user_id = u.user_id
                LEFT JOIN user_type ut ON u.user_type_id = ut.user_type_id
                WHERE e.employee_id = ?
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            return $stmt->fetch();

        } catch (Exception $e) {
            throw new Exception('Failed to get employee: ' . $e->getMessage());
        }
    }

    public function updateEmployee($id, $data)
    {
        try {
            $this->db->beginTransaction();

            // Get the user_id for this employee
            $stmt = $this->db->prepare("SELECT user_id FROM employees WHERE employee_id = ?");
            $stmt->execute([$id]);
            $employee = $stmt->fetch();

            if (!$employee) {
                throw new Exception('Employee not found');
            }

            // Update employees table (include gender, birthdate, basic_salary). Only update image if provided.
            $setParts = [
                'first_name = ?',
                'middle_name = ?',
                'last_name = ?',
                'email = ?',
                'contact_number = ?',
                'department_id = ?',
                'position_id = ?',
                'branch_id = ?',
                'employment_status = ?',
                'gender = ?',
                'birthdate = ?',
                'basic_salary = ?'
            ];

            $params = [
                $data['first_name'],
                $data['middle_name'] ?? null,
                $data['last_name'],
                $data['email'],
                $data['contact_number'] ?? null,
                $data['department_id'] ?? null,
                $data['position_id'] ?? null,
                $data['branch_id'] ?? null,
                $data['employment_status'] ?? 1,
                $data['gender'] ?? null,
                !empty($data['birthdate']) ? $data['birthdate'] : null,
                isset($data['basic_salary']) && $data['basic_salary'] !== '' ? (float)$data['basic_salary'] : null
            ];

            if (isset($data['image']) && $data['image'] !== null) {
                $setParts[] = 'image = ?';
                $params[] = $data['image'];
            }

            $setSql = implode(', ', $setParts);
            $sql = "UPDATE employees SET {$setSql} WHERE employee_id = ?";
            $params[] = $id;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Update user type if provided and user exists
            if (isset($data['user_type']) && !empty($data['user_type']) && $employee['user_id']) {
                $stmt = $this->db->prepare("UPDATE users SET user_type_id = ? WHERE user_id = ?");
                $stmt->execute([$data['user_type'], $employee['user_id']]);
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Employee updated successfully'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to update employee: ' . $e->getMessage()
            ];
        }
    }

    public function deleteEmployee($id)
    {
        try {
            $this->db->beginTransaction();

            // Get the user_id first
            $stmt = $this->db->prepare("SELECT user_id FROM employees WHERE employee_id = ?");
            $stmt->execute([$id]);
            $employee = $stmt->fetch();

            if ($employee) {
                // Soft delete the employee (set employment_status to 0)
                $stmt = $this->db->prepare("UPDATE employees SET employment_status = 0 WHERE employee_id = ?");
                $stmt->execute([$id]);

                // Also disable the associated user account
                $userStmt = $this->db->prepare("UPDATE users SET active_status = 'locked' WHERE user_id = ?");
                $userStmt->execute([$employee['user_id']]);
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Employee deleted successfully'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to delete employee: ' . $e->getMessage()
            ];
        }
    }

    // Helper method to check if email exists
    public function emailExists($email, $excludeEmployeeId = null)
    {
        try {
            $sql = "SELECT employee_id FROM employees WHERE email = ? AND employment_status = 1";
            $params = [$email];
            
            if ($excludeEmployeeId) {
                $sql .= " AND employee_id != ?";
                $params[] = $excludeEmployeeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get deduction types
     * Returns type_name, created_by, updated_by, is_active, created_at, updated_at, and deduction_type_id
     */
    public function getDeductionTypes()
    {
        try {
            $stmt = $this->db->prepare("SELECT deduction_type_id,
             type_name, amount_type, created_by, updated_by, is_active, statutory, created_at, updated_at, is_dynamic FROM deduction_type WHERE 1");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get employees by department_id
     */
    public function getEmployeesByDepartment($departmentId)
    {
        try {
            // Exclude admin user accounts
            $stmt = $this->db->prepare("SELECT e.employee_id, e.user_id, e.department_id, e.position_id, e.first_name, e.middle_name, e.last_name, e.email FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.department_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1) ORDER BY e.last_name, e.first_name");
            $stmt->execute([$departmentId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get employees by position_id
     */
    public function getEmployeesByPosition($positionId)
    {
        try {
            // Exclude admin user accounts
            $stmt = $this->db->prepare("SELECT e.employee_id, e.user_id, e.department_id, e.position_id, e.first_name, e.middle_name, e.last_name, e.email FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.position_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1) ORDER BY e.last_name, e.first_name");
            $stmt->execute([$positionId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get employee deductions rows
     * Returns employee_id, deduction_type_id, amount, amount_type
     */
    public function getEmployeeDeductions()
    {
        try {
            $stmt = $this->db->prepare("SELECT employee_deduction_id, employee_id, deduction_type_id, amount, amount_type FROM employee_deduction WHERE 1");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Assign allowances to an employee
     * @param int $employeeId
     * @param array $allowances Array of allowance_id => amount pairs
     * @return bool
     */
    public function assignAllowancesToEmployee($employeeId, $allowances)
    {
        try {
            if (empty($allowances)) {
                return true; // No allowances to assign
            }

            foreach ($allowances as $allowanceId => $amount) {
                // Check if assignment already exists
                $checkStmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM employee_allowance WHERE employee_id = ? AND allowance_id = ?");
                $checkStmt->execute([$employeeId, $allowanceId]);
                $exists = $checkStmt->fetch()['cnt'] > 0;

                if (!$exists) {
                    // Insert new assignment
                    $insertStmt = $this->db->prepare("INSERT INTO employee_allowance (employee_id, allowance_id, allowance_amount) VALUES (?, ?, ?)");
                    $insertStmt->execute([$employeeId, $allowanceId, $amount]);
                } else {
                    // Update existing assignment
                    $updateStmt = $this->db->prepare("UPDATE employee_allowance SET allowance_amount = ? WHERE employee_id = ? AND allowance_id = ?");
                    $updateStmt->execute([$amount, $employeeId, $allowanceId]);
                }
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Assign deductions to an employee
     * @param int $employeeId
     * @param array $deductions Array of deduction_type_id => amount pairs
     * @return bool
     */
    public function assignDeductionsToEmployee($employeeId, $deductions)
    {
        try {
            if (empty($deductions)) {
                return true; // No deductions to assign
            }

            foreach ($deductions as $deductionId => $amount) {
                // Check if assignment already exists
                $checkStmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM employee_deduction WHERE employee_id = ? AND deduction_type_id = ?");
                $checkStmt->execute([$employeeId, $deductionId]);
                $exists = $checkStmt->fetch()['cnt'] > 0;

                if (!$exists) {
                    // Insert new assignment
                    $insertStmt = $this->db->prepare("INSERT INTO employee_deduction (employee_id, deduction_type_id, amount) VALUES (?, ?, ?)");
                    $insertStmt->execute([$employeeId, $deductionId, $amount]);
                } else {
                    // Update existing assignment
                    $updateStmt = $this->db->prepare("UPDATE employee_deduction SET amount = ? WHERE employee_id = ? AND deduction_type_id = ?");
                    $updateStmt->execute([$amount, $employeeId, $deductionId]);
                }
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove all allowances for an employee that are not in the provided list
     * @param int $employeeId
     * @param array $keepAllowanceIds Array of allowance IDs to keep
     * @return bool
     */
    public function removeUnselectedAllowances($employeeId, $keepAllowanceIds = [])
    {
        try {
            if (empty($keepAllowanceIds)) {
                // Remove all allowances
                $stmt = $this->db->prepare("DELETE FROM employee_allowance WHERE employee_id = ?");
                $stmt->execute([$employeeId]);
            } else {
                // Remove allowances not in the keep list
                $placeholders = str_repeat('?,', count($keepAllowanceIds) - 1) . '?';
                $stmt = $this->db->prepare("DELETE FROM employee_allowance WHERE employee_id = ? AND allowance_id NOT IN ($placeholders)");
                $params = array_merge([$employeeId], $keepAllowanceIds);
                $stmt->execute($params);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove all deductions for an employee that are not in the provided list
     * @param int $employeeId
     * @param array $keepDeductionIds Array of deduction IDs to keep
     * @return bool
     */
    public function removeUnselectedDeductions($employeeId, $keepDeductionIds = [])
    {
        try {
            if (empty($keepDeductionIds)) {
                // Remove all deductions
                $stmt = $this->db->prepare("DELETE FROM employee_deduction WHERE employee_id = ?");
                $stmt->execute([$employeeId]);
            } else {
                // Remove deductions not in the keep list
                $placeholders = str_repeat('?,', count($keepDeductionIds) - 1) . '?';
                $stmt = $this->db->prepare("DELETE FROM employee_deduction WHERE employee_id = ? AND deduction_type_id NOT IN ($placeholders)");
                $params = array_merge([$employeeId], $keepDeductionIds);
                $stmt->execute($params);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Automatically create employee work schedule based on all future working calendar dates
     * Uses standard 8:00 AM - 5:00 PM shift for working days, Monday-Friday pattern
     */
    public function createAutomaticEmployeeSchedule($employeeId)
    {
        try {
            // Default shift times
            $shiftIn = '08:00:00';
            $shiftOut = '17:00:00';
            
            // Standard working days (Monday=1 to Friday=5)
            $standardWorkingDays = [1, 2, 3, 4, 5];
            
            // Get current user for created_by
            $createdBy = $_SESSION['user_id'] ?? 1;
            
            // Get all future working calendar entries (from today onwards, up to 1 year)
            $today = date('Y-m-d');
            $futureDate = date('Y-m-d', strtotime('+1 year'));
            
            $calendarStmt = $this->db->prepare("
                SELECT id as calendar_id, work_date, day_of_week, is_working, is_holiday, holiday_name
                FROM working_calendar 
                WHERE work_date >= ? AND work_date <= ?
                ORDER BY work_date
            ");
            $calendarStmt->execute([$today, $futureDate]);
            $calendarDays = $calendarStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no working calendar exists, create schedule for current month + next 6 months with Mon-Fri pattern
            if (empty($calendarDays)) {
                return $this->createScheduleWithoutCalendar($employeeId, $shiftIn, $shiftOut, $standardWorkingDays);
            }
            
            // Prepare insert statement for employee schedule
            $scheduleStmt = $this->db->prepare("
                INSERT INTO employee_schedule (employee_id, work_date, shift_in, shift_out, is_rest_day, calendar_id, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $schedulesCreated = 0;
            
            foreach ($calendarDays as $day) {
                $dayOfWeek = intval($day['day_of_week']);
                $workDate = $day['work_date'];
                $calendarId = $day['calendar_id'];
                $isWorkingDay = intval($day['is_working']) === 1;
                $isHoliday = intval($day['is_holiday']) === 1;
                
                // Determine if this is a rest day for the employee
                $isRestDay = 0;
                $notes = 'Auto-assigned working day';
                
                if (!$isWorkingDay || $isHoliday) {
                    // Company-wide non-working day or holiday
                    $isRestDay = 1;
                    $notes = $isHoliday ? "Holiday: " . ($day['holiday_name'] ?? 'Public Holiday') : 'Company rest day';
                } elseif (!in_array($dayOfWeek, $standardWorkingDays)) {
                    // Weekend (Saturday/Sunday) or non-standard working day
                    $isRestDay = 1;
                    $notes = 'Weekend rest day';
                }
                
                // Create schedule entry
                $scheduleStmt->execute([
                    $employeeId,
                    $workDate,
                    $isRestDay ? null : $shiftIn,
                    $isRestDay ? null : $shiftOut,
                    $isRestDay,
                    $calendarId,
                    $notes,
                    $createdBy
                ]);
                
                $schedulesCreated++;
            }
            
            return [
                'success' => true, 
                'message' => "Automatically created $schedulesCreated schedule entries for employee",
                'schedules_created' => $schedulesCreated
            ];
            
        } catch (Exception $e) {
            error_log("createAutomaticEmployeeSchedule error: " . $e->getMessage());
            throw new Exception("Failed to create automatic employee schedule: " . $e->getMessage());
        }
    }
    
    /**
     * Fallback method to create schedule when no working calendar exists
     */
    private function createScheduleWithoutCalendar($employeeId, $shiftIn, $shiftOut, $workingDays)
    {
        // Create schedule for next 6 months using Mon-Fri pattern
        $startDate = new DateTime();
        $endDate = clone $startDate;
        $endDate->modify('+6 months');
        
        $scheduleStmt = $this->db->prepare("
            INSERT INTO employee_schedule (employee_id, work_date, shift_in, shift_out, is_rest_day, notes, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $createdBy = $_SESSION['user_id'] ?? 1;
        $schedulesCreated = 0;
        $current = clone $startDate;
        
        while ($current <= $endDate) {
            $dayOfWeek = intval($current->format('N')); // 1=Monday, 7=Sunday
            $workDate = $current->format('Y-m-d');
            
            $isRestDay = !in_array($dayOfWeek, $workingDays) ? 1 : 0;
            $notes = $isRestDay ? 'Weekend rest day' : 'Auto-assigned working day (no calendar)';
            
            $scheduleStmt->execute([
                $employeeId,
                $workDate,
                $isRestDay ? null : $shiftIn,
                $isRestDay ? null : $shiftOut,
                $isRestDay,
                $notes,
                $createdBy
            ]);
            
            $schedulesCreated++;
            $current->modify('+1 day');
        }
        
        return [
            'success' => true, 
            'message' => "Created $schedulesCreated schedule entries (fallback mode - no working calendar)",
            'schedules_created' => $schedulesCreated
        ];
    }


}
?>

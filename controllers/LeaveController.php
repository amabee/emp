<?php
require_once '../shared/config.php';
require_once __DIR__ . '/SystemLogger.php';

class LeaveController
{
    private $db;
    private $logger;

    public function __construct()
    {
        $this->db = getDBConnection();
        $this->logger = new SystemLogger();
        if (!$this->db) {
            throw new Exception('Database connection failed');
        }
    }

    /**
     * Get all leave requests with filtering options
     */
    public function getLeaveRequests($filters = [])
    {
        try {
            $whereConditions = [];
            $params = [];

            // Build WHERE conditions based on filters
            if (!empty($filters['employee_id'])) {
                $whereConditions[] = "lr.employee_id = :employee_id";
                $params['employee_id'] = $filters['employee_id'];
            }

            if (!empty($filters['status'])) {
                $whereConditions[] = "lr.status = :status";
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['leave_type'])) {
                $whereConditions[] = "lr.leave_type = :leave_type";
                $params['leave_type'] = $filters['leave_type'];
            }

            if (!empty($filters['department_id'])) {
                $whereConditions[] = "d.department_id = :department_id";
                $params['department_id'] = $filters['department_id'];
            }

            if (!empty($filters['date_from'])) {
                $whereConditions[] = "lr.start_date >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $whereConditions[] = "lr.end_date <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }

            if (!empty($filters['search'])) {
                $whereConditions[] = "(e.first_name LIKE :search OR e.last_name LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);

            $sql = "SELECT 
                        lr.leave_id,
                        lr.employee_id,
                        lr.leave_type,
                        lr.start_date,
                        lr.end_date,
                        lr.status,
                        lr.reason,
                        lr.created_at,
                        lr.updated_at,
                        lr.approved_by,
                        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                        e.first_name,
                        e.last_name,
                        d.department_name,
                        jp.position_name,
                        CONCAT(approver.first_name, ' ', approver.last_name) as approved_by_name,
                        DATEDIFF(lr.end_date, lr.start_date) + 1 as total_days
                    FROM leave_records lr
                    LEFT JOIN employees e ON lr.employee_id = e.employee_id
                    LEFT JOIN department d ON e.department_id = d.department_id
                    LEFT JOIN job_position jp ON e.position_id = jp.position_id
                    LEFT JOIN users u_approver ON lr.approved_by = u_approver.user_id
                    LEFT JOIN employees approver ON u_approver.user_id = approver.user_id
                    $whereClause
                    ORDER BY lr.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $leaves
            ];

        } catch (Exception $e) {
            $this->logger->log(null, 'error', 'Failed to fetch leave requests: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error fetching leave requests: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Submit a new leave request
     */
    public function addLeaveRequest($data, $userId)
    {
        try {
            $this->db->beginTransaction();

            // Get employee_id from user_id
            $employeeId = $this->getEmployeeIdFromUserId($userId);
            if (!$employeeId) {
                throw new Exception('Employee record not found');
            }

            // Validate required fields
            $requiredFields = ['leave_type', 'start_date', 'end_date', 'reason'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }

            // Validate dates
            $startDate = new DateTime($data['start_date']);
            $endDate = new DateTime($data['end_date']);
            $today = new DateTime();

            if ($startDate < $today) {
                throw new Exception('Start date cannot be in the past');
            }

            if ($endDate < $startDate) {
                throw new Exception('End date cannot be before start date');
            }

            // Check for overlapping leave requests
            if ($this->hasOverlappingLeave($employeeId, $data['start_date'], $data['end_date'])) {
                throw new Exception('You already have a leave request for the selected dates');
            }

            // Check leave balance (if applicable)
            $leaveBalance = $this->getEmployeeLeaveBalance($employeeId, $data['leave_type']);
            $requestedDays = $startDate->diff($endDate)->days + 1;

            if ($data['half_day'] ?? false) {
                $requestedDays = 0.5;
            }

            if ($leaveBalance['available'] < $requestedDays) {
                throw new Exception("Insufficient leave balance. Available: {$leaveBalance['available']} days, Requested: $requestedDays days");
            }

            // Insert leave request
            $sql = "INSERT INTO leave_records (employee_id, leave_type, start_date, end_date, status, reason, created_at, updated_at, half_day) 
                    VALUES (:employee_id, :leave_type, :start_date, :end_date, 'Pending', :reason, NOW(), NOW(), :half_day)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'employee_id' => $employeeId,
                'leave_type' => $data['leave_type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'reason' => $data['reason'],
                'half_day' => isset($data['half_day']) ? 1 : 0
            ]);

            $leaveId = $this->db->lastInsertId();

            $this->db->commit();

            // Log activity
            $this->logger->log($userId, 'leave_request', "Leave request submitted for {$data['start_date']} to {$data['end_date']}");

            return [
                'success' => true,
                'message' => 'Leave request submitted successfully',
                'leave_id' => $leaveId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logger->log($userId, 'error', 'Failed to submit leave request: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update leave request status (approve/reject)
     */
    public function updateLeaveStatus($leaveId, $status, $userId, $comments = '')
    {
        try {
            $this->db->beginTransaction();

            // Validate status
            if (!in_array($status, ['Approved', 'Rejected'])) {
                throw new Exception('Invalid status. Must be Approved or Rejected');
            }

            // Get leave request details
            $leaveRequest = $this->getLeaveRequestById($leaveId);
            if (!$leaveRequest['success']) {
                throw new Exception('Leave request not found');
            }

            $leave = $leaveRequest['data'];

            if ($leave['status'] !== 'Pending') {
                throw new Exception('Leave request has already been processed');
            }

            // Update leave request
            $sql = "UPDATE leave_records 
                    SET status = :status, 
                        approved_by = :approved_by, 
                        updated_at = NOW(),
                        comments = :comments
                    WHERE leave_id = :leave_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'status' => $status,
                'approved_by' => $userId,
                'comments' => $comments,
                'leave_id' => $leaveId
            ]);

            // If approved, create attendance records for leave days
            if ($status === 'Approved') {
                $this->createLeaveAttendanceRecords($leave);
            }

            $this->db->commit();

            // Log activity
            $this->logger->log($userId, 'leave_' . strtolower($status), "Leave request {$status} for employee {$leave['employee_name']}");

            return [
                'success' => true,
                'message' => "Leave request $status successfully"
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logger->log($userId, 'error', 'Failed to update leave status: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get leave request by ID
     */
    public function getLeaveRequestById($leaveId)
    {
        try {
            $sql = "SELECT 
                        lr.*,
                        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                        d.department_name,
                        jp.position_name,
                        DATEDIFF(lr.end_date, lr.start_date) + 1 as total_days
                    FROM leave_records lr
                    LEFT JOIN employees e ON lr.employee_id = e.employee_id
                    LEFT JOIN department d ON e.department_id = d.department_id
                    LEFT JOIN job_position jp ON e.position_id = jp.position_id
                    WHERE lr.leave_id = :leave_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['leave_id' => $leaveId]);
            $leave = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$leave) {
                return [
                    'success' => false,
                    'message' => 'Leave request not found'
                ];
            }

            return [
                'success' => true,
                'data' => $leave
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching leave request: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get employee leave balances
     */
    public function getEmployeeLeaveBalances($employeeId = null)
    {
        try {
            $whereClause = $employeeId ? "WHERE e.employee_id = :employee_id" : "";
            $params = $employeeId ? ['employee_id' => $employeeId] : [];

            $sql = "SELECT 
                        e.employee_id,
                        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                        d.department_name,
                        -- Calculate leave balances
                        15 as vacation_total,
                        10 as sick_total,
                        5 as personal_total,
                        30 as emergency_total,
                        COALESCE(vacation_used.days, 0) as vacation_used,
                        COALESCE(sick_used.days, 0) as sick_used,
                        COALESCE(personal_used.days, 0) as personal_used,
                        COALESCE(emergency_used.days, 0) as emergency_used,
                        (15 - COALESCE(vacation_used.days, 0)) as vacation_remaining,
                        (10 - COALESCE(sick_used.days, 0)) as sick_remaining,
                        (5 - COALESCE(personal_used.days, 0)) as personal_remaining,
                        (30 - COALESCE(emergency_used.days, 0)) as emergency_remaining
                    FROM employees e
                    LEFT JOIN department d ON e.department_id = d.department_id
                    LEFT JOIN (
                        SELECT employee_id, 
                               SUM(DATEDIFF(end_date, start_date) + 1) as days
                        FROM leave_records 
                        WHERE leave_type = 'Vacation' 
                          AND status = 'Approved' 
                          AND YEAR(start_date) = YEAR(CURDATE())
                        GROUP BY employee_id
                    ) vacation_used ON e.employee_id = vacation_used.employee_id
                    LEFT JOIN (
                        SELECT employee_id, 
                               SUM(DATEDIFF(end_date, start_date) + 1) as days
                        FROM leave_records 
                        WHERE leave_type = 'Sick' 
                          AND status = 'Approved' 
                          AND YEAR(start_date) = YEAR(CURDATE())
                        GROUP BY employee_id
                    ) sick_used ON e.employee_id = sick_used.employee_id
                    LEFT JOIN (
                        SELECT employee_id, 
                               SUM(DATEDIFF(end_date, start_date) + 1) as days
                        FROM leave_records 
                        WHERE leave_type = 'Personal' 
                          AND status = 'Approved' 
                          AND YEAR(start_date) = YEAR(CURDATE())
                        GROUP BY employee_id
                    ) personal_used ON e.employee_id = personal_used.employee_id
                    LEFT JOIN (
                        SELECT employee_id, 
                               SUM(DATEDIFF(end_date, start_date) + 1) as days
                        FROM leave_records 
                        WHERE leave_type = 'Emergency' 
                          AND status = 'Approved' 
                          AND YEAR(start_date) = YEAR(CURDATE())
                        GROUP BY employee_id
                    ) emergency_used ON e.employee_id = emergency_used.employee_id
                    $whereClause
                    ORDER BY e.first_name, e.last_name";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $balances
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching leave balances: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get leave statistics for dashboard
     */
    public function getLeaveStatistics()
    {
        try {
            $stats = [];

            // Get current year statistics
            $sql = "SELECT 
                        COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending,
                        COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved,
                        COUNT(CASE WHEN status = 'Rejected' THEN 1 END) as rejected,
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'Approved' THEN DATEDIFF(end_date, start_date) + 1 ELSE 0 END) as total_days_approved
                    FROM leave_records 
                    WHERE YEAR(start_date) = YEAR(CURDATE())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $yearStats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get current month statistics
            $sql = "SELECT 
                        COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending,
                        COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved,
                        COUNT(CASE WHEN status = 'Rejected' THEN 1 END) as rejected,
                        COUNT(*) as total
                    FROM leave_records 
                    WHERE YEAR(start_date) = YEAR(CURDATE()) 
                      AND MONTH(start_date) = MONTH(CURDATE())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $monthStats = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'year' => $yearStats,
                    'month' => $monthStats
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching leave statistics: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Private helper methods
     */
    private function getEmployeeIdFromUserId($userId)
    {
        try {
            $sql = "SELECT employee_id FROM employees WHERE user_id = :user_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['employee_id'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function hasOverlappingLeave($employeeId, $startDate, $endDate)
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM leave_records 
                    WHERE employee_id = :employee_id 
                      AND status IN ('Pending', 'Approved')
                      AND (
                          (start_date <= :start_date AND end_date >= :start_date) OR
                          (start_date <= :end_date AND end_date >= :end_date) OR
                          (start_date >= :start_date AND end_date <= :end_date)
                      )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'employee_id' => $employeeId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getEmployeeLeaveBalance($employeeId, $leaveType)
    {
        // Default annual leave entitlements
        $entitlements = [
            'Vacation' => 15,
            'Sick' => 10,
            'Personal' => 5,
            'Emergency' => 30,
            'Maternity' => 90,
            'Paternity' => 7
        ];

        $total = $entitlements[$leaveType] ?? 0;

        try {
            // Get used leave days for current year
            $sql = "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as used_days
                    FROM leave_records 
                    WHERE employee_id = :employee_id 
                      AND leave_type = :leave_type 
                      AND status = 'Approved'
                      AND YEAR(start_date) = YEAR(CURDATE())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'employee_id' => $employeeId,
                'leave_type' => $leaveType
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $used = $result['used_days'] ?? 0;

            return [
                'total' => $total,
                'used' => $used,
                'available' => $total - $used
            ];
        } catch (Exception $e) {
            return [
                'total' => $total,
                'used' => 0,
                'available' => $total
            ];
        }
    }

    private function createLeaveAttendanceRecords($leave)
    {
        try {
            // Create attendance records for approved leave days
            $startDate = new DateTime($leave['start_date']);
            $endDate = new DateTime($leave['end_date']);

            while ($startDate <= $endDate) {
                $currentDate = $startDate->format('Y-m-d');

                // Check if it's a working day (skip weekends unless it's emergency leave)
                $dayOfWeek = $startDate->format('N'); // 1 = Monday, 7 = Sunday
                $isWorkingDay = ($dayOfWeek < 6) || ($leave['leave_type'] === 'Emergency');

                if ($isWorkingDay) {
                    // Get employee's schedule info for this date
                    $scheduleInfo = $this->getEmployeeScheduleInfo($leave['employee_id'], $currentDate);

                    // Insert attendance record for leave day
                    $sql = "INSERT INTO attendance (employee_id, schedule_id, calendar_id, date, status, remarks, created_at) 
                            VALUES (:employee_id, :schedule_id, :calendar_id, :date, :status, :remarks, NOW())
                            ON DUPLICATE KEY UPDATE 
                            status = VALUES(status), 
                            remarks = VALUES(remarks),
                            updated_at = NOW()";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        'employee_id' => $leave['employee_id'],
                        'schedule_id' => $scheduleInfo['schedule_id'],
                        'calendar_id' => $scheduleInfo['calendar_id'],
                        'date' => $currentDate,
                        'status' => 'on_leave',
                        'remarks' => "On {$leave['leave_type']} leave - {$leave['reason']}"
                    ]);
                }

                $startDate->add(new DateInterval('P1D'));
            }

        } catch (Exception $e) {
            // Log error but don't fail the leave approval
            $this->logger->log(null, 'error', 'Failed to create leave attendance records: ' . $e->getMessage());
        }
    }

    /**
     * Get employee's schedule and calendar information for a given date
     */
    private function getEmployeeScheduleInfo($employeeId, $date)
    {
        try {
            // Get employee's schedule with calendar_id from employee_schedule table
            $scheduleSql = "SELECT schedule_id, calendar_id FROM employee_schedule 
                             WHERE employee_id = :employee_id 
                             AND work_date <= :date
                             ORDER BY work_date DESC 
                             LIMIT 1";
            $scheduleStmt = $this->db->prepare($scheduleSql);
            $scheduleStmt->execute(['employee_id' => $employeeId, 'date' => $date]);
            $scheduleResult = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

            return [
                'schedule_id' => $scheduleResult ? $scheduleResult['schedule_id'] : null,
                'calendar_id' => $scheduleResult ? $scheduleResult['calendar_id'] : null
            ];

        } catch (Exception $e) {
            // Return nulls if there's an error
            return [
                'schedule_id' => null,
                'calendar_id' => null
            ];
        }
    }
}
?>

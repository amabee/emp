<?php
require_once __DIR__ . '/SystemLogger.php';

class AttendanceController
{
    private $db;
    private $logger;

    public function __construct()
    {
        $this->db = getDBConnection();
        $this->logger = new SystemLogger();
        if (!$this->db) throw new Exception('DB connection failed');
    }

    /**
     * Get attendance records with filtering and search capabilities
     * @param array $filters - date, department, status, search, limit, offset
     * @return array
     */
    public function getAttendanceRecords($filters = [])
    {
        $where = [];
        $params = [];
        
        // Base query with employee and department joins - using generated employee number
        $sql = "SELECT 
                    a.attendance_id,
                    a.employee_id,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    CONCAT('EMP-', LPAD(e.employee_id, 4, '0')) as employee_number,
                    d.department_name,
                    a.date,
                    a.time_in,
                    a.time_out,
                    a.status,
                    a.remarks,
                    a.schedule_id,
                    a.calendar_id
                FROM attendance a
                LEFT JOIN employees e ON a.employee_id = e.employee_id
                LEFT JOIN department d ON e.department_id = d.department_id
                WHERE e.employment_status = 1"; // Only active employees
        
        // Apply filters
        if (!empty($filters['date'])) {
            $sql .= " AND a.date = ?";
            $params[] = $filters['date'];
        }
        
        if (!empty($filters['department'])) {
            if (is_numeric($filters['department'])) {
                $sql .= " AND e.department_id = ?";
                $params[] = (int)$filters['department'];
            } else {
                $sql .= " AND d.department_name = ?";
                $params[] = $filters['department'];
            }
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND LOWER(a.status) = LOWER(?)";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (CONCAT(e.first_name, ' ', e.last_name) LIKE ? OR CONCAT('EMP-', LPAD(e.employee_id, 4, '0')) LIKE ? OR d.department_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add ordering
        $sql .= " ORDER BY a.date DESC, a.attendance_id DESC";
        
        // Add pagination if specified (using direct values instead of parameters for LIMIT)
        if (isset($filters['limit'])) {
            $limit = (int)$filters['limit'];
            if (isset($filters['offset'])) {
                $offset = (int)$filters['offset'];
                $sql .= " LIMIT $offset, $limit";
            } else {
                $sql .= " LIMIT $limit";
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the data for frontend
            return array_map([$this, 'formatAttendanceRecord'], $records);
            
        } catch (Exception $e) {
            error_log("AttendanceController::getAttendanceRecords Error: " . $e->getMessage());
            throw new Exception('Failed to fetch attendance records: ' . $e->getMessage());
        }
    }

    /**
     * Get attendance count for pagination
     * @param array $filters
     * @return int
     */
    public function getAttendanceCount($filters = [])
    {
        $where = [];
        $params = [];
        
        $sql = "SELECT COUNT(*) as total
                FROM attendance a
                LEFT JOIN employees e ON a.employee_id = e.employee_id
                LEFT JOIN department d ON e.department_id = d.department_id
                WHERE e.employment_status = 1";
        
        // Apply same filters as getAttendanceRecords
        if (!empty($filters['date'])) {
            $sql .= " AND a.date = ?";
            $params[] = $filters['date'];
        }
        
        if (!empty($filters['department'])) {
            if (is_numeric($filters['department'])) {
                $sql .= " AND e.department_id = ?";
                $params[] = (int)$filters['department'];
            } else {
                $sql .= " AND d.department_name = ?";
                $params[] = $filters['department'];
            }
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND LOWER(a.status) = LOWER(?)";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (CONCAT(e.first_name, ' ', e.last_name) LIKE ? OR CONCAT('EMP-', LPAD(e.employee_id, 4, '0')) LIKE ? OR d.department_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("AttendanceController::getAttendanceCount Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get attendance details by attendance ID
     * @param int $attendanceId
     * @return array|null
     */
    public function getAttendanceDetails($attendanceId)
    {
        $sql = "SELECT 
                    a.attendance_id,
                    a.employee_id,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    CONCAT('EMP-', LPAD(e.employee_id, 4, '0')) as employee_number,
                    d.department_name,
                    p.position_name,
                    a.date,
                    a.time_in,
                    a.time_out,
                    a.status,
                    a.remarks,
                    a.schedule_id,
                    a.calendar_id,
                    -- Calculate hours worked
                    CASE 
                        WHEN a.time_in IS NOT NULL AND a.time_out IS NOT NULL 
                        THEN TIMESTAMPDIFF(MINUTE, 
                            CONCAT(a.date, ' ', a.time_in), 
                            CONCAT(a.date, ' ', a.time_out)
                        ) / 60.0
                        ELSE NULL 
                    END as hours_worked
                FROM attendance a
                LEFT JOIN employees e ON a.employee_id = e.employee_id
                LEFT JOIN department d ON e.department_id = d.department_id
                LEFT JOIN job_position p ON e.position_id = p.position_id
                WHERE a.attendance_id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$attendanceId]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($record) {
                return $this->formatAttendanceRecord($record, true); // detailed format
            }
            return null;
        } catch (Exception $e) {
            error_log("AttendanceController::getAttendanceDetails Error: " . $e->getMessage());
            throw new Exception('Failed to fetch attendance details: ' . $e->getMessage());
        }
    }

    /**
     * Get all departments for filter dropdown
     * @return array
     */
    public function getDepartments()
    {
        $sql = "SELECT department_id, department_name FROM department ORDER BY department_name";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AttendanceController::getDepartments Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get attendance summary for a specific date
     * @param string $date
     * @return array
     */
    public function getAttendanceSummary($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $sql = "SELECT 
                    a.status,
                    COUNT(*) as count
                FROM attendance a
                LEFT JOIN employees e ON a.employee_id = e.employee_id
                WHERE a.date = ? AND e.employment_status = 1
                GROUP BY a.status";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$date]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $summary = [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'on_leave' => 0,
                'overtime' => 0
            ];
            
            foreach ($results as $result) {
                $status = strtolower($result['status']);
                $count = (int)$result['count'];
                $summary['total'] += $count;
                
                switch ($status) {
                    case 'present':
                        $summary['present'] = $count;
                        break;
                    case 'absent':
                        $summary['absent'] = $count;
                        break;
                    case 'late':
                        $summary['late'] = $count;
                        break;
                    case 'on leave':
                        $summary['on_leave'] = $count;
                        break;
                    case 'overtime':
                        $summary['overtime'] = $count;
                        break;
                }
            }
            
            return $summary;
        } catch (Exception $e) {
            error_log("AttendanceController::getAttendanceSummary Error: " . $e->getMessage());
            return ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'on_leave' => 0, 'overtime' => 0];
        }
    }

    /**
     * Format attendance record for consistent output
     * @param array $record
     * @param bool $detailed
     * @return array
     */
    private function formatAttendanceRecord($record, $detailed = false)
    {
        $formatted = [
            'attendance_id' => (int)$record['attendance_id'],
            'employee_id' => (int)$record['employee_id'],
            'employee_name' => $record['employee_name'] ?? 'Unknown Employee',
            'employee_number' => $record['employee_number'] ?? '',
            'department_name' => $record['department_name'] ?? 'No Department',
            'date' => $record['date'],
            'time_in' => $record['time_in'] ? date('H:i', strtotime($record['time_in'])) : null,
            'time_out' => $record['time_out'] ? date('H:i', strtotime($record['time_out'])) : null,
            'status' => $record['status'],
            'remarks' => $record['remarks'] ?? '',
            'status_badge' => $this->getStatusBadge($record['status'])
        ];
        
        if ($detailed) {
            $formatted['position_name'] = $record['position_name'] ?? '';
            $formatted['schedule_id'] = $record['schedule_id'];
            $formatted['calendar_id'] = $record['calendar_id'];
            $formatted['hours_worked'] = $record['hours_worked'] ? round($record['hours_worked'], 2) : null;
            $formatted['time_in_full'] = $record['time_in'];
            $formatted['time_out_full'] = $record['time_out'];
        }
        
        return $formatted;
    }

    /**
     * Get status badge HTML for display
     * @param string $status
     * @return string
     */
    private function getStatusBadge($status)
    {
        switch (strtolower($status)) {
            case 'present':
                return '<span class="badge bg-success">Present</span>';
            case 'absent':
                return '<span class="badge bg-danger">Absent</span>';
            case 'late':
                return '<span class="badge bg-warning text-dark">Late</span>';
            case 'on leave':
                return '<span class="badge bg-info">On Leave</span>';
            case 'overtime':
                return '<span class="badge bg-primary">Overtime</span>';
            default:
                return '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
        }
    }
}
?>

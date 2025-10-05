<?php

class DashboardController
{
    private $db;

    public function __construct()
    {
        $this->db = getDBConnection();
        if (!$this->db) {
            throw new Exception("Database connection failed");
        }
    }

    public function getDashboardStats()
    {
        try {
            $stats = [];

            // Get total employees
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employees WHERE employment_status = 1");
            $stmt->execute();
            $stats['total_employees'] = $stmt->fetch()['count'];

            // Get employee growth this month
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM employees 
                WHERE employment_status = 1 
                AND date_hired >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            ");
            $stmt->execute();
            $stats['new_employees_this_month'] = $stmt->fetch()['count'];

            // Calculate percentage growth (rough estimate)
            $stats['employee_growth_percentage'] = $stats['total_employees'] > 0 ? 
                round(($stats['new_employees_this_month'] / $stats['total_employees']) * 100, 1) : 0;

            // Get pending leave requests (if table exists)
            try {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'");
                $stmt->execute();
                $stats['pending_leaves'] = $stmt->fetch()['count'];
            } catch (Exception $e) {
                // Table might not exist yet
                $stats['pending_leaves'] = 0;
            }

            // Get total departments
            $stmt = $this->db->prepare("SELECT COUNT(DISTINCT department_name) as count FROM department");
            $stmt->execute();
            $stats['total_departments'] = $stmt->fetch()['count'];

            // Get total active users
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE active_status = 'active'");
            $stmt->execute();
            $stats['total_users'] = $stmt->fetch()['count'];

            return $stats;

        } catch (Exception $e) {
            return [
                'total_employees' => 0,
                'new_employees_this_month' => 0,
                'employee_growth_percentage' => 0,
                'pending_leaves' => 0,
                'total_departments' => 0,
                'total_users' => 0
            ];
        }
    }

    public function getRecentActivity($limit = 10)
    {
        try {
            $activities = [];

            // Get recent employee additions
            $stmt = $this->db->prepare("
                SELECT 
                    e.first_name, 
                    e.last_name, 
                    e.date_hired,
                    jp.position_name,
                    'employee_added' as activity_type
                FROM employees e
                LEFT JOIN job_position jp ON e.position_id = jp.id
                WHERE e.employment_status = 1
                ORDER BY e.date_hired DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $employees = $stmt->fetchAll();

            foreach ($employees as $emp) {
                $activities[] = [
                    'type' => 'employee_added',
                    'icon' => 'bx-check',
                    'color' => 'success',
                    'title' => 'New Employee Added',
                    'description' => $emp['first_name'] . ' ' . $emp['last_name'] . ' joined as ' . ($emp['position_name'] ?? 'Employee'),
                    'time' => $this->timeAgo($emp['date_hired']),
                    'timestamp' => $emp['date_hired']
                ];
            }

            // Get recent leave requests (if table exists)
            try {
                $stmt = $this->db->prepare("
                    SELECT 
                        lr.created_at,
                        e.first_name,
                        e.last_name,
                        lr.status,
                        'leave_request' as activity_type
                    FROM leave_requests lr
                    JOIN employees e ON lr.employee_id = e.id
                    ORDER BY lr.created_at DESC
                    LIMIT ?
                ");
                $stmt->execute([$limit]);
                $leaves = $stmt->fetchAll();

                foreach ($leaves as $leave) {
                    $activities[] = [
                        'type' => 'leave_request',
                        'icon' => 'bx-time',
                        'color' => $leave['status'] === 'pending' ? 'info' : ($leave['status'] === 'approved' ? 'success' : 'warning'),
                        'title' => 'Leave Request',
                        'description' => $leave['first_name'] . ' ' . $leave['last_name'] . ' requested leave',
                        'time' => $this->timeAgo($leave['created_at']),
                        'timestamp' => $leave['created_at']
                    ];
                }
            } catch (Exception $e) {
                // Leave requests table might not exist
            }

            // Sort activities by timestamp
            usort($activities, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            return array_slice($activities, 0, $limit);

        } catch (Exception $e) {
            return [];
        }
    }

    public function getAttendanceData()
    {
        try {
            // This is a placeholder for attendance data
            // You'll need to implement based on your attendance tracking system
            $attendanceData = [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'present' => [85, 92, 88, 90, 87, 45, 20],
                'absent' => [15, 8, 12, 10, 13, 5, 2],
                'late' => [5, 3, 7, 4, 6, 2, 1]
            ];

            return $attendanceData;

        } catch (Exception $e) {
            return [
                'labels' => [],
                'present' => [],
                'absent' => [],
                'late' => []
            ];
        }
    }

    public function getDepartmentStats()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    d.department_name,
                    COUNT(e.id) as employee_count
                FROM department d
                LEFT JOIN employees e ON d.department_id = e.department_id AND e.employment_status = 1
                GROUP BY d.department_id, d.department_name
                ORDER BY employee_count DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();

        } catch (Exception $e) {
            return [];
        }
    }

    private function timeAgo($datetime)
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . ' minutes ago';
        if ($time < 86400) return floor($time/3600) . ' hours ago';
        if ($time < 2592000) return floor($time/86400) . ' days ago';
        if ($time < 31536000) return floor($time/2592000) . ' months ago';
        
        return floor($time/31536000) . ' years ago';
    }
}
?>

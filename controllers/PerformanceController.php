<?php

class PerformanceController
{
    private $db;

    public function __construct()
    {
        $this->db = getDBConnection();
        if (!$this->db) {
            throw new Exception("Database connection failed");
        }
    }

    /**
     * Get all performance evaluations with employee details
     */
    public function getAllPerformances($filters = [])
    {
        try {
            $sql = "SELECT 
                        p.performance_id,
                        p.employee_id,
                        p.period_start,
                        p.period_end,
                        p.rating,
                        p.remarks,
                        p.evaluated_by,
                        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                        d.department_name,
                        pos.position_name,
                        CONCAT(eval_user.first_name, ' ', eval_user.last_name) as evaluator_name
                    FROM performance p
                    LEFT JOIN employees e ON p.employee_id = e.employee_id
                    LEFT JOIN department d ON e.department_id = d.department_id
                    LEFT JOIN job_position pos ON e.position_id = pos.position_id
                    LEFT JOIN users eval_u ON p.evaluated_by = eval_u.user_id
                    LEFT JOIN employees eval_user ON eval_u.user_id = eval_user.user_id";

            $conditions = [];
            $params = [];

            if (!empty($filters['employee_id'])) {
                $conditions[] = "p.employee_id = ?";
                $params[] = $filters['employee_id'];
            }

            if (!empty($filters['department_id'])) {
                $conditions[] = "e.department_id = ?";
                $params[] = $filters['department_id'];
            }

            if (!empty($filters['period_year'])) {
                $conditions[] = "YEAR(p.period_start) = ?";
                $params[] = $filters['period_year'];
            }

            if (!empty($filters['rating_min'])) {
                $conditions[] = "p.rating >= ?";
                $params[] = $filters['rating_min'];
            }

            if (!empty($filters['rating_max'])) {
                $conditions[] = "p.rating <= ?";
                $params[] = $filters['rating_max'];
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY p.period_end DESC, e.first_name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching performance evaluations: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get performance evaluation by ID
     */
    public function getPerformanceById($performanceId)
    {
        try {
            $sql = "SELECT 
                        p.*,
                        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                        d.department_name,
                        pos.position_name,
                        CONCAT(eval_user.first_name, ' ', eval_user.last_name) as evaluator_name
                    FROM performance p
                    LEFT JOIN employees e ON p.employee_id = e.employee_id
                    LEFT JOIN department d ON e.department_id = d.department_id
                    LEFT JOIN position pos ON e.position_id = pos.position_id
                    LEFT JOIN users eval_u ON p.evaluated_by = eval_u.user_id
                    LEFT JOIN employees eval_user ON eval_u.user_id = eval_user.user_id
                    WHERE p.performance_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$performanceId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return [
                    'success' => true,
                    'data' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Performance evaluation not found'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching performance evaluation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create new performance evaluation
     */
    public function createPerformance($data)
    {
        try {
            $this->db->beginTransaction();

            // Validate required fields
            $required = ['employee_id', 'period_start', 'period_end', 'rating'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }

            // Validate rating (1-5 scale)
            if ($data['rating'] < 1 || $data['rating'] > 5) {
                throw new Exception("Rating must be between 1 and 5");
            }

            // Validate dates
            if (strtotime($data['period_start']) >= strtotime($data['period_end'])) {
                throw new Exception("Period start date must be before end date");
            }

            // Check for overlapping periods for same employee
            $checkSql = "SELECT COUNT(*) FROM performance 
                        WHERE employee_id = ? 
                        AND performance_id != ?
                        AND (
                            (period_start BETWEEN ? AND ?) OR 
                            (period_end BETWEEN ? AND ?) OR
                            (period_start <= ? AND period_end >= ?)
                        )";
            
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([
                $data['employee_id'],
                $data['performance_id'] ?? 0,
                $data['period_start'], $data['period_end'],
                $data['period_start'], $data['period_end'],
                $data['period_start'], $data['period_end']
            ]);

            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("Performance evaluation period overlaps with existing evaluation for this employee");
            }

            $sql = "INSERT INTO performance (employee_id, period_start, period_end, rating, remarks, evaluated_by) 
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['employee_id'],
                $data['period_start'],
                $data['period_end'],
                $data['rating'],
                $data['remarks'] ?? null,
                $_SESSION['user_id'] ?? null
            ]);

            if ($result) {
                $performanceId = $this->db->lastInsertId();
                $this->db->commit();

                // Get employee name for logging
                $empStmt = $this->db->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM employees WHERE employee_id = ?");
                $empStmt->execute([$data['employee_id']]);
                $employeeName = $empStmt->fetchColumn();

                return [
                    'success' => true,
                    'message' => 'Performance evaluation created successfully',
                    'performance_id' => $performanceId,
                    'employee_name' => $employeeName
                ];
            } else {
                throw new Exception("Failed to create performance evaluation");
            }

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error creating performance evaluation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update performance evaluation
     */
    public function updatePerformance($performanceId, $data)
    {
        try {
            $this->db->beginTransaction();

            // Check if performance evaluation exists
            $checkStmt = $this->db->prepare("SELECT employee_id FROM performance WHERE performance_id = ?");
            $checkStmt->execute([$performanceId]);
            
            if (!$checkStmt->fetch()) {
                throw new Exception("Performance evaluation not found");
            }

            // Validate rating if provided
            if (isset($data['rating']) && ($data['rating'] < 1 || $data['rating'] > 5)) {
                throw new Exception("Rating must be between 1 and 5");
            }

            // Validate dates if provided
            if (isset($data['period_start']) && isset($data['period_end'])) {
                if (strtotime($data['period_start']) >= strtotime($data['period_end'])) {
                    throw new Exception("Period start date must be before end date");
                }
            }

            // Build update query dynamically
            $updateFields = [];
            $params = [];

            $allowedFields = ['employee_id', 'period_start', 'period_end', 'rating', 'remarks'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            if (empty($updateFields)) {
                throw new Exception("No fields to update");
            }

            $sql = "UPDATE performance SET " . implode(", ", $updateFields) . " WHERE performance_id = ?";
            $params[] = $performanceId;

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $this->db->commit();
                return [
                    'success' => true,
                    'message' => 'Performance evaluation updated successfully'
                ];
            } else {
                throw new Exception("Failed to update performance evaluation");
            }

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error updating performance evaluation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete performance evaluation
     */
    public function deletePerformance($performanceId)
    {
        try {
            $this->db->beginTransaction();

            // Get performance details before deletion for logging
            $stmt = $this->db->prepare("
                SELECT p.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name
                FROM performance p
                LEFT JOIN employees e ON p.employee_id = e.employee_id
                WHERE p.performance_id = ?
            ");
            $stmt->execute([$performanceId]);
            $performance = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$performance) {
                throw new Exception("Performance evaluation not found");
            }

            // Delete the performance evaluation
            $deleteStmt = $this->db->prepare("DELETE FROM performance WHERE performance_id = ?");
            $result = $deleteStmt->execute([$performanceId]);

            if ($result) {
                $this->db->commit();
                return [
                    'success' => true,
                    'message' => 'Performance evaluation deleted successfully',
                    'employee_name' => $performance['employee_name']
                ];
            } else {
                throw new Exception("Failed to delete performance evaluation");
            }

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error deleting performance evaluation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStatistics($filters = [])
    {
        try {
            $whereClause = "";
            $params = [];

            if (!empty($filters['year'])) {
                $whereClause = "WHERE YEAR(p.period_start) = ?";
                $params[] = $filters['year'];
            }

            // Get overall statistics
            $sql = "SELECT 
                        COUNT(*) as total_evaluations,
                        AVG(rating) as average_rating,
                        COUNT(DISTINCT employee_id) as total_employees_evaluated,
                        SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as excellent_performers,
                        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as satisfactory_performers,
                        SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as needs_improvement
                    FROM performance p $whereClause";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get rating distribution
            $ratingDistSql = "SELECT 
                                rating,
                                COUNT(*) as count,
                                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM performance p2 $whereClause), 2) as percentage
                              FROM performance p $whereClause
                              GROUP BY rating
                              ORDER BY rating DESC";

            $ratingStmt = $this->db->prepare($ratingDistSql);
            $ratingStmt->execute($params);
            $ratingDistribution = $ratingStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get department performance
            $deptSql = "SELECT 
                            d.department_name,
                            COUNT(*) as evaluations_count,
                            AVG(p.rating) as average_rating
                        FROM performance p
                        LEFT JOIN employees e ON p.employee_id = e.employee_id
                        LEFT JOIN department d ON e.department_id = d.department_id
                        $whereClause
                        GROUP BY d.department_id, d.department_name
                        ORDER BY average_rating DESC";

            $deptStmt = $this->db->prepare($deptSql);
            $deptStmt->execute($params);
            $departmentStats = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'overall' => $stats,
                    'rating_distribution' => $ratingDistribution,
                    'department_performance' => $departmentStats
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching performance statistics: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get employee's performance history
     */
    public function getEmployeePerformanceHistory($employeeId)
    {
        try {
            $sql = "SELECT 
                        p.*,
                        CONCAT(eval_user.first_name, ' ', eval_user.last_name) as evaluator_name
                    FROM performance p
                    LEFT JOIN users eval_u ON p.evaluated_by = eval_u.user_id
                    LEFT JOIN employees eval_user ON eval_u.user_id = eval_user.user_id
                    WHERE p.employee_id = ?
                    ORDER BY p.period_end DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$employeeId]);
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching employee performance history: ' . $e->getMessage()
            ];
        }
    }
}
?>

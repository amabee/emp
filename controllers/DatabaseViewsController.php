<?php
require_once __DIR__ . '/../shared/config.php';

class DatabaseViewsController
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
     * VIEW TYPE 1: SUMMARY VIEW USAGE
     * Get department summary statistics using aggregated view
     */
    public function getDepartmentSummaryStats($filters = [])
    {
        try {
            $sql = "SELECT * FROM employee_summary_view";
            $conditions = [];
            $params = [];

            // Apply filters
            if (!empty($filters['min_employees'])) {
                $conditions[] = "total_employees >= ?";
                $params[] = $filters['min_employees'];
            }

            if (!empty($filters['min_performance'])) {
                $conditions[] = "avg_performance_rating >= ?";
                $params[] = $filters['min_performance'];
            }

            if (!empty($filters['department_name'])) {
                $conditions[] = "department_name LIKE ?";
                $params[] = '%' . $filters['department_name'] . '%';
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY avg_performance_rating DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $results,
                'summary' => [
                    'total_departments' => count($results),
                    'total_employees_across_all' => array_sum(array_column($results, 'total_employees')),
                    'avg_salary_across_all' => round(array_sum(array_column($results, 'avg_salary')) / count($results), 2),
                    'best_performing_dept' => $results[0]['department_name'] ?? 'N/A'
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching department summary: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * VIEW TYPE 2: FILTERED VIEW USAGE
     * Get active employees with pre-applied business logic
     */
    public function getActiveEmployeesWithFilters($filters = [])
    {
        try {
            $sql = "SELECT * FROM active_employees_detailed_view";
            $conditions = [];
            $params = [];

            // Apply additional filters on top of view's built-in filtering
            if (!empty($filters['tenure_category'])) {
                $conditions[] = "employee_tenure_category = ?";
                $params[] = $filters['tenure_category'];
            }

            if (!empty($filters['salary_category'])) {
                $conditions[] = "salary_category = ?";
                $params[] = $filters['salary_category'];
            }

            if (!empty($filters['department'])) {
                $conditions[] = "department_name = ?";
                $params[] = $filters['department'];
            }

            if (!empty($filters['has_recent_evaluation'])) {
                $conditions[] = "has_recent_evaluation = ?";
                $params[] = $filters['has_recent_evaluation'];
            }

            if (!empty($filters['min_performance'])) {
                $conditions[] = "latest_performance_rating >= ?";
                $params[] = $filters['min_performance'];
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            // Add pagination (using direct integer insertion for MySQL compatibility)
            $page = max(1, intval($filters['page'] ?? 1));
            $limit = max(1, min(100, intval($filters['limit'] ?? 20))); // Max 100 records per page
            $offset = ($page - 1) * $limit;

            $sql .= " LIMIT " . $limit . " OFFSET " . $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination
            $countSql = "SELECT COUNT(*) as total FROM active_employees_detailed_view";
            if (!empty($conditions)) {
                $countSql .= " WHERE " . implode(" AND ", $conditions);
                $countStmt = $this->db->prepare($countSql);
                $countStmt->execute($params);
            } else {
                $countStmt = $this->db->prepare($countSql);
                $countStmt->execute();
            }
            $totalCount = $countStmt->fetch()['total'];

            return [
                'success' => true,
                'data' => $results,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_records' => $totalCount,
                    'total_pages' => ceil($totalCount / $limit)
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching filtered employees: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * VIEW TYPE 3: MULTI-TABLE VIEW USAGE
     * Get comprehensive employee analytics from complex joined view
     */
    public function getComprehensiveEmployeeAnalytics($filters = [])
    {
        try {
            $sql = "SELECT * FROM comprehensive_employee_analytics_view";
            $conditions = [];
            $params = [];

            // Complex filtering options
            if (!empty($filters['department'])) {
                $conditions[] = "department_name = ?";
                $params[] = $filters['department'];
            }

            if (!empty($filters['performance_category'])) {
                $conditions[] = "performance_category = ?";
                $params[] = $filters['performance_category'];
            }

            if (!empty($filters['evaluation_recency'])) {
                $conditions[] = "evaluation_recency = ?";
                $params[] = $filters['evaluation_recency'];
            }

            if (!empty($filters['min_salary'])) {
                $conditions[] = "basic_salary >= ?";
                $params[] = $filters['min_salary'];
            }

            if (!empty($filters['max_dept_rank'])) {
                $conditions[] = "dept_performance_rank <= ?";
                $params[] = $filters['max_dept_rank'];
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            // Sophisticated ordering (with validation for security)
            $allowedOrderBy = [
                'company_performance_rank', 'dept_performance_rank', 
                'avg_performance_rating', 'basic_salary', 'employee_name'
            ];
            $orderBy = in_array($filters['order_by'] ?? '', $allowedOrderBy) 
                ? $filters['order_by'] 
                : 'company_performance_rank';
            
            $orderDir = strtoupper($filters['order_direction'] ?? 'ASC');
            $orderDir = in_array($orderDir, ['ASC', 'DESC']) ? $orderDir : 'ASC';
            
            $sql .= " ORDER BY {$orderBy} {$orderDir}";

            // Pagination (using direct integer insertion for MySQL compatibility)
            $page = max(1, intval($filters['page'] ?? 1));
            $limit = max(1, min(100, intval($filters['limit'] ?? 25))); // Max 100 records per page
            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT " . $limit . " OFFSET " . $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Generate analytics insights
            $insights = $this->generateAnalyticsInsights($results);

            return [
                'success' => true,
                'data' => $results,
                'insights' => $insights,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'has_results' => !empty($results)
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching comprehensive analytics: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Generate insights from comprehensive analytics data
     */
    private function generateAnalyticsInsights($data)
    {
        if (empty($data)) {
            return [];
        }

        $insights = [];

        // Performance insights
        $performanceCategories = array_count_values(array_column($data, 'performance_category'));
        $insights['performance_distribution'] = $performanceCategories;

        // Top performers (top 10% by company rank)
        $totalEmployees = count($data);
        $topPerformerCount = max(1, floor($totalEmployees * 0.1));
        $topPerformers = array_slice($data, 0, $topPerformerCount);
        $insights['top_performers'] = array_column($topPerformers, 'employee_name');

        // Salary vs Performance correlation
        $highPerformers = array_filter($data, function($emp) {
            return in_array($emp['performance_category'], ['Outstanding', 'Excellent']);
        });
        $avgSalaryHighPerformers = !empty($highPerformers) ? 
            array_sum(array_column($highPerformers, 'basic_salary')) / count($highPerformers) : 0;
        
        $otherEmployees = array_filter($data, function($emp) {
            return !in_array($emp['performance_category'], ['Outstanding', 'Excellent']);
        });
        $avgSalaryOthers = !empty($otherEmployees) ? 
            array_sum(array_column($otherEmployees, 'basic_salary')) / count($otherEmployees) : 0;

        $insights['salary_performance_correlation'] = [
            'high_performers_avg_salary' => round($avgSalaryHighPerformers, 2),
            'others_avg_salary' => round($avgSalaryOthers, 2),
            'salary_premium_for_performance' => round($avgSalaryHighPerformers - $avgSalaryOthers, 2)
        ];

        // Leave patterns
        $insights['leave_patterns'] = [
            'avg_leave_days' => round(array_sum(array_column($data, 'total_leave_days_taken')) / $totalEmployees, 1),
            'employees_with_pending_leaves' => count(array_filter($data, function($emp) { 
                return $emp['pending_leaves'] > 0; 
            }))
        ];

        return $insights;
    }

    /**
     * Get view-specific statistics and metadata
     */
    public function getViewStatistics()
    {
        try {
            $stats = [];

            // Summary view statistics
            $stmt = $this->db->prepare("SELECT COUNT(*) as dept_count, AVG(total_employees) as avg_employees_per_dept FROM employee_summary_view");
            $stmt->execute();
            $stats['summary_view'] = $stmt->fetch();

            // Filtered view statistics  
            $stmt = $this->db->prepare("SELECT COUNT(*) as active_employees, COUNT(DISTINCT department_name) as active_departments FROM active_employees_detailed_view");
            $stmt->execute();
            $stats['filtered_view'] = $stmt->fetch();

            // Multi-table view statistics
            $stmt = $this->db->prepare("SELECT COUNT(*) as total_records, AVG(avg_performance_rating) as overall_avg_performance FROM comprehensive_employee_analytics_view");
            $stmt->execute();
            $stats['multitable_view'] = $stmt->fetch();

            return [
                'success' => true,
                'statistics' => $stats
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching view statistics: ' . $e->getMessage()
            ];
        }
    }
}
?>

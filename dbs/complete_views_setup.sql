-- =============================================
-- COMPLETE DATABASE VIEWS SETUP SCRIPT
-- Execute this entire file in phpMyAdmin or MySQL
-- =============================================

-- First, drop existing views if they exist
DROP VIEW IF EXISTS comprehensive_employee_analytics_view;
DROP VIEW IF EXISTS active_employees_detailed_view;
DROP VIEW IF EXISTS employee_summary_view;

-- =============================================
-- DATABASE VIEWS IMPLEMENTATION
-- Employee Management System (Fixed for Actual Schema)
-- =============================================

-- =============================================
-- VIEW TYPE 1: SUMMARY VIEW
-- Purpose: Aggregated statistics and KPIs
-- =============================================

CREATE OR REPLACE VIEW employee_summary_view AS
SELECT 
    d.department_name,
    d.department_id,
    COUNT(e.employee_id) as total_employees,
    AVG(e.basic_salary) as avg_salary,
    MIN(e.basic_salary) as min_salary,
    MAX(e.basic_salary) as max_salary,
    COUNT(CASE WHEN e.employment_status = 1 THEN 1 END) as active_employees,
    COUNT(CASE WHEN e.employment_status = 0 THEN 1 END) as inactive_employees,
    COALESCE(AVG(p.rating), 0) as avg_performance_rating,
    COUNT(p.performance_id) as total_evaluations,
    COUNT(CASE WHEN p.rating >= 4 THEN 1 END) as high_performers,
    ROUND((COUNT(CASE WHEN p.rating >= 4 THEN 1 END) * 100.0 / NULLIF(COUNT(p.performance_id), 0)), 2) as high_performer_percentage,
    -- Leave statistics for department
    COALESCE(SUM(CASE WHEN lr.status = 'Approved' THEN DATEDIFF(lr.end_date, lr.start_date) + 1 ELSE 0 END), 0) as total_leave_days
FROM department d
LEFT JOIN employees e ON d.department_id = e.department_id AND e.employment_status = 1
LEFT JOIN performance p ON e.employee_id = p.employee_id
LEFT JOIN leave_records lr ON e.employee_id = lr.employee_id
GROUP BY d.department_id, d.department_name
ORDER BY avg_performance_rating DESC;

-- =============================================
-- VIEW TYPE 2: FILTERED VIEW
-- Purpose: Pre-filtered data with business logic
-- =============================================

CREATE OR REPLACE VIEW active_employees_detailed_view AS
SELECT 
    e.employee_id,
    e.first_name,
    e.last_name,
    CONCAT(e.first_name, ' ', COALESCE(e.middle_name, ''), ' ', e.last_name) as employee_name,
    e.email,
    e.contact_number,
    e.basic_salary,
    e.date_hired,
    DATEDIFF(CURDATE(), e.date_hired) as days_employed,
    CASE 
        WHEN DATEDIFF(CURDATE(), e.date_hired) < 365 THEN 'New'
        WHEN DATEDIFF(CURDATE(), e.date_hired) < 1825 THEN 'Experienced' 
        ELSE 'Senior'
    END as employee_tenure_category,
    d.department_name,
    jp.position_name,
    u.username,
    -- Performance indicators
    CASE 
        WHEN e.basic_salary >= 20000 THEN 'Senior Level'
        WHEN e.basic_salary >= 15000 THEN 'Mid Level'
        ELSE 'Entry Level'
    END as salary_category,
    -- Latest performance rating
    (SELECT p.rating FROM performance p WHERE p.employee_id = e.employee_id ORDER BY p.period_end DESC LIMIT 1) as latest_performance_rating,
    -- Has recent evaluation
    CASE 
        WHEN EXISTS (SELECT 1 FROM performance p WHERE p.employee_id = e.employee_id AND p.period_end >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)) 
        THEN 1 
        ELSE 0 
    END as has_recent_evaluation
FROM employees e
LEFT JOIN department d ON e.department_id = d.department_id
LEFT JOIN job_position jp ON e.position_id = jp.position_id
LEFT JOIN users u ON e.user_id = u.user_id
WHERE e.employment_status = 1  -- Only active employees
ORDER BY e.date_hired DESC;

-- =============================================
-- VIEW TYPE 3: MULTI-TABLE VIEW
-- Purpose: Complex joins across multiple tables
-- =============================================

CREATE OR REPLACE VIEW comprehensive_employee_analytics_view AS
SELECT 
    -- Employee Basic Info
    e.employee_id,
    CONCAT(e.first_name, ' ', COALESCE(e.middle_name, ''), ' ', e.last_name) as employee_name,
    e.email,
    e.basic_salary,
    e.date_hired,
    
    -- Department & Position Info
    d.department_name,
    jp.position_name,
    
    -- User Account Info
    u.username,
    
    -- Performance Analytics
    COALESCE(perf_stats.total_evaluations, 0) as total_evaluations,
    COALESCE(perf_stats.avg_rating, 0) as avg_performance_rating,
    COALESCE(perf_stats.latest_rating, 0) as latest_performance_rating,
    COALESCE(perf_stats.latest_evaluation_date, NULL) as latest_evaluation_date,
    CASE 
        WHEN perf_stats.avg_rating >= 4.5 THEN 'Outstanding'
        WHEN perf_stats.avg_rating >= 4.0 THEN 'Excellent'
        WHEN perf_stats.avg_rating >= 3.5 THEN 'Good'
        WHEN perf_stats.avg_rating >= 3.0 THEN 'Satisfactory'
        WHEN perf_stats.avg_rating > 0 THEN 'Needs Improvement'
        ELSE 'Not Evaluated'
    END as performance_category,
    
    -- Leave Statistics
    COALESCE(leave_stats.total_leaves, 0) as total_leave_requests,
    COALESCE(leave_stats.approved_leaves, 0) as approved_leaves,
    COALESCE(leave_stats.pending_leaves, 0) as pending_leaves,
    COALESCE(leave_stats.rejected_leaves, 0) as rejected_leaves,
    COALESCE(leave_stats.total_leave_days, 0) as total_leave_days_taken,
    
    -- Compensation Details
    COALESCE(allowance_stats.total_allowances, 0) as total_monthly_allowances,
    COALESCE(deduction_stats.total_deductions, 0) as total_monthly_deductions,
    (e.basic_salary + COALESCE(allowance_stats.total_allowances, 0) - COALESCE(deduction_stats.total_deductions, 0)) as net_monthly_salary,
    
    -- Department Rankings
    RANK() OVER (PARTITION BY e.department_id ORDER BY COALESCE(perf_stats.avg_rating, 0) DESC) as dept_performance_rank,
    RANK() OVER (PARTITION BY e.department_id ORDER BY e.basic_salary DESC) as dept_salary_rank,
    
    -- Overall Rankings
    RANK() OVER (ORDER BY COALESCE(perf_stats.avg_rating, 0) DESC) as company_performance_rank,
    RANK() OVER (ORDER BY e.basic_salary DESC) as company_salary_rank,
    
    -- Status Indicators
    CASE 
        WHEN e.employment_status = 1 THEN 'Active'
        ELSE 'Inactive'
    END as employment_status,
    
    CASE 
        WHEN EXISTS (SELECT 1 FROM performance p WHERE p.employee_id = e.employee_id AND p.period_end >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)) 
        THEN 'Recent'
        WHEN EXISTS (SELECT 1 FROM performance p WHERE p.employee_id = e.employee_id AND p.period_end >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH))
        THEN 'Moderate'
        ELSE 'Old'
    END as evaluation_recency

FROM employees e
LEFT JOIN department d ON e.department_id = d.department_id
LEFT JOIN job_position jp ON e.position_id = jp.position_id
LEFT JOIN users u ON e.user_id = u.user_id

-- Performance Statistics Subquery
LEFT JOIN (
    SELECT 
        employee_id,
        COUNT(*) as total_evaluations,
        AVG(rating) as avg_rating,
        MAX(rating) as latest_rating,
        MAX(period_end) as latest_evaluation_date
    FROM performance 
    GROUP BY employee_id
) perf_stats ON e.employee_id = perf_stats.employee_id

-- Leave Statistics Subquery
LEFT JOIN (
    SELECT 
        employee_id,
        COUNT(*) as total_leaves,
        COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved_leaves,
        COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_leaves,
        COUNT(CASE WHEN status = 'Rejected' THEN 1 END) as rejected_leaves,
        SUM(CASE WHEN status = 'Approved' THEN DATEDIFF(end_date, start_date) + 1 ELSE 0 END) as total_leave_days
    FROM leave_records 
    GROUP BY employee_id
) leave_stats ON e.employee_id = leave_stats.employee_id

-- Allowance Statistics Subquery
LEFT JOIN (
    SELECT 
        employee_id,
        SUM(allowance_amount) as total_allowances
    FROM employee_allowance
    GROUP BY employee_id
) allowance_stats ON e.employee_id = allowance_stats.employee_id

-- Deduction Statistics Subquery
LEFT JOIN (
    SELECT 
        employee_id,
        SUM(deduction_amount) as total_deductions
    FROM employee_deduction
    GROUP BY employee_id
) deduction_stats ON e.employee_id = deduction_stats.employee_id

WHERE e.employment_status = 1  -- Only active employees
ORDER BY company_performance_rank, company_salary_rank;

-- =============================================
-- TESTING THE VIEWS
-- =============================================

-- Verify views were created successfully
SELECT 'Views created successfully!' as status;
SHOW TABLES LIKE '%view';

-- Test View 1: Department Summary
SELECT '=== Testing View 1: Department Summary ===' as test_section;
SELECT * FROM employee_summary_view LIMIT 3;

-- Test View 2: Active Employees Detailed  
SELECT '=== Testing View 2: Active Employees Detailed ===' as test_section;
SELECT employee_name, department_name, salary_category, employee_tenure_category
FROM active_employees_detailed_view LIMIT 5;

-- Test View 3: Comprehensive Analytics
SELECT '=== Testing View 3: Comprehensive Analytics ===' as test_section;
SELECT employee_name, department_name, performance_category, 
       company_performance_rank, dept_performance_rank
FROM comprehensive_employee_analytics_view 
ORDER BY company_performance_rank LIMIT 5;

-- Final Status Check
SELECT '=== Final Status Check ===' as test_section;
SELECT 
    'employee_summary_view' as view_name,
    COUNT(*) as record_count
FROM employee_summary_view
UNION ALL
SELECT 
    'active_employees_detailed_view' as view_name,
    COUNT(*) as record_count
FROM active_employees_detailed_view
UNION ALL
SELECT 
    'comprehensive_employee_analytics_view' as view_name,
    COUNT(*) as record_count
FROM comprehensive_employee_analytics_view;

SELECT 'Database views setup complete! 🎉' as final_message;

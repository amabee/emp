-- Execute Database Views Creation Script (FIXED VERSION)
-- Run this file to create all three types of database views

-- First, drop existing views if they exist
DROP VIEW IF EXISTS comprehensive_employee_analytics_view;
DROP VIEW IF EXISTS active_employees_detailed_view;
DROP VIEW IF EXISTS employee_summary_view;

-- Execute the main database views script
SOURCE database_views.sql;

-- Run test script to verify views work
SOURCE test_views.sql;

-- Verify views were created successfully
SHOW TABLES LIKE '%view';

-- Test each view with a sample query
SELECT 'Testing employee_summary_view' as test_description;
SELECT * FROM employee_summary_view LIMIT 3;

SELECT 'Testing active_employees_detailed_view' as test_description;
SELECT employee_name, department_name, salary_category, employee_tenure_category 
FROM active_employees_detailed_view LIMIT 5;

SELECT 'Testing comprehensive_employee_analytics_view' as test_description;
SELECT employee_name, department_name, performance_category, company_performance_rank, dept_performance_rank
FROM comprehensive_employee_analytics_view 
WHERE company_performance_rank <= 10
ORDER BY company_performance_rank;

-- Show view structures
SELECT 'View structures created:' as info;
DESCRIBE employee_summary_view;
DESCRIBE active_employees_detailed_view;
DESCRIBE comprehensive_employee_analytics_view;

-- Performance check - verify indexes are being used
EXPLAIN SELECT * FROM comprehensive_employee_analytics_view WHERE department_name = 'IT';
EXPLAIN SELECT * FROM active_employees_detailed_view WHERE salary_category = 'Senior Level';
EXPLAIN SELECT * FROM employee_summary_view WHERE total_employees > 5;

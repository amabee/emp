-- Quick Setup: Branches Feature
-- Run this in your MySQL/phpMyAdmin to set up the branches feature

-- Step 1: Run the full migration
SOURCE dbs/migrations/add_branches_feature.sql;

-- OR if SOURCE doesn't work, copy and paste the migration file content

-- Step 2: Verify the setup
DESCRIBE branches;
DESCRIBE employees;

-- Step 3: Check sample data
SELECT * FROM branches;

-- Step 4: Test query - Get branches with employee count
SELECT 
    b.branch_id,
    b.branch_name,
    b.branch_code,
    COUNT(e.employee_id) as employee_count
FROM branches b
LEFT JOIN employees e ON b.branch_id = e.branch_id
GROUP BY b.branch_id, b.branch_name, b.branch_code;

-- Done! Now access the feature at:
-- http://yourdomain.com/pages/organization-settings.php

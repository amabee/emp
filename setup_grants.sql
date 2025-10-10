-- =============================================
-- DATABASE GRANTS SETUP SCRIPT
-- Employee Management System - Production Security
-- =============================================
-- 
-- INSTRUCTIONS:
-- 1. Run this as MySQL root user or admin with GRANT privileges
-- 2. Replace 'CHANGE_THIS_PASSWORD' with a secure password
-- 3. Update your shared/config.php with the new credentials
-- 4. Test the connection before deploying
--
-- USAGE:
-- mysql -u root -p < setup_grants.sql
-- =============================================

-- =============================================
-- CREATE APPLICATION USER
-- =============================================

-- Create the main application user (local access only for security)
CREATE USER IF NOT EXISTS 'emp_app'@'localhost' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';

-- Optional: Create user for remote access (adjust host as needed)
-- CREATE USER IF NOT EXISTS 'emp_app'@'192.168.1.%' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';

-- =============================================
-- CORE APPLICATION PERMISSIONS
-- =============================================

-- Employee management permissions
GRANT SELECT, INSERT, UPDATE ON emp.employees TO 'emp_app'@'localhost';

-- System logging permissions (INSERT only for security)
GRANT SELECT, INSERT ON emp.system_logs TO 'emp_app'@'localhost';

-- Reference table permissions (SELECT only)
GRANT SELECT ON emp.users TO 'emp_app'@'localhost';
GRANT SELECT ON emp.department TO 'emp_app'@'localhost';
GRANT SELECT ON emp.job_position TO 'emp_app'@'localhost';

-- =============================================
-- ADDITIONAL TABLE PERMISSIONS
-- Adjust these based on your application needs
-- =============================================

-- HR management tables
GRANT SELECT, INSERT, UPDATE, DELETE ON emp.leave_balances TO 'emp_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON emp.employee_allowances TO 'emp_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON emp.employee_deductions TO 'emp_app'@'localhost';

-- Time tracking
GRANT SELECT, INSERT, UPDATE ON emp.dtr TO 'emp_app'@'localhost';

-- Leave management
GRANT SELECT, INSERT, UPDATE ON emp.leaves TO 'emp_app'@'localhost';

-- Lookup tables (READ-only)
GRANT SELECT ON emp.allowance_types TO 'emp_app'@'localhost';
GRANT SELECT ON emp.deduction_types TO 'emp_app'@'localhost';

-- Payroll (if needed)
GRANT SELECT ON emp.payroll TO 'emp_app'@'localhost';

-- =============================================
-- SPECIAL PERMISSIONS FOR TRIGGERS
-- =============================================

-- Session variable permissions (required for trigger logging)
-- Choose ONE of the following based on your MySQL version:

-- For MySQL 8.0+ (Recommended)
GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_app'@'localhost';

-- For older MySQL versions (if SESSION_VARIABLES_ADMIN doesn't work)
-- GRANT SUPER ON *.* TO 'emp_app'@'localhost';

-- =============================================
-- ADMINISTRATIVE USER (Optional)
-- For database maintenance and trigger management
-- =============================================

-- Create admin user with full database access
CREATE USER IF NOT EXISTS 'emp_admin'@'localhost' IDENTIFIED BY 'ADMIN_PASSWORD_HERE';

-- Grant full privileges on emp database only
GRANT ALL PRIVILEGES ON emp.* TO 'emp_admin'@'localhost';

-- Grant trigger creation/modification privileges
GRANT CREATE, ALTER, DROP, TRIGGER ON emp.* TO 'emp_admin'@'localhost';

-- =============================================
-- READ-ONLY USER (Optional)
-- For reports, analytics, or backup purposes
-- =============================================

-- Create read-only user
CREATE USER IF NOT EXISTS 'emp_readonly'@'localhost' IDENTIFIED BY 'READONLY_PASSWORD_HERE';

-- Grant SELECT privileges on all tables
GRANT SELECT ON emp.* TO 'emp_readonly'@'localhost';

-- =============================================
-- APPLY CHANGES
-- =============================================

FLUSH PRIVILEGES;

-- =============================================
-- VERIFICATION QUERIES
-- Run these to verify the grants were applied
-- =============================================

-- Check application user grants
SHOW GRANTS FOR 'emp_app'@'localhost';

-- Check admin user grants (if created)
-- SHOW GRANTS FOR 'emp_admin'@'localhost';

-- Check readonly user grants (if created)  
-- SHOW GRANTS FOR 'emp_readonly'@'localhost';

-- List all emp-related users
SELECT User, Host, account_locked, password_expired 
FROM mysql.user 
WHERE User LIKE 'emp%';

-- =============================================
-- SECURITY NOTES
-- =============================================

/*
IMPORTANT SECURITY REMINDERS:

1. CHANGE DEFAULT PASSWORDS:
   - Replace 'CHANGE_THIS_PASSWORD' with a strong password
   - Use different passwords for each user
   - Store passwords securely (environment variables, password manager)

2. HOST RESTRICTIONS:
   - 'localhost' restricts access to local connections only
   - For remote access, specify exact IPs: 'emp_app'@'192.168.1.100'
   - Avoid wildcard '%' in production

3. PRINCIPLE OF LEAST PRIVILEGE:
   - Application user has minimal required permissions
   - No DELETE permissions on critical tables
   - No DROP or ALTER permissions for app user

4. REGULAR MAINTENANCE:
   - Review user accounts quarterly
   - Monitor failed login attempts
   - Rotate passwords regularly
   - Remove unused accounts

5. CONNECTION SECURITY:
   - Use SSL/TLS for database connections
   - Consider connection pooling
   - Implement connection timeouts

TESTING CONNECTION:
After running this script, test with:
mysql -u emp_app -p emp
*/

-- =============================================
-- TROUBLESHOOTING COMMON ISSUES
-- =============================================

/*
ISSUE: "Access denied for user 'emp_app'@'localhost'"
SOLUTION: 
1. Verify user exists: SELECT User FROM mysql.user WHERE User = 'emp_app';
2. Check host: SHOW GRANTS FOR 'emp_app'@'localhost';
3. Verify password: Try logging in manually

ISSUE: "Can't create table" or "Permission denied"
SOLUTION:
1. Check database permissions: SHOW GRANTS FOR 'emp_app'@'localhost';
2. Verify database exists: SHOW DATABASES LIKE 'emp';
3. Check table permissions: SELECT * FROM information_schema.TABLE_PRIVILEGES WHERE GRANTEE = "'emp_app'@'localhost'";

ISSUE: "Cannot set session variable @current_user_id"
SOLUTION:
1. Verify SESSION_VARIABLES_ADMIN grant
2. For older MySQL: May need SUPER privilege
3. Check MySQL version: SELECT VERSION();

ISSUE: Triggers not working
SOLUTION:
1. Check trigger exists: SHOW TRIGGERS FROM emp;
2. Verify definer permissions
3. Check trigger syntax and references
*/

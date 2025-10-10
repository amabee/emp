-- =============================================
-- DATABASE TRIGGERS IMPLEMENTATION
-- Employee Management System
-- =============================================

-- =============================================
-- TRIGGER 1: BEFORE INSERT VALIDATION
-- Purpose: Validate employee data before insertion
-- =============================================

DELIMITER $$

CREATE TRIGGER before_employee_insert_validation
BEFORE INSERT ON employees
FOR EACH ROW
BEGIN
    DECLARE dept_exists INT DEFAULT 0;
    DECLARE position_exists INT DEFAULT 0;
    DECLARE user_exists INT DEFAULT 0;
    DECLARE email_count INT DEFAULT 0;
    DECLARE contact_count INT DEFAULT 0;
    
    -- Validation 1: Check if required fields are not empty
    IF NEW.first_name IS NULL OR TRIM(NEW.first_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'First name cannot be empty';
    END IF;
    
    IF NEW.last_name IS NULL OR TRIM(NEW.last_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Last name cannot be empty';
    END IF;
    
    IF NEW.email IS NULL OR TRIM(NEW.email) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email cannot be empty';
    END IF;
    
    -- Validation 2: Check email format (basic validation)
    IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid email format';
    END IF;
    
    -- Validation 3: Check if email is unique
    SELECT COUNT(*) INTO email_count 
    FROM employees 
    WHERE email = NEW.email AND employment_status = 1;
    
    IF email_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists for an active employee';
    END IF;
    
    -- Validation 4: Check if contact number is unique (if provided)
    IF NEW.contact_number IS NOT NULL AND TRIM(NEW.contact_number) != '' THEN
        SELECT COUNT(*) INTO contact_count 
        FROM employees 
        WHERE contact_number = NEW.contact_number AND employment_status = 1;
        
        IF contact_count > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number already exists for an active employee';
        END IF;
    END IF;
    
    -- Validation 5: Check if department exists (if provided)
    IF NEW.department_id IS NOT NULL THEN
        SELECT COUNT(*) INTO dept_exists 
        FROM department 
        WHERE department_id = NEW.department_id;
        
        IF dept_exists = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Department does not exist';
        END IF;
    END IF;
    
    -- Validation 6: Check if position exists (if provided)
    IF NEW.position_id IS NOT NULL THEN
        SELECT COUNT(*) INTO position_exists 
        FROM job_position 
        WHERE position_id = NEW.position_id;
        
        IF position_exists = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Job position does not exist';
        END IF;
    END IF;
    
    -- Validation 7: Check if user exists
    SELECT COUNT(*) INTO user_exists 
    FROM users 
    WHERE user_id = NEW.user_id;
    
    IF user_exists = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User does not exist';
    END IF;
    
    -- Validation 8: Check salary is positive
    IF NEW.basic_salary <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Basic salary must be greater than zero';
    END IF;
    
    -- Validation 9: Check birthdate is reasonable (not in future, not too old)
    IF NEW.birthdate IS NOT NULL THEN
        IF NEW.birthdate > CURDATE() THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Birthdate cannot be in the future';
        END IF;
        
        IF NEW.birthdate < DATE_SUB(CURDATE(), INTERVAL 100 YEAR) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Birthdate cannot be more than 100 years ago';
        END IF;
    END IF;
    
    -- Validation 10: Check hire date is not in the future
    IF NEW.date_hired IS NOT NULL AND NEW.date_hired > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hire date cannot be in the future';
    END IF;
    
    -- Auto-set hire date if not provided
    IF NEW.date_hired IS NULL THEN
        SET NEW.date_hired = CURDATE();
    END IF;
    
    -- Auto-set employment status if not provided
    IF NEW.employment_status IS NULL THEN
        SET NEW.employment_status = 1;
    END IF;
    
END$$

-- =============================================
-- TRIGGER 2: BEFORE UPDATE VALIDATION  
-- Purpose: Validate employee data before updates
-- =============================================

CREATE TRIGGER before_employee_update_validation
BEFORE UPDATE ON employees
FOR EACH ROW
BEGIN
    DECLARE dept_exists INT DEFAULT 0;
    DECLARE position_exists INT DEFAULT 0;
    DECLARE email_count INT DEFAULT 0;
    DECLARE contact_count INT DEFAULT 0;
    
    -- Validation 1: Check if required fields are not empty
    IF NEW.first_name IS NULL OR TRIM(NEW.first_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'First name cannot be empty';
    END IF;
    
    IF NEW.last_name IS NULL OR TRIM(NEW.last_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Last name cannot be empty';
    END IF;
    
    IF NEW.email IS NULL OR TRIM(NEW.email) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email cannot be empty';
    END IF;
    
    -- Validation 2: Check email format
    IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid email format';
    END IF;
    
    -- Validation 3: Check if email is unique (excluding current record)
    SELECT COUNT(*) INTO email_count 
    FROM employees 
    WHERE email = NEW.email AND employee_id != NEW.employee_id AND employment_status = 1;
    
    IF email_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists for another active employee';
    END IF;
    
    -- Validation 4: Check if contact number is unique (excluding current record)
    IF NEW.contact_number IS NOT NULL AND TRIM(NEW.contact_number) != '' THEN
        SELECT COUNT(*) INTO contact_count 
        FROM employees 
        WHERE contact_number = NEW.contact_number AND employee_id != NEW.employee_id AND employment_status = 1;
        
        IF contact_count > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number already exists for another active employee';
        END IF;
    END IF;
    
    -- Validation 5: Check if department exists (if provided)
    IF NEW.department_id IS NOT NULL THEN
        SELECT COUNT(*) INTO dept_exists 
        FROM department 
        WHERE department_id = NEW.department_id;
        
        IF dept_exists = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Department does not exist';
        END IF;
    END IF;
    
    -- Validation 6: Check if position exists (if provided)
    IF NEW.position_id IS NOT NULL THEN
        SELECT COUNT(*) INTO position_exists 
        FROM job_position 
        WHERE position_id = NEW.position_id;
        
        IF position_exists = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Job position does not exist';
        END IF;
    END IF;
    
    -- Validation 7: Check salary is positive
    IF NEW.basic_salary <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Basic salary must be greater than zero';
    END IF;
    
    -- Validation 8: Check birthdate is reasonable
    IF NEW.birthdate IS NOT NULL THEN
        IF NEW.birthdate > CURDATE() THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Birthdate cannot be in the future';
        END IF;
        
        IF NEW.birthdate < DATE_SUB(CURDATE(), INTERVAL 100 YEAR) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Birthdate cannot be more than 100 years ago';
        END IF;
    END IF;
    
    -- Validation 9: Check hire date is not in the future
    IF NEW.date_hired IS NOT NULL AND NEW.date_hired > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hire date cannot be in the future';
    END IF;
    
END$$

-- =============================================
-- TRIGGER 3: AFTER UPDATE LOGGING
-- Purpose: Log all employee updates for audit trail
-- =============================================

CREATE TRIGGER after_employee_update_logging
AFTER UPDATE ON employees
FOR EACH ROW
BEGIN
    DECLARE change_description TEXT DEFAULT '';
    DECLARE user_session_id INT DEFAULT NULL;
    
    -- Try to get current user from session (this might not work in all contexts)
    -- You may need to modify this based on your session handling
    SET user_session_id = @current_user_id;
    
    -- Build change description
    SET change_description = 'Employee Updated: ';
    
    IF OLD.first_name != NEW.first_name THEN
        SET change_description = CONCAT(change_description, 'First Name: "', OLD.first_name, '" → "', NEW.first_name, '"; ');
    END IF;
    
    IF OLD.middle_name != NEW.middle_name OR (OLD.middle_name IS NULL AND NEW.middle_name IS NOT NULL) OR (OLD.middle_name IS NOT NULL AND NEW.middle_name IS NULL) THEN
        SET change_description = CONCAT(change_description, 'Middle Name: "', COALESCE(OLD.middle_name, 'NULL'), '" → "', COALESCE(NEW.middle_name, 'NULL'), '"; ');
    END IF;
    
    IF OLD.last_name != NEW.last_name THEN
        SET change_description = CONCAT(change_description, 'Last Name: "', OLD.last_name, '" → "', NEW.last_name, '"; ');
    END IF;
    
    IF OLD.email != NEW.email THEN
        SET change_description = CONCAT(change_description, 'Email: "', OLD.email, '" → "', NEW.email, '"; ');
    END IF;
    
    IF OLD.contact_number != NEW.contact_number OR (OLD.contact_number IS NULL AND NEW.contact_number IS NOT NULL) OR (OLD.contact_number IS NOT NULL AND NEW.contact_number IS NULL) THEN
        SET change_description = CONCAT(change_description, 'Contact: "', COALESCE(OLD.contact_number, 'NULL'), '" → "', COALESCE(NEW.contact_number, 'NULL'), '"; ');
    END IF;
    
    IF OLD.department_id != NEW.department_id OR (OLD.department_id IS NULL AND NEW.department_id IS NOT NULL) OR (OLD.department_id IS NOT NULL AND NEW.department_id IS NULL) THEN
        SET change_description = CONCAT(change_description, 'Department ID: "', COALESCE(OLD.department_id, 'NULL'), '" → "', COALESCE(NEW.department_id, 'NULL'), '"; ');
    END IF;
    
    IF OLD.position_id != NEW.position_id OR (OLD.position_id IS NULL AND NEW.position_id IS NOT NULL) OR (OLD.position_id IS NOT NULL AND NEW.position_id IS NULL) THEN
        SET change_description = CONCAT(change_description, 'Position ID: "', COALESCE(OLD.position_id, 'NULL'), '" → "', COALESCE(NEW.position_id, 'NULL'), '"; ');
    END IF;
    
    IF OLD.basic_salary != NEW.basic_salary THEN
        SET change_description = CONCAT(change_description, 'Salary: "', OLD.basic_salary, '" → "', NEW.basic_salary, '"; ');
    END IF;
    
    IF OLD.employment_status != NEW.employment_status THEN
        SET change_description = CONCAT(change_description, 'Employment Status: "', OLD.employment_status, '" → "', NEW.employment_status, '"; ');
    END IF;
    
    IF OLD.gender != NEW.gender THEN
        SET change_description = CONCAT(change_description, 'Gender: "', OLD.gender, '" → "', NEW.gender, '"; ');
    END IF;
    
    IF OLD.birthdate != NEW.birthdate OR (OLD.birthdate IS NULL AND NEW.birthdate IS NOT NULL) OR (OLD.birthdate IS NOT NULL AND NEW.birthdate IS NULL) THEN
        SET change_description = CONCAT(change_description, 'Birthdate: "', COALESCE(OLD.birthdate, 'NULL'), '" → "', COALESCE(NEW.birthdate, 'NULL'), '"; ');
    END IF;
    
    IF OLD.date_hired != NEW.date_hired OR (OLD.date_hired IS NULL AND NEW.date_hired IS NOT NULL) OR (OLD.date_hired IS NOT NULL AND NEW.date_hired IS NULL) THEN
        SET change_description = CONCAT(change_description, 'Hire Date: "', COALESCE(OLD.date_hired, 'NULL'), '" → "', COALESCE(NEW.date_hired, 'NULL'), '"; ');
    END IF;
    
    -- Only log if there were actual changes
    IF LENGTH(change_description) > 18 THEN  -- More than just "Employee Updated: "
        INSERT INTO system_logs (
            user_id,
            action_performed,
            full_description,
            date_performed,
            ip_address
        ) VALUES (
            COALESCE(user_session_id, 1), -- Default to user ID 1 if no session user
            'UPDATE',
            CONCAT('Employee ID ', NEW.employee_id, ' (', NEW.first_name, ' ', NEW.last_name, ') - ', change_description),
            NOW(),
            '127.0.0.1' -- You might want to capture real IP from application layer
        );
    END IF;
    
END$$

-- =============================================
-- TRIGGER 4: AFTER INSERT LOGGING
-- Purpose: Log new employee creation
-- =============================================

CREATE TRIGGER after_employee_insert_logging
AFTER INSERT ON employees
FOR EACH ROW
BEGIN
    DECLARE user_session_id INT DEFAULT NULL;
    
    -- Try to get current user from session
    SET user_session_id = @current_user_id;
    
    -- Log the employee creation
    INSERT INTO system_logs (
        user_id,
        action_performed,
        full_description,
        date_performed,
        ip_address
    ) VALUES (
        COALESCE(user_session_id, 1), -- Default to user ID 1 if no session user
        'CREATE',
        CONCAT('New Employee Created: ID ', NEW.employee_id, ' - ', NEW.first_name, ' ', NEW.last_name, 
               ' (Email: ', NEW.email, ', Salary: ', NEW.basic_salary, 
               ', Department ID: ', COALESCE(NEW.department_id, 'NULL'), 
               ', Position ID: ', COALESCE(NEW.position_id, 'NULL'), ')'),
        NOW(),
        '127.0.0.1'
    );
    
    -- Auto-create leave balances for the new employee
    INSERT INTO leave_balances (
        employee_id,
        year,
        vacation_total,
        sick_total,
        personal_total,
        emergency_total,
        maternity_total,
        paternity_total,
        vacation_used,
        sick_used,
        personal_used,
        emergency_used,
        maternity_used,
        paternity_used
    ) VALUES (
        NEW.employee_id,
        YEAR(CURDATE()),
        15, -- Default vacation days
        10, -- Default sick days
        5,  -- Default personal days
        30, -- Default emergency days
        90, -- Default maternity days
        7,  -- Default paternity days
        0, 0, 0, 0, 0, 0 -- All used counts start at 0
    );
    
END$$

DELIMITER ;

-- =============================================
-- USAGE INSTRUCTIONS
-- =============================================

/*
To set the current user ID for logging purposes in your PHP application:

// In your PHP code, before performing employee operations:
$stmt = $pdo->prepare("SET @current_user_id = ?");
$stmt->execute([$_SESSION['user_id']]);

// Then perform your employee insert/update operations
// The triggers will automatically use this user ID for logging

Example PHP usage:
```php
// Set session user for trigger logging
$stmt = $pdo->prepare("SET @current_user_id = ?");
$stmt->execute([$_SESSION['user_id']]);

// Now insert/update employee - triggers will fire automatically
$stmt = $pdo->prepare("INSERT INTO employees (user_id, first_name, last_name, email, basic_salary) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $firstName, $lastName, $email, $salary]);
```
*/  

-- =============================================
-- TRIGGER TESTING QUERIES
-- =============================================

/*
-- Test validation triggers (these should fail):

-- Test 1: Empty first name (should fail)
INSERT INTO employees (user_id, first_name, last_name, email, basic_salary) 
VALUES (1, '', 'Test', 'test@example.com', 15000);

-- Test 2: Invalid email (should fail)
INSERT INTO employees (user_id, first_name, last_name, email, basic_salary) 
VALUES (1, 'John', 'Doe', 'invalid-email', 15000);

-- Test 3: Negative salary (should fail)
INSERT INTO employees (user_id, first_name, last_name, email, basic_salary) 
VALUES (1, 'John', 'Doe', 'john@example.com', -1000);

-- Test 4: Future birthdate (should fail)
INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, birthdate) 
VALUES (1, 'John', 'Doe', 'john2@example.com', 15000, '2030-01-01');

-- Test 5: Valid insertion (should succeed)
SET @current_user_id = 1;
INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, department_id, position_id) 
VALUES (1, 'John', 'Doe', 'john.doe@example.com', 15000, 1, 1);

-- Check system logs to see the logged insertion
SELECT * FROM system_logs ORDER BY date_performed DESC LIMIT 5;

-- Test update logging
UPDATE employees SET basic_salary = 16000, first_name = 'Johnny' WHERE employee_id = (SELECT MAX(employee_id) FROM employees);

-- Check system logs again
SELECT * FROM system_logs ORDER BY date_performed DESC LIMIT 5;
*/

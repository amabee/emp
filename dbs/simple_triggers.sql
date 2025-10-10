-- =============================================
-- SIMPLE DATABASE TRIGGERS FOR EMPLOYEE VALIDATION AND LOGGING
-- Employee Management System  
-- =============================================

-- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS before_employee_insert_validation;
DROP TRIGGER IF EXISTS before_employee_update_validation;
DROP TRIGGER IF EXISTS after_employee_update_logging;
DROP TRIGGER IF EXISTS after_employee_insert_logging;

-- =============================================
-- TRIGGER 1: BEFORE INSERT VALIDATION
-- =============================================
CREATE TRIGGER before_employee_insert_validation
BEFORE INSERT ON employees
FOR EACH ROW
BEGIN
    -- Validation: Check required fields
    IF NEW.first_name IS NULL OR TRIM(NEW.first_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'First name cannot be empty';
    END IF;
    
    IF NEW.last_name IS NULL OR TRIM(NEW.last_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Last name cannot be empty';
    END IF;
    
    IF NEW.email IS NULL OR TRIM(NEW.email) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email cannot be empty';
    END IF;
    
    -- Check email format
    IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid email format';
    END IF;
    
    -- Check email uniqueness
    IF (SELECT COUNT(*) FROM employees WHERE email = NEW.email AND employment_status = 1) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists for an active employee';
    END IF;
    
    -- Check contact number uniqueness (if provided)
    IF NEW.contact_number IS NOT NULL AND TRIM(NEW.contact_number) != '' THEN
        IF (SELECT COUNT(*) FROM employees WHERE contact_number = NEW.contact_number AND employment_status = 1) > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number already exists for an active employee';
        END IF;
    END IF;
    
    -- Check department exists (if provided)
    IF NEW.department_id IS NOT NULL THEN
        IF (SELECT COUNT(*) FROM department WHERE department_id = NEW.department_id) = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Department does not exist';
        END IF;
    END IF;
    
    -- Check position exists (if provided)
    IF NEW.position_id IS NOT NULL THEN
        IF (SELECT COUNT(*) FROM job_position WHERE position_id = NEW.position_id) = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Job position does not exist';
        END IF;
    END IF;
    
    -- Check user exists
    IF (SELECT COUNT(*) FROM users WHERE user_id = NEW.user_id) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User does not exist';
    END IF;
    
    -- Check positive salary
    IF NEW.basic_salary <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Basic salary must be greater than zero';
    END IF;
    
    -- Check reasonable birthdate
    IF NEW.birthdate IS NOT NULL THEN
        IF NEW.birthdate > CURDATE() THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Birthdate cannot be in the future';
        END IF;
        
        IF NEW.birthdate < DATE_SUB(CURDATE(), INTERVAL 100 YEAR) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Birthdate cannot be more than 100 years ago';
        END IF;
    END IF;
    
    -- Check hire date not in future
    IF NEW.date_hired IS NOT NULL AND NEW.date_hired > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hire date cannot be in the future';
    END IF;
    
    -- Set defaults
    IF NEW.date_hired IS NULL THEN
        SET NEW.date_hired = CURDATE();
    END IF;
    
    IF NEW.employment_status IS NULL THEN
        SET NEW.employment_status = 1;
    END IF;
END;

-- =============================================
-- TRIGGER 2: BEFORE UPDATE VALIDATION
-- =============================================
CREATE TRIGGER before_employee_update_validation
BEFORE UPDATE ON employees
FOR EACH ROW
BEGIN
    -- Validation: Check required fields
    IF NEW.first_name IS NULL OR TRIM(NEW.first_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'First name cannot be empty';
    END IF;
    
    IF NEW.last_name IS NULL OR TRIM(NEW.last_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Last name cannot be empty';
    END IF;
    
    IF NEW.email IS NULL OR TRIM(NEW.email) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email cannot be empty';
    END IF;
    
    -- Check email format
    IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid email format';
    END IF;
    
    -- Check email uniqueness (excluding current record)
    IF (SELECT COUNT(*) FROM employees WHERE email = NEW.email AND employee_id != NEW.employee_id AND employment_status = 1) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists for another active employee';
    END IF;
    
    -- Check contact number uniqueness (excluding current record)
    IF NEW.contact_number IS NOT NULL AND TRIM(NEW.contact_number) != '' THEN
        IF (SELECT COUNT(*) FROM employees WHERE contact_number = NEW.contact_number AND employee_id != NEW.employee_id AND employment_status = 1) > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number already exists for another active employee';
        END IF;
    END IF;
    
    -- Check department exists (if provided)
    IF NEW.department_id IS NOT NULL THEN
        IF (SELECT COUNT(*) FROM department WHERE department_id = NEW.department_id) = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Department does not exist';
        END IF;
    END IF;
    
    -- Check position exists (if provided)
    IF NEW.position_id IS NOT NULL THEN
        IF (SELECT COUNT(*) FROM job_position WHERE position_id = NEW.position_id) = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Job position does not exist';
        END IF;
    END IF;
    
    -- Check positive salary
    IF NEW.basic_salary <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Basic salary must be greater than zero';
    END IF;
    
    -- Check reasonable birthdate
    IF NEW.birthdate IS NOT NULL THEN
        IF NEW.birthdate > CURDATE() THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Birthdate cannot be in the future';
        END IF;
        
        IF NEW.birthdate < DATE_SUB(CURDATE(), INTERVAL 100 YEAR) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Birthdate cannot be more than 100 years ago';
        END IF;
    END IF;
    
    -- Check hire date not in future  
    IF NEW.date_hired IS NOT NULL AND NEW.date_hired > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hire date cannot be in the future';
    END IF;
END;

-- =============================================
-- TRIGGER 3: AFTER INSERT LOGGING
-- =============================================
CREATE TRIGGER after_employee_insert_logging
AFTER INSERT ON employees
FOR EACH ROW
BEGIN
    INSERT INTO system_logs (
        user_id,
        action_performed,
        full_description,
        date_performed,
        ip_address
    ) VALUES (
        COALESCE(@current_user_id, 1),
        'CREATE',
        CONCAT('New Employee Created: ID ', NEW.employee_id, ' - ', NEW.first_name, ' ', NEW.last_name, 
               ' (Email: ', NEW.email, ', Salary: ', NEW.basic_salary, 
               ', Department ID: ', COALESCE(NEW.department_id, 'NULL'), 
               ', Position ID: ', COALESCE(NEW.position_id, 'NULL'), ')'),
        NOW(),
        '127.0.0.1'
    );
END;

-- =============================================
-- TRIGGER 4: AFTER UPDATE LOGGING  
-- =============================================
CREATE TRIGGER after_employee_update_logging
AFTER UPDATE ON employees
FOR EACH ROW
BEGIN
    DECLARE change_description TEXT DEFAULT 'Employee Updated: ';
    
    -- Build change description for modified fields
    IF OLD.first_name != NEW.first_name THEN
        SET change_description = CONCAT(change_description, 'First Name: "', OLD.first_name, '" → "', NEW.first_name, '"; ');
    END IF;
    
    IF OLD.last_name != NEW.last_name THEN
        SET change_description = CONCAT(change_description, 'Last Name: "', OLD.last_name, '" → "', NEW.last_name, '"; ');
    END IF;
    
    IF OLD.email != NEW.email THEN
        SET change_description = CONCAT(change_description, 'Email: "', OLD.email, '" → "', NEW.email, '"; ');
    END IF;
    
    IF OLD.basic_salary != NEW.basic_salary THEN
        SET change_description = CONCAT(change_description, 'Salary: "', OLD.basic_salary, '" → "', NEW.basic_salary, '"; ');
    END IF;
    
    IF OLD.employment_status != NEW.employment_status THEN
        SET change_description = CONCAT(change_description, 'Employment Status: "', OLD.employment_status, '" → "', NEW.employment_status, '"; ');
    END IF;
    
    -- Only log if there were actual changes
    IF LENGTH(change_description) > 18 THEN
        INSERT INTO system_logs (
            user_id,
            action_performed,
            full_description,
            date_performed,
            ip_address
        ) VALUES (
            COALESCE(@current_user_id, 1),
            'UPDATE',
            CONCAT('Employee ID ', NEW.employee_id, ' (', NEW.first_name, ' ', NEW.last_name, ') - ', change_description),
            NOW(),
            '127.0.0.1'
        );
    END IF;
END;

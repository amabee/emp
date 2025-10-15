-- Migration: Add Applicants Feature
-- Description: Creates applicants table and related structures for job application management
-- Date: 2025-10-16

-- Create applicants table
CREATE TABLE IF NOT EXISTS applicants (
  applicant_id INT PRIMARY KEY AUTO_INCREMENT,
  first_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100),
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  alternative_phone VARCHAR(20),
  address TEXT,
  city VARCHAR(100),
  state VARCHAR(100),
  zip_code VARCHAR(20),
  date_of_birth DATE,
  gender ENUM('Male', 'Female', 'Other'),
  
  -- Application details
  position_applied INT,
  branch_applied INT,
  department_applied INT,
  resume_path VARCHAR(500),
  cover_letter TEXT,
  application_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  -- Application status tracking
  status ENUM('pending', 'reviewing', 'interview_scheduled', 'interviewed', 'accepted', 'hired', 'rejected', 'withdrawn') DEFAULT 'pending',
  
  -- Interview details
  interview_date DATETIME,
  interview_notes TEXT,
  interviewer_id INT,
  
  -- HR notes and evaluation
  hr_notes TEXT,
  skills TEXT,
  experience_years DECIMAL(4,2),
  expected_salary DECIMAL(12,2),
  available_start_date DATE,
  
  -- Reference information
  reference_name VARCHAR(200),
  reference_contact VARCHAR(200),
  reference_relationship VARCHAR(100),
  
  -- System fields
  is_active BOOLEAN DEFAULT TRUE,
  email_verified BOOLEAN DEFAULT FALSE,
  verification_token VARCHAR(100),
  last_login DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Foreign keys
  CONSTRAINT fk_applicant_position FOREIGN KEY (position_applied) REFERENCES job_position(position_id) ON DELETE SET NULL,
  CONSTRAINT fk_applicant_branch FOREIGN KEY (branch_applied) REFERENCES branches(branch_id) ON DELETE SET NULL,
  CONSTRAINT fk_applicant_department FOREIGN KEY (department_applied) REFERENCES department(dept_id) ON DELETE SET NULL,
  CONSTRAINT fk_applicant_interviewer FOREIGN KEY (interviewer_id) REFERENCES employees(emp_id) ON DELETE SET NULL,
  
  -- Indexes
  INDEX idx_email (email),
  INDEX idx_status (status),
  INDEX idx_application_date (application_date),
  INDEX idx_position_applied (position_applied),
  INDEX idx_branch_applied (branch_applied)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create applicant_documents table for multiple file uploads
CREATE TABLE IF NOT EXISTS applicant_documents (
  document_id INT PRIMARY KEY AUTO_INCREMENT,
  applicant_id INT NOT NULL,
  document_type ENUM('resume', 'cover_letter', 'certificate', 'id_proof', 'portfolio', 'other') NOT NULL,
  document_name VARCHAR(255) NOT NULL,
  document_path VARCHAR(500) NOT NULL,
  file_size INT,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_document_applicant FOREIGN KEY (applicant_id) REFERENCES applicants(applicant_id) ON DELETE CASCADE,
  INDEX idx_applicant_documents (applicant_id, document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create applicant_activity_log table for tracking status changes
CREATE TABLE IF NOT EXISTS applicant_activity_log (
  log_id INT PRIMARY KEY AUTO_INCREMENT,
  applicant_id INT NOT NULL,
  action VARCHAR(100) NOT NULL,
  old_status VARCHAR(50),
  new_status VARCHAR(50),
  performed_by INT,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_log_applicant FOREIGN KEY (applicant_id) REFERENCES applicants(applicant_id) ON DELETE CASCADE,
  CONSTRAINT fk_log_performer FOREIGN KEY (performed_by) REFERENCES employees(emp_id) ON DELETE SET NULL,
  INDEX idx_applicant_log (applicant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add applicant_id column to employees table to link hired applicants
ALTER TABLE employees ADD COLUMN IF NOT EXISTS applicant_id INT NULL AFTER emp_id;
ALTER TABLE employees ADD CONSTRAINT fk_employee_applicant 
  FOREIGN KEY (applicant_id) REFERENCES applicants(applicant_id) ON DELETE SET NULL;
ALTER TABLE employees ADD INDEX idx_applicant_id (applicant_id);

-- Insert sample applicant data for testing
INSERT INTO applicants (
  first_name, last_name, email, password_hash, phone, address, 
  position_applied, status, application_date, skills, experience_years
) VALUES
  ('John', 'Smith', 'john.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
   '09171234567', '123 Main St, Manila', 1, 'pending', NOW(), 'PHP, MySQL, JavaScript', 3.5),
  ('Maria', 'Garcia', 'maria.garcia@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
   '09187654321', '456 Oak Ave, Quezon City', 2, 'reviewing', NOW(), 'Accounting, Excel, SAP', 5.0),
  ('James', 'Lee', 'james.lee@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
   '09195551234', '789 Pine Rd, Makati', 3, 'interview_scheduled', NOW(), 'HR Management, Recruitment', 4.0);

-- Create view for applicant dashboard statistics
CREATE OR REPLACE VIEW v_applicant_statistics AS
SELECT 
  COUNT(*) as total_applicants,
  SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
  SUM(CASE WHEN status = 'reviewing' THEN 1 ELSE 0 END) as reviewing_count,
  SUM(CASE WHEN status = 'interview_scheduled' THEN 1 ELSE 0 END) as interview_scheduled_count,
  SUM(CASE WHEN status = 'interviewed' THEN 1 ELSE 0 END) as interviewed_count,
  SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
  SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired_count,
  SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
  SUM(CASE WHEN DATE(application_date) = CURDATE() THEN 1 ELSE 0 END) as today_applications,
  SUM(CASE WHEN DATE(application_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_applications,
  SUM(CASE WHEN DATE(application_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as month_applications
FROM applicants
WHERE is_active = TRUE;

-- Grant permissions (adjust username as needed)
GRANT SELECT, INSERT, UPDATE, DELETE ON applicants TO 'root'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON applicant_documents TO 'root'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON applicant_activity_log TO 'root'@'localhost';
GRANT SELECT ON v_applicant_statistics TO 'root'@'localhost';

-- Success message
SELECT 'Applicants feature tables created successfully!' as message;

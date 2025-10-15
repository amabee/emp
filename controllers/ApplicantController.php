<?php
class ApplicantController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Register a new applicant
     */
    public function register($data) {
        try {
            // Validate required fields
            $required = ['first_name', 'last_name', 'email', 'password', 'phone'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            // Check if email already exists
            $checkQuery = "SELECT applicant_id FROM applicants WHERE email = ?";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->bind_param("s", $data['email']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                return ['success' => false, 'message' => 'Email already registered'];
            }
            
            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            
            // Insert applicant
            $query = "INSERT INTO applicants (
                first_name, middle_name, last_name, email, password_hash, phone, 
                alternative_phone, address, city, state, zip_code, date_of_birth, gender,
                position_applied, branch_applied, department_applied, 
                skills, experience_years, expected_salary, available_start_date,
                verification_token
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "sssssssssssssiiisdss",
                $data['first_name'],
                $data['middle_name'],
                $data['last_name'],
                $data['email'],
                $passwordHash,
                $data['phone'],
                $data['alternative_phone'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['zip_code'],
                $data['date_of_birth'],
                $data['gender'],
                $data['position_applied'],
                $data['branch_applied'],
                $data['department_applied'],
                $data['skills'],
                $data['experience_years'],
                $data['expected_salary'],
                $data['available_start_date'],
                $verificationToken
            );
            
            if ($stmt->execute()) {
                $applicantId = $stmt->insert_id;
                
                // Log activity
                $this->logActivity($applicantId, 'registration', null, 'pending', null, 'Applicant registered');
                
                return [
                    'success' => true, 
                    'message' => 'Registration successful',
                    'applicant_id' => $applicantId,
                    'verification_token' => $verificationToken
                ];
            }
            
            return ['success' => false, 'message' => 'Registration failed'];
            
        } catch (Exception $e) {
            error_log("ApplicantController::register error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during registration'];
        }
    }
    
    /**
     * Applicant login
     */
    public function login($email, $password) {
        try {
            $query = "SELECT 
                applicant_id, first_name, middle_name, last_name, email, password_hash, 
                phone, address, position_applied, status, is_active, email_verified
            FROM applicants 
            WHERE email = ? AND is_active = TRUE";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            $applicant = $result->fetch_assoc();
            
            // Verify password
            if (!password_verify($password, $applicant['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Update last login
            $updateQuery = "UPDATE applicants SET last_login = NOW() WHERE applicant_id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $applicant['applicant_id']);
            $updateStmt->execute();
            
            // Remove password hash from response
            unset($applicant['password_hash']);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'applicant' => $applicant
            ];
            
        } catch (Exception $e) {
            error_log("ApplicantController::login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login'];
        }
    }
    
    /**
     * Get applicant profile
     */
    public function getProfile($applicantId) {
        try {
            $query = "SELECT 
                a.*,
                p.position_name,
                b.branch_name,
                d.dept_name,
                CONCAT(e.fname, ' ', e.lname) as interviewer_name
            FROM applicants a
            LEFT JOIN job_position p ON a.position_applied = p.position_id
            LEFT JOIN branches b ON a.branch_applied = b.branch_id
            LEFT JOIN department d ON a.department_applied = d.department_id
            LEFT JOIN employees e ON a.interviewer_id = e.employee_id
            WHERE a.applicant_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $applicantId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => 'Applicant not found'];
            }
            
            $applicant = $result->fetch_assoc();
            unset($applicant['password_hash']);
            
            return ['success' => true, 'applicant' => $applicant];
            
        } catch (Exception $e) {
            error_log("ApplicantController::getProfile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error fetching profile'];
        }
    }
    
    /**
     * Update applicant profile
     */
    public function updateProfile($applicantId, $data) {
        try {
            $query = "UPDATE applicants SET 
                first_name = ?, middle_name = ?, last_name = ?, phone = ?,
                alternative_phone = ?, address = ?, city = ?, state = ?, zip_code = ?,
                date_of_birth = ?, gender = ?, skills = ?, experience_years = ?,
                expected_salary = ?, available_start_date = ?,
                reference_name = ?, reference_contact = ?, reference_relationship = ?
            WHERE applicant_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "ssssssssssssdssssi",
                $data['first_name'],
                $data['middle_name'],
                $data['last_name'],
                $data['phone'],
                $data['alternative_phone'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['zip_code'],
                $data['date_of_birth'],
                $data['gender'],
                $data['skills'],
                $data['experience_years'],
                $data['expected_salary'],
                $data['available_start_date'],
                $data['reference_name'],
                $data['reference_contact'],
                $data['reference_relationship'],
                $applicantId
            );
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Profile updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to update profile'];
            
        } catch (Exception $e) {
            error_log("ApplicantController::updateProfile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating profile'];
        }
    }
    
    /**
     * Get all applicants (HR view)
     */
    public function getAllApplicants($filters = []) {
        try {
            $query = "SELECT 
                a.applicant_id, a.first_name, a.middle_name, a.last_name, a.email, a.phone,
                a.status, a.application_date, a.interview_date, a.experience_years,
                p.position_name, b.branch_name, d.dept_name,
                CONCAT(e.fname, ' ', e.lname) as interviewer_name
            FROM applicants a
            LEFT JOIN job_position p ON a.position_applied = p.position_id
            LEFT JOIN branches b ON a.branch_applied = b.branch_id
            LEFT JOIN department d ON a.department_applied = d.department_id
            LEFT JOIN employees e ON a.interviewer_id = e.employee_id
            WHERE a.is_active = TRUE";
            
            $params = [];
            $types = "";
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query .= " AND a.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            if (!empty($filters['position_id'])) {
                $query .= " AND a.position_applied = ?";
                $params[] = $filters['position_id'];
                $types .= "i";
            }
            
            if (!empty($filters['branch_id'])) {
                $query .= " AND a.branch_applied = ?";
                $params[] = $filters['branch_id'];
                $types .= "i";
            }
            
            $query .= " ORDER BY a.application_date DESC";
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $applicants = [];
            while ($row = $result->fetch_assoc()) {
                $applicants[] = $row;
            }
            
            return ['success' => true, 'applicants' => $applicants];
            
        } catch (Exception $e) {
            error_log("ApplicantController::getAllApplicants error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error fetching applicants'];
        }
    }
    
    /**
     * Update applicant status (HR function)
     */
    public function updateStatus($applicantId, $newStatus, $performedBy, $notes = null) {
        try {
            // Get current status
            $currentQuery = "SELECT status FROM applicants WHERE applicant_id = ?";
            $stmt = $this->conn->prepare($currentQuery);
            $stmt->bind_param("i", $applicantId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => 'Applicant not found'];
            }
            
            $currentStatus = $result->fetch_assoc()['status'];
            
            // Update status
            $updateQuery = "UPDATE applicants SET status = ? WHERE applicant_id = ?";
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->bind_param("si", $newStatus, $applicantId);
            
            if ($stmt->execute()) {
                // Log the status change
                $this->logActivity($applicantId, 'status_change', $currentStatus, $newStatus, $performedBy, $notes);
                
                return ['success' => true, 'message' => 'Status updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to update status'];
            
        } catch (Exception $e) {
            error_log("ApplicantController::updateStatus error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating status'];
        }
    }
    
    /**
     * Get applicant activity log
     */
    public function getActivityLog($applicantId) {
        try {
            $query = "SELECT 
                l.*,
                CONCAT(e.fname, ' ', e.lname) as performer_name
            FROM applicant_activity_log l
            LEFT JOIN employees e ON l.performed_by = e.employee_id
            WHERE l.applicant_id = ?
            ORDER BY l.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $applicantId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            
            return ['success' => true, 'logs' => $logs];
            
        } catch (Exception $e) {
            error_log("ApplicantController::getActivityLog error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error fetching activity log'];
        }
    }
    
    /**
     * Log applicant activity
     */
    private function logActivity($applicantId, $action, $oldStatus, $newStatus, $performedBy, $notes) {
        try {
            $query = "INSERT INTO applicant_activity_log 
                (applicant_id, action, old_status, new_status, performed_by, notes) 
                VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("isssis", $applicantId, $action, $oldStatus, $newStatus, $performedBy, $notes);
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("ApplicantController::logActivity error: " . $e->getMessage());
        }
    }
    
    /**
     * Get applicant statistics
     */
    public function getStatistics() {
        try {
            $query = "SELECT * FROM v_applicant_statistics";
            $result = $this->conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                return ['success' => true, 'statistics' => $result->fetch_assoc()];
            }
            
            return ['success' => false, 'message' => 'No statistics available'];
            
        } catch (Exception $e) {
            error_log("ApplicantController::getStatistics error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error fetching statistics'];
        }
    }
    
    /**
     * Convert applicant to employee
     */
    public function convertToEmployee($applicantId, $employeeData, $performedBy) {
        try {
            $this->conn->begin_transaction();
            
            // Get applicant data
            $applicantQuery = "SELECT * FROM applicants WHERE applicant_id = ?";
            $stmt = $this->conn->prepare($applicantQuery);
            $stmt->bind_param("i", $applicantId);
            $stmt->execute();
            $applicant = $stmt->get_result()->fetch_assoc();
            
            if (!$applicant) {
                throw new Exception("Applicant not found");
            }
            
            // Insert into employees table
            $empQuery = "INSERT INTO employees (
                fname, mname, lname, email, contact_no, address, 
                date_of_birth, gender, position_id, branch_id, department_id,
                hire_date, applicant_id, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 1)";
            
            $stmt = $this->conn->prepare($empQuery);
            $stmt->bind_param(
                "ssssssssiiiii",
                $applicant['first_name'],
                $applicant['middle_name'],
                $applicant['last_name'],
                $applicant['email'],
                $applicant['phone'],
                $applicant['address'],
                $applicant['date_of_birth'],
                $applicant['gender'],
                $employeeData['position_id'],
                $employeeData['branch_id'],
                $employeeData['department_id'],
                $applicantId
            );
            
            $stmt->execute();
            $employeeId = $stmt->insert_id;
            
            // Update applicant status to 'hired'
            $updateQuery = "UPDATE applicants SET status = 'hired' WHERE applicant_id = ?";
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->bind_param("i", $applicantId);
            $stmt->execute();
            
            // Log activity
            $this->logActivity($applicantId, 'hired', $applicant['status'], 'hired', $performedBy, 
                "Converted to employee ID: " . $employeeId);
            
            $this->conn->commit();
            
            return [
                'success' => true, 
                'message' => 'Applicant successfully converted to employee',
                'employee_id' => $employeeId
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("ApplicantController::convertToEmployee error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error converting applicant to employee: ' . $e->getMessage()];
        }
    }
}

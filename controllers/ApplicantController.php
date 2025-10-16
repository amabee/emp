<?php
class ApplicantController
{
  private $conn;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  /**
   * Register a new applicant
   */
  public function register($data)
  {
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
      $stmt->execute([$data['email']]);
      if ($stmt->fetch()) {
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
                position_applied, branch_applied, department_applied, resume_path, cover_letter,
                skills, experience_years, expected_salary, available_start_date,
                reference_name, reference_contact, reference_relationship,
                verification_token
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt = $this->conn->prepare($query);
      $result = $stmt->execute([
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
        $data['resume_path'],
        $data['cover_letter'],
        $data['skills'],
        $data['experience_years'],
        $data['expected_salary'],
        $data['available_start_date'],
        $data['reference_name'],
        $data['reference_contact'],
        $data['reference_relationship'],
        $verificationToken
      ]);

      if ($result) {
        $applicantId = $this->conn->lastInsertId();

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
  public function login($email, $password)
  {
    try {
      $query = "SELECT 
                applicant_id, first_name, middle_name, last_name, email, password_hash, 
                phone, address, position_applied, status, is_active, email_verified
            FROM applicants 
            WHERE email = ? AND is_active = TRUE";

      $stmt = $this->conn->prepare($query);
      $stmt->execute([$email]);

      $applicant = $stmt->fetch();

      if (!$applicant) {
        return ['success' => false, 'message' => 'Invalid email or password'];
      }

      // Verify password
      if (!password_verify($password, $applicant['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password'];
      }

      // Update last login
      $updateQuery = "UPDATE applicants SET last_login = NOW() WHERE applicant_id = ?";
      $updateStmt = $this->conn->prepare($updateQuery);
      $updateStmt->execute([$applicant['applicant_id']]);

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
  public function getProfile($applicantId)
  {
    try {
      $query = "SELECT 
                a.*,
                p.position_name,
                b.branch_name,
                d.department_name,
                CONCAT(e.first_name, ' ', e.last_name) as interviewer_name
            FROM applicants a
            LEFT JOIN job_position p ON a.position_applied = p.position_id
            LEFT JOIN branches b ON a.branch_applied = b.branch_id
            LEFT JOIN department d ON a.department_applied = d.department_id
            LEFT JOIN employees e ON a.interviewer_id = e.employee_id
            WHERE a.applicant_id = ?";

      $stmt = $this->conn->prepare($query);
      $stmt->execute([$applicantId]);

      $applicant = $stmt->fetch();

      if (!$applicant) {
        return ['success' => false, 'message' => 'Applicant not found'];
      }

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
  public function updateProfile($applicantId, $data)
  {
    try {
      $query = "UPDATE applicants SET 
                first_name = ?, middle_name = ?, last_name = ?, phone = ?,
                alternative_phone = ?, address = ?, city = ?, state = ?, zip_code = ?,
                date_of_birth = ?, gender = ?, skills = ?, experience_years = ?,
                expected_salary = ?, available_start_date = ?,
                reference_name = ?, reference_contact = ?, reference_relationship = ?
            WHERE applicant_id = ?";

      $stmt = $this->conn->prepare($query);
      $result = $stmt->execute([
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
      ]);

      if ($result) {
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
  public function getAllApplicants($filters = [])
  {
    try {
      $query = "SELECT 
                a.applicant_id, a.first_name, a.middle_name, a.last_name, a.email, a.phone,
                a.status, a.application_date, a.interview_date, a.experience_years,
                p.position_name, b.branch_name, d.department_name,
                CONCAT(e.first_name, ' ', e.last_name) as interviewer_name
            FROM applicants a
            LEFT JOIN job_position p ON a.position_applied = p.position_id
            LEFT JOIN branches b ON a.branch_applied = b.branch_id
            LEFT JOIN department d ON a.department_applied = d.department_id
            LEFT JOIN employees e ON a.interviewer_id = e.employee_id
            WHERE a.is_active = TRUE";

      $params = [];

      // Apply filters
      if (!empty($filters['status'])) {
        $query .= " AND a.status = ?";
        $params[] = $filters['status'];
      }

      if (!empty($filters['position_id'])) {
        $query .= " AND a.position_applied = ?";
        $params[] = $filters['position_id'];
      }

      if (!empty($filters['branch_id'])) {
        $query .= " AND a.branch_applied = ?";
        $params[] = $filters['branch_id'];
      }

      $query .= " ORDER BY a.application_date DESC";

      $stmt = $this->conn->prepare($query);
      $stmt->execute($params);

      $applicants = $stmt->fetchAll();

      return ['success' => true, 'applicants' => $applicants];

    } catch (Exception $e) {
      error_log("ApplicantController::getAllApplicants error: " . $e->getMessage());
      return ['success' => false, 'message' => 'Error fetching applicants'];
    }
  }

  /**
   * Update applicant status (HR function)
   */
  public function updateStatus($applicantId, $newStatus, $performedBy, $notes = null)
  {
    try {
      // Get current status
      $currentQuery = "SELECT status FROM applicants WHERE applicant_id = ?";
      $stmt = $this->conn->prepare($currentQuery);
      $stmt->execute([$applicantId]);

      $row = $stmt->fetch();

      if (!$row) {
        return ['success' => false, 'message' => 'Applicant not found'];
      }

      $currentStatus = $row['status'];

      // Update status
      $updateQuery = "UPDATE applicants SET status = ? WHERE applicant_id = ?";
      $stmt = $this->conn->prepare($updateQuery);
      $result = $stmt->execute([$newStatus, $applicantId]);

      if ($result) {
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
  public function getActivityLog($applicantId)
  {
    try {
      $query = "SELECT 
                l.*,
                CONCAT(e.first_name, ' ', e.last_name) as performer_name
            FROM applicant_activity_log l
            LEFT JOIN employees e ON l.performed_by = e.employee_id
            WHERE l.applicant_id = ?
            ORDER BY l.created_at DESC";

      $stmt = $this->conn->prepare($query);
      $stmt->execute([$applicantId]);

      $logs = $stmt->fetchAll();

      return ['success' => true, 'logs' => $logs];

    } catch (Exception $e) {
      error_log("ApplicantController::getActivityLog error: " . $e->getMessage());
      return ['success' => false, 'message' => 'Error fetching activity log'];
    }
  }

  /**
   * Log applicant activity
   */
  private function logActivity($applicantId, $action, $oldStatus, $newStatus, $performedBy, $notes)
  {
    try {
      $query = "INSERT INTO applicant_activity_log 
                (applicant_id, action, old_status, new_status, performed_by, notes) 
                VALUES (?, ?, ?, ?, ?, ?)";

      $stmt = $this->conn->prepare($query);
      $stmt->execute([$applicantId, $action, $oldStatus, $newStatus, $performedBy, $notes]);

    } catch (Exception $e) {
      error_log("ApplicantController::logActivity error: " . $e->getMessage());
    }
  }

  /**
   * Get applicant statistics
   */
  public function getStatistics()
  {
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
  public function convertToEmployee($applicantId, $employeeData, $performedBy)
  {
    try {
      $this->conn->beginTransaction();

      // Get applicant data
      $applicantQuery = "SELECT * FROM applicants WHERE applicant_id = ?";
      $stmt = $this->conn->prepare($applicantQuery);
      $stmt->execute([$applicantId]);
      $applicant = $stmt->fetch();

      if (!$applicant) {
        throw new Exception("Applicant not found");
      }

      // Generate username and temporary password
      $username = $this->generateUsername($applicant['first_name'], $applicant['last_name']);
      $temporaryPassword = $this->generateTemporaryPassword();
      $hashedPassword = password_hash($temporaryPassword, PASSWORD_DEFAULT);

      // Insert into employees table
      $empQuery = "INSERT INTO employees (
                fname, mname, lname, email, contact_no, address, 
                date_of_birth, gender, position_id, branch_id, department_id,
                hire_date, applicant_id, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 1)";

      $stmt = $this->conn->prepare($empQuery);
      $stmt->execute([
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
      ]);

      $employeeId = $this->conn->lastInsertId();

      // Create user account for employee login
      $userQuery = "INSERT INTO users (username, password, role, employee_id, email, is_active, created_at) 
                    VALUES (?, ?, 'employee', ?, ?, 1, NOW())";
      $stmt = $this->conn->prepare($userQuery);
      $stmt->execute([$username, $hashedPassword, $employeeId, $applicant['email']]);

      // Update applicant status to 'hired'
      $updateQuery = "UPDATE applicants SET status = 'hired' WHERE applicant_id = ?";
      $stmt = $this->conn->prepare($updateQuery);
      $stmt->execute([$applicantId]);

      // Log activity
      $this->logActivity(
        $applicantId,
        'hired',
        $applicant['status'],
        'hired',
        $performedBy,
        "Converted to employee ID: " . $employeeId . ", Username: " . $username
      );

      $this->conn->commit();

      return [
        'success' => true,
        'message' => 'Applicant successfully converted to employee',
        'employee_id' => $employeeId,
        'username' => $username,
        'temporary_password' => $temporaryPassword,
        'employee_data' => [
          'fname' => $applicant['first_name'],
          'mname' => $applicant['middle_name'],
          'lname' => $applicant['last_name'],
          'email' => $applicant['email']
        ]
      ];

    } catch (Exception $e) {
      $this->conn->rollBack();
      error_log("ApplicantController::convertToEmployee error: " . $e->getMessage());
      return ['success' => false, 'message' => 'Error converting applicant to employee: ' . $e->getMessage()];
    }
  }

  /**
   * Generate username from name (e.g., jdoe, jdoe2, jdoe3)
   */
  private function generateUsername($firstName, $lastName)
  {
    // Create base username (first initial + last name)
    $baseUsername = strtolower(substr($firstName, 0, 1) . $lastName);
    $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername); // Remove special characters
    
    $username = $baseUsername;
    $counter = 1;
    
    // Check if username exists, add number if needed
    while ($this->usernameExists($username)) {
      $counter++;
      $username = $baseUsername . $counter;
    }
    
    return $username;
  }

  /**
   * Check if username exists
   */
  private function usernameExists($username)
  {
    $query = "SELECT COUNT(*) FROM users WHERE username = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$username]);
    return $stmt->fetchColumn() > 0;
  }

  /**
   * Generate temporary password (8-12 characters, alphanumeric)
   */
  private function generateTemporaryPassword($length = 10)
  {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
      $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
  }
}
